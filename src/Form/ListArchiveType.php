<?php

namespace App\Form;

use App\Entity\TaskList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ListArchiveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $status = $options['data']['status'];
        $builder
            ->add('status', HiddenType::class, [
                'data' => !$status,
            ])
            ->add('archive', SubmitType::class, [
                'label' => $status ? 'list.restore' : 'list.archive',
                'attr' => ['class' => 'btn btn-outline-primary'],
            ])
        ;
    }
}
