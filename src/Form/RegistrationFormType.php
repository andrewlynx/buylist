<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'label' => 'user.email',
            ])
            ->add('nickName', null, [
                'required' => false,
                'label' => 'user.nickname',
                'help' => 'user.nickname_help',
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'user.enter_password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'user.password_limit',
                        //'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
                'label' => 'user.password',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $user = $event->getData();
                if (!$user) {
                    return;
                }

                if (empty($user['nickName']) && !empty($user['email'])) {
                    $user['nickName'] =
                        substr($user['email'], 0, strpos($user['email'], '@') ?: null)
                        ?? $user['email'];
                }
                $user['nickName'] = empty($user['nickName']) ? 'user'.rand(100000, 999999) : $user['nickName'];

                $event->setData($user);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
