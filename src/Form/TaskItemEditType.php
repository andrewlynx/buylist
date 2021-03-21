<?php

namespace App\Form;

use App\Entity\TaskItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskItemEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'task_item.name'
            ])
            ->add('qty', null, [
                'required' => false,
                'label' => 'task_item.qty'
            ])
            ->add('id', HiddenType::class)
            ->add('save', SubmitType::class, [
                'label' => 'form.save'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskItem::class,
        ]);
    }
}
