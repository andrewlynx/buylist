<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\DTO\TaskList\TaskListShare;
use App\Entity\JsonResponse\JsonError;
use App\Entity\JsonResponse\JsonSuccess;
use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Entity\User;
use App\Form\ShareListEmailType;
use App\Form\ListArchiveType;
use App\Form\TaskItemCompleteType;
use App\Form\TaskItemCreateType;
use App\Form\TaskListType;
use App\Form\UnsubscribeType;
use App\Repository\TaskListRepository;
use App\UseCase\TaskList\TaskListHandler;
use DateTime;
use Exception;
use RuntimeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Exception\ValidatorException;
use Throwable;

/**
 * @Route("/{_locale}/task-list", name="task_list_", requirements={"_locale": "[a-z]{2}"})
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
            'task-list/index.html.twig',
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
            'parts/private/list/list.html.twig',
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
            'task-list/shared-index.html.twig',
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
            'parts/private/list/shared-list.html.twig',
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
            'task-list/archive-index.html.twig',
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
            'parts/private/list/list.html.twig',
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
     *
     * @return Response
     *
     * @throws Exception
     */
    public function create(TaskListHandler $taskListHandler): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $taskList = $taskListHandler->create($user);

        return $this->redirectToRoute('task_list_view', ['id' => $taskList->getId()]);
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

        $form = $this->createForm(TaskListType::class, $taskList)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$taskList->isArchived()) {
                $taskList->setUpdatedAt(new DateTime());
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'list.updated');
            } else {
                $this->addFlash('warning', 'list.activate_list_to_edit');
            }

            return $this->redirectToRoute('task_list_view', ['id' => $taskList->getId()]);
        }

        $createItemForm = $this->createForm(
            TaskItemCreateType::class,
            ['taskList' => $taskList],
            ['action' => $this->generateUrl('task_item_create')]
        );

        $shareListForm = $this->createForm(
            ShareListEmailType::class,
            [],
            ['action' => $this->generateUrl('task_list_share', ['id' => $taskList->getId()])]
        );

        $archiveForm = $this->getArchiveListForm($taskList);

        return $this->render(
            'task-list/view.html.twig',
            [
                'task_list' => $taskList,
                'form' => $form->createView(),
                'task_list_archive' => $archiveForm->createView(),
                'create_item_form' => $createItemForm->createView(),
                'task_list_share' => $shareListForm->createView(),
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
     * @Route("/share/{id}", name="share", methods={"POST"})
     *
     * @param TaskList $taskList
     * @param Request $request
     * @param TaskListHandler $taskListHandler
     *
     * @return Response
     */
    public function taskListShare(
        TaskList $taskList,
        Request $request,
        TaskListHandler $taskListHandler
    ): Response {
        try {
            if ($taskList->isArchived()) {
                throw new RuntimeException(
                    $this->translator->trans('list.activate_list_to_edit')
                );
            }

            $dataArray = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(
                    $this->translator->trans('validation.invalid_json_request', ['error' => json_last_error_msg()])
                );
            }

            $taskListShareData = new TaskListShare($dataArray);
            if (!$this->isCsrfTokenValid(TaskListShare::FORM_NAME, $taskListShareData->token)) {
                throw new ValidatorException('validation.invalid_csrf');
            }

            $user = $taskListHandler->share($taskList, $taskListShareData);

            return new JsonSuccess(
                $this->renderView(
                    'parts/private/list/shared-user.html.twig',
                    [
                        'user' => $user,
                    ]
                )
            );
        } catch (Throwable $e) {
            return new JsonError(
                $this->translator->trans($e->getMessage())
            );
        }
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
