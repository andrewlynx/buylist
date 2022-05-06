<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskListPublic extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $status = $options['data']['status'];
        $builder
            ->add('status', HiddenType::class, [
                'data' => !$status,
            ])
            ->add('publish', SubmitType::class, [
                'label' => $status ? 'list.unpublish' : 'list.publish',
                'attr' => ['class' => 'btn btn-outline-primary list-archive iconly-brokenSend'],
            ])
        ;
    }
}
