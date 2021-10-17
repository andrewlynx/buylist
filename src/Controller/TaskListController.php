<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\DTO\TaskList\TaskListUsersRaw;
use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Entity\User;
use App\Form\ShareListEmailType;
use App\Form\ListArchiveType;
use App\Form\TaskItemCompleteType;
use App\Form\TaskListType;
use App\Form\UnsubscribeType;
use App\Repository\TaskListRepository;
use App\UseCase\TaskList\TaskListHandler;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/{_locale}/task-list", name="task_list_", locale="en", requirements={"_locale": "[a-z]{2}"})
 */
class TaskListController extends TranslatableController
{
    /**
     * @Route("/", name="index")
     *
     * @param TaskListRepository $taskListRepository
     *
     * @return Response
     */
    public function index(TaskListRepository $taskListRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $taskLists = $taskListRepository->getUsersTasks($user);

        return $this->render(
            'v1/task-list/index.html.twig',
            [
                'task_lists' => $taskLists,
                'archive_item_forms' => $this->getArchiveListFormsViews($taskLists),
                'load_more_link' => $this->generateUrl('task_list_load_more', ['page' => 1]),
            ]
        );
    }

    /**
     * @Route("/load-more/{page}", name="load_more")
     *
     * @param int $page
     * @param TaskListRepository $taskListRepository
     *
     * @return Response
     */
    public function loadMore(int $page, TaskListRepository $taskListRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $taskLists = $taskListRepository->getUsersTasks($user, $page);

        return $this->render(
            'v1/parts/private/list/list.html.twig',
            [
                'task_lists' => $taskLists,
                'archive_item_forms' => $this->getArchiveListFormsViews($taskLists),
                'load_more_link' => $this->generateUrl('task_list_load_more', ['page' => ++$page]),
            ]
        );
    }

    /**
     * @Route("/shared", name="index_shared")
     *
     * @param TaskListRepository $taskListRepository
     *
     * @return Response
     */
    public function indexShared(TaskListRepository $taskListRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $taskLists = $taskListRepository->getSharedTasks($user);

        return $this->render(
            'v1/task-list/shared-index.html.twig',
            [
                'task_lists' => $taskLists,
                'unsubscribe_forms' => $this->getUnsubscribeFormsViews($taskLists),
                'load_more_link' => $this->generateUrl('task_list_load_more_shared', ['page' => 1]),
            ]
        );
    }

    /**
     * @Route("/load-more-shared/{page}", name="load_more_shared")
     *
     * @param int $page
     * @param TaskListRepository $taskListRepository
     *
     * @return Response
     */
    public function loadMoreShared(int $page, TaskListRepository $taskListRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $taskLists = $taskListRepository->getSharedTasks($user, $page);

        return $this->render(
            'v1/parts/private/list/shared-list.html.twig',
            [
                'task_lists' => $taskLists,
                'unsubscribe_forms' => $this->getUnsubscribeFormsViews($taskLists),
                'load_more_link' => $this->generateUrl('task_list_load_more_shared', ['page' => ++$page]),
            ]
        );
    }

    /**
     * @Route("/archive", name="archive")
     *
     * @param TaskListRepository $taskListRepository
     *
     * @return Response
     */
    public function archive(TaskListRepository $taskListRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $taskLists = $taskListRepository->getArchivedUsersTasks($user);

        return $this->render(
            'v1/task-list/archive-index.html.twig',
            [
                'task_lists' => $taskLists,
                'archive_item_forms' => $this->getArchiveListFormsViews($taskLists),
                'load_more_link' => $this->generateUrl('task_list_load_more_archive', ['page' => 1]),
            ]
        );
    }

    /**
     * @Route("/load-more-archive/{page}", name="load_more_archive")
     *
     * @param int $page
     * @param TaskListRepository $taskListRepository
     *
     * @return Response
     */
    public function loadMoreArchive(int $page, TaskListRepository $taskListRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $taskLists = $taskListRepository->getArchivedUsersTasks($user, $page);

        return $this->render(
            'v1/parts/private/list/list.html.twig',
            [
                'task_lists' => $taskLists,
                'archive_item_forms' => $this->getArchiveListFormsViews($taskLists),
                'load_more_link' => $this->generateUrl('task_list_load_more_archive', ['page' => ++$page]),
            ]
        );
    }

    /**
     * @Route("/archive-clear", name="archive_clear")
     *
     * @param TaskListHandler $taskListHandler
     * @param Request         $request
     *
     * @return Response
     */
    public function archiveClear(TaskListHandler $taskListHandler, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('clear_archive', $request->request->get('_token'))) {
            $taskListHandler->clearArchive($user);

            $this->addFlash('success', $this->translator->trans('list.archive_cleared'));

            return $this->redirectToRoute('task_list_index');
        }

        $this->addFlash('danger', 'validation.invalid_submission');

        return $this->redirectToRoute('task_list_archive');
    }

    /**
     * @Route("/create", name="create")
     *
     * @param TaskListHandler $taskListHandler
     * @param Request $request
     * @param TaskList $taskList
     *
     * @return Response
     *
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function create(TaskListHandler $taskListHandler, Request $request, TaskList $taskList = null): Response
    {
        $isUpdate = $taskList instanceof TaskList;
        /** @var User $user */
        $user = $this->getUser();
        $taskList = $taskList ?? $taskListHandler->create($user);

        $form = $this->createForm(TaskListType::class, $taskList)
            ->handleRequest($request);

        $shareListForm = $this->createForm(ShareListEmailType::class);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($taskList->getCreator() === $user) {
                    $fromFavourites = $form->get('favouriteUsers')->getNormData();
                    $fromUsersData = $form->get('users')->getNormData();

                    $fromUsers = $taskListHandler->processSharedList(new TaskListUsersRaw($fromUsersData), $taskList);
                    foreach ($fromUsers->notAllowed as $notAllowed) {
                        $this->addFlash('warning', $notAllowed);
                    }
                    foreach ($fromUsers->invitationSent as $invitationSent) {
                        $this->addFlash('success', $invitationSent);
                    }
                    foreach ($fromUsers->invitationExists as $invitationExist) {
                        $this->addFlash('warning', $invitationExist);
                    }
                    $users = array_merge($fromFavourites, $fromUsers->registered);

                    $taskList = $taskListHandler->updateSharedUsers($taskList, $users);
                }
                $taskList = $taskListHandler->edit($taskList);
                $this->addFlash('success', $isUpdate ? 'list.updated' : 'list.created');

                return $this->redirectToRoute('task_list_view', ['id' => $taskList->getId()]);
            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render(
            'v1/task-list/create.html.twig',
            [
                'task_list' => $taskList,
                'form' => $form->createView(),
                'task_list_share' => $shareListForm->createView(),
                'complete_item_forms' => $this->getCompleteItemFormsViews($taskList->getTaskItems()),
            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="edit")
     *
     * @param TaskList $taskList
     * @param TaskListHandler $taskListHandler
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function edit(TaskList $taskList, TaskListHandler $taskListHandler, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->checkSharedAccess($taskList, $user);

        return $this->create($taskListHandler, $request, $taskList);
    }

    /**
     * @Route("/{id}", name="view")
     *
     * @param TaskList $taskList
     * @param Request  $request
     *
     * @return Response
     *
     * @throws Exception
     */
    public function view(TaskList $taskList, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->checkSharedAccess($taskList, $user);

        $archiveForm = $this->getArchiveListForm($taskList);

        return $this->render(
            'v1/task-list/view.html.twig',
            [
                'task_list' => $taskList,
                'task_list_archive' => $archiveForm->createView(),
                'complete_item_forms' => $this->getCompleteItemFormsViews($taskList->getTaskItems()),
            ]
        );
    }

    /**
     * @Route("/delete/{id}", name="delete")
     *
     * @param TaskList        $taskList
     * @param Request         $request
     * @param TaskListHandler $taskListHandler
     *
     * @return Response
     *
     * @throws Exception
     */
    public function delete(TaskList $taskList, Request $request, TaskListHandler $taskListHandler): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->checkCreatorAccess($taskList, $user);

        if ($this->isCsrfTokenValid('delete'.$taskList->getId(), $request->request->get('_token'))) {
            $taskListHandler->delete($taskList);

            $this->addFlash('success', sprintf('%s deleted', $taskList->getName()));

            return $this->redirectToRoute('task_list_index');
        }
        $this->addFlash('danger', 'validation.invalid_submission');

        return $this->redirectToRoute('task_list_index');
    }

    /**
     * @Route("/{id}/archive-list", name="archive_list")
     *
     * @param TaskList $taskList
     * @param Request $request
     * @param TaskListHandler $taskListHandler
     *
     * @return Response
     *
     * @throws Exception
     */
    public function archiveList(
        TaskList $taskList,
        Request $request,
        TaskListHandler $taskListHandler
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $this->checkCreatorAccess($taskList, $user);

        $archiveForm = $this->getArchiveListForm($taskList)->handleRequest($request);

        if ($archiveForm->isSubmitted() && $archiveForm->isValid()) {
            $taskListHandler->archive(
                $taskList,
                (bool) $request->request->get('list_archive')['status'] ?? false
            );

            $this->addFlash(
                'success',
                $taskList->isArchived() ? 'list.list_archived' : 'list.list_restored'
            );
            return $this->redirectToRoute('task_list_index');
        }
        $this->addFlash('danger', 'validation.invalid_submission');

        return $this->redirectToRoute('task_list_view', ['id' => $taskList->getId()]);
    }

    /**
     * @Route("/{id}/unsubscribe", name="unsubscribe")
     *
     * @param TaskList $taskList
     * @param Request $request
     * @param TaskListHandler $taskListHandler
     *
     * @return Response
     *
     * @throws Exception
     */
    public function unsubscribe(
        TaskList $taskList,
        Request $request,
        TaskListHandler $taskListHandler
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $this->checkSharedAccess($taskList, $user);

        $unsubscribeForm = $this->getUnsubscribeForm($taskList)->handleRequest($request);

        if ($unsubscribeForm->isSubmitted() && $unsubscribeForm->isValid()) {
            $taskListHandler->unsubscribe(
                $taskList,
                $user
            );

            $this->addFlash(
                'success',
                $this->translator->trans(
                    'list.unsubscribed',
                    [
                        'list' => $taskList->getName(),
                    ]
                )
            );

            return $this->redirectToRoute('task_list_index_shared');
        }
        $this->addFlash('danger', 'validation.invalid_submission');

        return $this->redirectToRoute('task_list_index_shared');
    }

    /**
     * @param TaskItem $taskItem
     *
     * @return FormInterface
     */
    private function getCompleteItemForm(TaskItem $taskItem): FormInterface
    {
        return $this->createForm(TaskItemCompleteType::class, $taskItem, [
            'action' => $this->generateUrl('task_item_complete'),
        ]);
    }

    /**
     * @param iterable $taskItems
     *
     * @return array
     */
    private function getCompleteItemFormsViews(iterable $taskItems): array
    {
        $views = [];
        foreach ($taskItems as $taskItem) {
            $views[$taskItem->getId()] = $this->getCompleteItemForm($taskItem)->createView();
        }

        return $views;
    }

    /**
     * @param TaskList $taskList
     *
     * @return FormInterface
     */
    private function getArchiveListForm(TaskList $taskList): FormInterface
    {
        return $this->createForm(
            ListArchiveType::class,
            ['status' => $taskList->isArchived()],
            ['action' => $this->generateUrl('task_list_archive_list', ['id' => $taskList->getId()])]
        );
    }

    /**
     * @param iterable $taskItems
     *
     * @return array
     */
    private function getArchiveListFormsViews(iterable $taskItems): array
    {
        $views = [];
        foreach ($taskItems as $taskItem) {
            $views[$taskItem->getId()] = $this->getArchiveListForm($taskItem)->createView();
        }

        return $views;
    }

    /**
     * @param TaskList $taskList
     *
     * @return FormInterface
     */
    private function getUnsubscribeForm(TaskList $taskList): FormInterface
    {
        return $this->createForm(
            UnsubscribeType::class,
            ['task_list' => $taskList],
            ['action' => $this->generateUrl('task_list_unsubscribe', ['id' => $taskList->getId()])]
        );
    }

    /**
     * @param iterable $taskItems
     *
     * @return array
     */
    private function getUnsubscribeFormsViews(iterable $taskItems): array
    {
        $views = [];
        foreach ($taskItems as $taskItem) {
            $views[$taskItem->getId()] = $this->getUnsubscribeForm($taskItem)->createView();
        }

        return $views;
    }

    /**
     * @param TaskList $taskList
     * @param User     $user
     *
     * @throws AccessDeniedException
     */
    private function checkCreatorAccess(TaskList $taskList, User $user): void
    {
        if ($taskList->getCreator() !== $user) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @param TaskList $taskList
     * @param User     $user
     *
     * @throws AccessDeniedException
     */
    private function checkSharedAccess(TaskList $taskList, User $user): void
    {
        if (!($taskList->getCreator() === $user || $taskList->getShared()->contains($user))) {
            throw new AccessDeniedException();
        }
    }
}
