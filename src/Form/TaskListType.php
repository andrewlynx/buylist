<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'list.name',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'list.description',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'form.save'
            ])
        ;
    }
}
