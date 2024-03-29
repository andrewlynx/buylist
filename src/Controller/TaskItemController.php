<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\Controller\Traits\FormsTrait;
use App\DTO\TaskItem\TaskItemComplete;
use App\DTO\TaskItem\TaskItemCreate;
use App\DTO\TaskItem\TaskItemEdit;
use App\DTO\TaskItem\TaskItemIncrement;
use App\Entity\JsonResponse\JsonError;
use App\Entity\JsonResponse\JsonSuccess;
use App\Entity\TaskItem;
use App\Entity\User;
use App\Form\TaskItemCompleteType;
use App\Form\TaskItemEditType;
use App\UseCase\TaskItem\TaskItemHandler;
use Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale}/task-item", name="task_item_", locale="en", requirements={"_locale": "[a-z]{2}"})
 */
class TaskItemController extends TranslatableController
{
    use FormsTrait;

    /**
     * @var TaskItemHandler
     */
    private $taskItemHandler;

    /**
     * @param TranslatorInterface $translator
     * @param TaskItemHandler     $taskItemHandler
     */
    public function __construct(TranslatorInterface $translator, TaskItemHandler $taskItemHandler)
    {
        parent::__construct($translator);
        $this->taskItemHandler = $taskItemHandler;
    }

    /**
     * @Route("/create", name="create", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        try {
            $taskItemCreateData = new TaskItemCreate(
                $this->jsonDecode($request->getContent())
            );
            $this->checkCsrf(TaskItemCreate::FORM_NAME, $taskItemCreateData->token);

            /** @var User $user */
            $user = $this->getUser();
            $taskItem = $this->taskItemHandler->create($taskItemCreateData, $user);

            $completeForm = $this->getCompleteItemForm($taskItem);

            return new JsonSuccess(
                $this->renderView(
                    'v1/task-item/task-item.html.twig',
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
                $this->translator->trans($e->getMessage())
            );
        }
    }

    /**
     * @Route("/complete", name="complete", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     */
    public function complete(Request $request): Response
    {
        try {
            $taskItemCompleteData = new TaskItemComplete(
                $this->jsonDecode($request->getContent())
            );
            $this->checkCsrf(TaskItemComplete::FORM_NAME, $taskItemCompleteData->token);
            /** @var User $user */
            $user = $this->getUser();
            $taskItem = $this->taskItemHandler->complete($taskItemCompleteData, $user);
            $taskList = $taskItem->getTaskList();

            return new JsonSuccess(
                $this->renderView(
                    'v1/task-item/task-item-list.html.twig',
                    [
                        'task_list' => $taskList,
                        'complete_item_forms' => $this->getCompleteItemFormsViews($taskList->getTaskItems()),
                    ]
                )
            );
        } catch (Exception $e) {
            return new JsonError(
                $this->translator->trans($e->getMessage())
            );
        }
    }

    /**
     * @Route("/increment", name="increment", methods={"POST"})
     *
     * @param Request             $request
     * @param SerializerInterface $serializer
     *
     * @return Response
     *
     * @throws Exception
     */
    public function increment(
        Request $request,
        SerializerInterface $serializer
    ): Response {
        try {
            $taskItemIncrementData = new TaskItemIncrement(
                $this->jsonDecode($request->getContent())
            );
            $this->checkCsrf(TaskItemIncrement::FORM_NAME, $taskItemIncrementData->token);
            /** @var User $user */
            $user = $this->getUser();
            $taskItem = $this->taskItemHandler->increment($taskItemIncrementData, $user);

            return new JsonSuccess(
                $serializer->serialize($taskItem, 'json', [AbstractNormalizer::ATTRIBUTES => ['id', 'qty']])
            );
        } catch (Exception $e) {
            return new JsonError(
                $this->translator->trans($e->getMessage())
            );
        }
    }

    /**
     * @param string|null $data
     *
     * @return array
     */
    private function jsonDecode(?string $data): array
    {
        $dataArray = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                $this->translator->trans('validation.invalid_json_request', ['error' => json_last_error_msg()])
            );
        }

        return $dataArray;
    }
}
