<?php

namespace App\Controller;

use App\DTO\TaskItem\TaskItemComplete;
use App\DTO\TaskItem\TaskItemCreate;
use App\Entity\JsonResponse\JsonError;
use App\Entity\JsonResponse\JsonSuccess;
use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Form\TaskItemCompleteType;
use App\UseCase\TaskItem\TaskItemHandler;
use DateTime;
use Exception;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @Route("/{_locale}/task-item", name="task_item_")
 *
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class TaskItemController extends AbstractController
{
    /**
     * @Route("/create", name="create", methods={"POST"})
     *
     * @param Request $request
     * @param TaskItemHandler $taskItemHandler
     *
     * @return Response
     */
    public function create(Request $request, TaskItemHandler $taskItemHandler): Response
    {
        try {
            $dataArray = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(sprintf('Failed to decode request: %s', json_last_error_msg()));
            }

            $taskItemCreateData = new TaskItemCreate($dataArray);
            if (!$this->isCsrfTokenValid(TaskItemCreate::FORM_NAME, $taskItemCreateData->token)) {
                throw new ValidatorException('Invalid CSRF token');
            }

            $taskItem = $taskItemHandler->create($taskItemCreateData, $this->getUser());

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
     * @param TaskItemHandler     $taskItemHandler
     *
     * @return Response
     *
     * @throws Exception
     */
    public function complete(Request $request, SerializerInterface $serializer, TaskItemHandler $taskItemHandler): Response
    {
        try {
            $dataArray = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(sprintf('Failed to decode request: %s', json_last_error_msg()));
            }

            $taskItemCompleteData = new TaskItemComplete($dataArray);
            if (!$this->isCsrfTokenValid(TaskItemComplete::FORM_NAME, $taskItemCompleteData->token)) {
                throw new ValidatorException('Invalid CSRF token');
            }
            $taskItem = $taskItemHandler->complete($taskItemCompleteData);

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