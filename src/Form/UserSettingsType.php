<?php

namespace App\Form;

use App\Constant\AppConstant;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('current_password', PasswordType::class, [
                'label' => 'user.password',
                'mapped' => false,
                'required' => false,
                'help' => 'user.password_change_help',
            ])
            ->add('new_password', PasswordType::class, [
                'label' => 'user.new_password',
                'mapped' => false,
                'required' => false,
                'help' => 'user.password_change_help',
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'user.language',
                'choices' => AppConstant::APP_LOCALES,
                'choice_label' => function ($key, $value) {
                    return strtoupper($key);
                },
                'required' => false,
                'choice_translation_domain' => false,
            ])
            ->add('add', SubmitType::class, [
                'label' => 'form.save'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
