<?php

namespace App\Controller;

use App\Entity\JsonRequest\TaskItemComplete;
use App\Entity\JsonRequest\TaskItemCreate;
use App\Entity\JsonResponse\JsonError;
use App\Entity\JsonResponse\JsonSuccess;
use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Form\TaskItemCompleteType;
use DateTime;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @Route("/task-item", name="task_item_")
 */
class TaskItemController extends AbstractController
{
    /**
     * @Route("/create", name="create", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     */
    public function create(Request $request): Response
    {
        try {
            $dataArray = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(sprintf('Failed to decode request: %s', json_last_error_msg()));
            }

            $taskItemCreateData = new TaskItemCreate($dataArray);
            if (!$this->isCsrfTokenValid(TaskItemCreate::FORM_NAME, $taskItemCreateData->getToken())) {
                throw new ValidatorException('Invalid CSRF token');
            }

            $taskListRepo = $this->getDoctrine()->getRepository(TaskList::class);
            /** @var TaskList $taskList */
            $taskList = $taskListRepo->find($taskItemCreateData->getListId());

            if (!$taskList || $taskList->getCreator() !== $this->getUser()) {
                throw new Exception('Error assigning new Item to List');
            }

            $taskItem = (new TaskItem())
                ->setName($taskItemCreateData->getName())
                ->setQty($taskItemCreateData->getQty())
                ->setTaskList($taskList->setUpdatedAt(new DateTime()));

            $this->getDoctrine()->getManager()->persist($taskItem);
            $this->getDoctrine()->getManager()->flush();

            $completeForm = $this->createForm(TaskItemCompleteType::class, $taskItem, [
                'action' => $this->generateUrl('task_item_complete'),
            ]);

            return new JsonSuccess(
                $this->renderView(
                    'task-item/task-item.html.twig',
                    [
                        'task_item' => $taskItem,
                        'complete_item_forms' => [
                            $taskItem->getId() => $completeForm->createView(),
                        ]
                    ]
                )
            );
        } catch (Exception $e) {
            return new JsonError(
                $e->getMessage()
            );
        }
    }

    /**
     * @Route("/complete", name="complete", methods={"POST"})
     *
     * @param Request             $request
     * @param SerializerInterface $serializer
     *
     * @return Response
     *
     * @throws Exception
     */
    public function complete(Request $request, SerializerInterface $serializer): Response
    {
        try {
            $dataArray = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(sprintf('Failed to decode request: %s', json_last_error_msg()));
            }

            $taskItemCompleteData = new TaskItemComplete($dataArray);
            if (!$this->isCsrfTokenValid(TaskItemComplete::FORM_NAME, $taskItemCompleteData->getToken())) {
                throw new ValidatorException('Invalid CSRF token');
            }

            $taskItemRepo = $this->getDoctrine()->getRepository(TaskItem::class);
            /** @var TaskItem $taskItem */
            $taskItem = $taskItemRepo->find($taskItemCompleteData->getId());
            $taskItem->setCompleted(!$taskItemCompleteData->isCompleted());

            $this->getDoctrine()->getManager()->flush();

            return new JsonSuccess(
                $serializer->serialize($taskItem, 'json', [AbstractNormalizer::ATTRIBUTES => ['id', 'completed']])
            );
        } catch (Exception $e) {
            return new JsonError(
                $e->getMessage()
            );
        }
    }
}