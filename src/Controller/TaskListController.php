<?php

namespace App\Controller;

use App\Entity\JsonRequest\TaskItemCreate;
use App\Entity\JsonRequest\TaskListShare;
use App\Entity\JsonResponse\JsonError;
use App\Entity\JsonResponse\JsonSuccess;
use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Entity\User;
use App\Form\ShareListEmailType;
use App\Form\TaskItemCompleteType;
use App\Form\TaskItemCreateType;
use App\Form\TaskListType;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use DateTime;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @Route("/task-list", name="task_list_")
 */
class TaskListController extends AbstractController
{
    /**
     * @Route("/", name="index")
     *
     * @param Request            $request
     * @param TaskListRepository $taskListRepository
     *
     * @return Response
     */
    public function index(Request $request, TaskListRepository $taskListRepository): Response
    {
        $taskLists = $taskListRepository->getUsersTasks($this->getUser());
        $sharedLists = $taskListRepository->getSharedTasks($this->getUser());

        return $this->render(
            'task-list/index.html.twig',
            [
                'task_lists' => $taskLists,
                'shared_lists' => $sharedLists,
            ]
        );
    }

    /**
     * @Route("/create", name="create")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     */
    public function create(Request $request): Response
    {
        $taskList = (new TaskList())
            ->setName('New List')
            ->setCreator($this->getUser())
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());

        $this->getDoctrine()->getManager()->persist($taskList);
        $this->getDoctrine()->getManager()->flush();

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
        if ($taskList->getCreator() !== $this->getUser()) {
            throw new AccessDeniedException();
        }

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

        return $this->render(
            'task-list/view.html.twig',
            [
                'task_list' => $taskList,
                'form' => $form->createView(),
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
     * @param Request  $request
     *
     * @return Response
     */
    public function delete(TaskList $taskList, Request $request): Response
    {
        if ($taskList->getCreator() !== $this->getUser()) {
            throw new AccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$taskList->getId(), $request->request->get('_token'))) {
            $this->getDoctrine()->getManager()->remove($taskList);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', sprintf('%s deleted', $taskList->getName()));

            return $this->redirectToRoute('task_list_index');
        }

        throw new InvalidCsrfTokenException();
    }

    /**
     * @Route("/share/{id}", name="share", methods={"POST"})
     *
     * @param TaskList $taskList
     * @param Request  $request
     *
     * @return Response
     */
    public function taskListShare(TaskList $taskList, Request $request): Response
    {
        try {
            $dataArray = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(sprintf('Failed to decode request: %s', json_last_error_msg()));
            }

            $taskListShareData = new TaskListShare($dataArray);
            if (!$this->isCsrfTokenValid(TaskListShare::FORM_NAME, $taskListShareData->getToken())) {
                throw new ValidatorException('Invalid CSRF token');
            }

            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $taskListShareData->getEmail()]);
            if ($user) {
                $taskList->addShared($user);
                $this->getDoctrine()->getManager()->flush();
                // @todo send notification email
                return new JsonSuccess(
                    $this->renderView(
                        'task-list/shared-user.html.twig',
                        [
                            'user' => $user,
                        ]
                    )
                );
            }

            // @todo send invitation email
            return new JsonError('User not found. The registration invitation was send on this email');

        } catch (Exception $e) {
            return new JsonError(
                $e->getMessage()
            );
        }
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
    public function getCompleteItemFormsViews(iterable $taskItems): array
    {
        $views = [];
        foreach ($taskItems as $taskItem) {
            $views[$taskItem->getId()] = $this->getCompleteItemForm($taskItem)->createView();
        }

        return $views;
    }
}