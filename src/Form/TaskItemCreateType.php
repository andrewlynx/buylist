<?php

namespace App\Form;

use App\Entity\TaskList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskItemCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TaskList $taskList */
        $taskList = $options['data']['taskList'];

        $builder
            ->add('name', null, [
                'label' => 'task_item.name'
            ])
            ->add('qty', null, [
                'required' => false,
                'label' => 'task_item.qty'
            ])
            ->add('list_id', HiddenType::class, [
                'data' => $taskList->getId()
            ])
            ->add('add', SubmitType::class, [
                'label' => 'form.add'
            ])
        ;
    }
}
