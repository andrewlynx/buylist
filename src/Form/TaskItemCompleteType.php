<?php

namespace App\Form;

use App\Entity\TaskItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskItemCompleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TaskItem $taskItem */
        $taskItem = $options['data'];
        $builder
            ->add('id', HiddenType::class, [
                'data' => $taskItem->getId(),
            ])
            ->add('completed', CheckboxType::class, [
                'label' => ' ',
            ])
        ;
    }
}