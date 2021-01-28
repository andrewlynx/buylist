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
use App\Repository\TaskListRepository;
use App\UseCase\TaskList\TaskListHandler;
use DateTime;
use Exception;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Exception\ValidatorException;
use Throwable;

/**
 * @Route("/{_locale}/task-list", name="task_list_", requirements={"_locale": "[a-z]{2}"})
 *
 * @IsGranted("IS_AUTHENTICATED_FULLY")
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
        $taskLists = $taskListRepository->getUsersTasks($this->getUser());

        return $this->render(
            'task-list/index.html.twig',
            [
                'task_lists' => $taskLists,
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
        $taskLists = $taskListRepository->getSharedTasks($this->getUser());

        return $this->render(
            'task-list/index-shared.html.twig',
            [
                'task_lists' => $taskLists,
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
        $taskLists = $taskListRepository->getArchivedUsersTasks($this->getUser());

        return $this->render(
            'task-list/index.html.twig',
            [
                'task_lists' => $taskLists,
            ]
        );
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
        $taskList = $taskListHandler->create($this->getUser());

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
        $this->checkSharedAccess($taskList, $this->getUser());

        $form = $this->createForm(TaskListType::class, $taskList)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taskList->setUpdatedAt(new DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', sprintf('%s updated', $taskList->getName()));
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
     * @param TaskList $taskList
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     */
    public function delete(TaskList $taskList, Request $request): Response
    {
        $this->checkCreatorAccess($taskList, $this->getUser());

        if ($this->isCsrfTokenValid('delete'.$taskList->getId(), $request->request->get('_token'))) {
            $this->getDoctrine()->getManager()->remove($taskList);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', sprintf('%s deleted', $taskList->getName()));

            return $this->redirectToRoute('task_list_index');
        }

        $this->addFlash('danger', 'validation.invalid_csrf');
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
    ): Response
    {
        try {
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
                    'task-list/shared-user.html.twig',
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
     */
    public function archiveList(
        TaskList $taskList,
        Request $request,
        TaskListHandler $taskListHandler
    ): Response
    {
        $this->checkCreatorAccess($taskList, $this->getUser());

        $archiveForm = $this->getArchiveListForm($taskList)->handleRequest($request);

        if ($archiveForm->isSubmitted() && $archiveForm->isValid()) {
            $taskListHandler->archive(
                $taskList,
                $request->request->get('status') ?? false
            );

            $this->addFlash('success', 'list.archived');
            return $this->redirectToRoute('task_list_index');
        }

        $this->addFlash('danger', 'validation.invalid_csrf');

        return $this->redirectToRoute('task_list_view', ['id' => $taskList->getId()]);
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
     * @param TaskList $taskList
     *
     * @return FormInterface
     */
    private function getArchiveListForm(TaskList $taskList): FormInterface
    {
        return $this->createForm(
            ListArchiveType::class,
            ['status' => false],
            ['action' => $this->generateUrl('task_list_archive_list', ['id' => $taskList->getId()])]
        );
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
