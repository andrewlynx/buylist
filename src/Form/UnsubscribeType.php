<?php

namespace App\Form;

use App\Entity\TaskList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class UnsubscribeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TaskList $list */
        $list = $options['data']['task_list'];
        $builder
            ->add('list_id', HiddenType::class, [
                'data' => $list->getId(),
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'list.unsubscribe'
            ])
        ;
    }
}
