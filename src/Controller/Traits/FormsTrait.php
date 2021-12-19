<?php

namespace App\Controller\Traits;

use App\Constant\TaskListTypes;
use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Form\ListArchiveType;
use App\Form\TaskItemCompleteType;
use App\Form\TaskItemIncrementType;
use App\Form\UnsubscribeType;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

trait FormsTrait
{
    /**
     * @param TaskItem $taskItem
     *
     * @return FormInterface
     */
    protected function getCompleteItemForm(TaskItem $taskItem): FormInterface
    {
        return $this->createForm(TaskItemCompleteType::class, $taskItem, [
            'action' => $this->generateUrl('task_item_complete'),
        ]);
    }

    /**
     * @param TaskItem $taskItem
     *
     * @return FormInterface
     */
    protected function getIncrementItemForm(TaskItem $taskItem): FormInterface
    {
        return $this->createForm(TaskItemIncrementType::class, $taskItem, [
            'action' => $this->generateUrl('task_item_increment'),
        ]);
    }

    /**
     * @param Collection $taskItems
     *
     * @return array
     */
    protected function getCompleteItemFormsViews(Collection $taskItems): array
    {
        $views = [];
        foreach ($taskItems as $taskItem) {
            $views[$taskItem->getId()] = $this->getCompleteItemForm($taskItem)->createView();
        }

        return $views;
    }

    /**
     * @param Collection $taskItems
     *
     * @return array
     */
    protected function getIncrementItemFormsViews(Collection $taskItems): array
    {
        $views = [];
        // Return empty array for all Task List types except "counter" type
        if (!$taskItems->isEmpty() && $taskItems->first()->getTaskList()->getType() !== TaskListTypes::COUNTER) {
            return $views;
        }
        foreach ($taskItems as $taskItem) {
            $views[$taskItem->getId()] = $this->getIncrementItemForm($taskItem)->createView();
        }

        return $views;
    }

    /**
     * @param TaskList $taskList
     *
     * @return FormInterface
     */
    protected function getArchiveListForm(TaskList $taskList): FormInterface
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
    protected function getArchiveListFormsViews(iterable $taskItems): array
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
    protected function getUnsubscribeForm(TaskList $taskList): FormInterface
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
    protected function getUnsubscribeFormsViews(iterable $taskItems): array
    {
        $views = [];
        foreach ($taskItems as $taskItem) {
            $views[$taskItem->getId()] = $this->getUnsubscribeForm($taskItem)->createView();
        }

        return $views;
    }

    /**
     * @param string      $formName
     * @param string|null $token
     */
    protected function checkCsrf(string $formName, ?string $token): void
    {
        if (!$this->isCsrfTokenValid($formName, $token)) {
            throw new ValidatorException('validation.invalid_csrf');
        }
    }
}
