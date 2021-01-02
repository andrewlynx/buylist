<?php

namespace App\Controller;

use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Form\TaskItemCompleteType;
use App\Form\TaskItemCreateType;
use App\Form\TaskListType;
use App\Repository\TaskListRepository;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

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

        return $this->render(
            'task-list/index.html.twig',
            [
                'task_lists' => $taskLists,
                'forms' => $this->getDeleteFormViews($taskLists),
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
            [
                'taskList' => $taskList,
            ],
            [
                'action' => $this->generateUrl('task_item_create'),
            ]
        );

        return $this->render(
            'task-list/view.html.twig',
            [
                'task_list' => $taskList,
                'form' => $form->createView(),
                'create_item_form' => $createItemForm->createView(),
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

        $form = $this->getDeleteForm($taskList)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->remove($taskList);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', sprintf('%s deleted', $taskList->getName()));

            return $this->redirectToRoute('task_list_index');
        }

        throw new InvalidCsrfTokenException();
    }

    /**
     * @param TaskList $taskList
     *
     * @return Form
     */
    private function getDeleteForm(TaskList $taskList): Form
    {
        return $this->get('form.factory')
            ->createNamed(
                $taskList->getId(),
                'Symfony\Component\Form\Extension\Core\Type\FormType',
                [],
                [
                    'action' => $this->generateUrl('task_list_delete', ['id' => $taskList->getId()]),
                ]
            )
            ->add('Delete', SubmitType::class);
    }

    /**
     * @param iterable $taskLists
     *
     * @return array
     */
    private function getDeleteFormViews(iterable $taskLists): array
    {
        $views = [];
        foreach ($taskLists as $taskList) {
            $views[$taskList->getId()] = $this->getDeleteForm($taskList)->createView();
        }

        return $views;
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