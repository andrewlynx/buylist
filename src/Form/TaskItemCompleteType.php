<?php

namespace App\Form;

use App\Entity\TaskItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskItemCompleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var TaskItem $taskItem */
        $taskItem = $options['data'];
        $builder
            ->add('id', HiddenType::class, [
                'data' => $taskItem->getId(),
            ])
            ->add('completed', HiddenType::class)
        ;
    }
}
