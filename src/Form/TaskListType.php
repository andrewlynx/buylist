<?php

namespace App\Form;

use App\Entity\TaskItem;
use App\Entity\TaskList;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TaskListType extends AbstractType
{
    /**
     * @var TokenStorageInterface
     */
    private $token;

    /**
     * @param TokenStorageInterface $token
     */
    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $this->token->getToken()->getUser();
        /** @var TaskList $taskList */
        $taskList = $options['data'];

        $builder
            ->add('name', null, [
                'label' => 'list.name',
                'attr' => [
                    'maxlength' => 64,
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'list.description',
            ])
            ->add('taskItems', CollectionType::class, [
                'entry_type' => TaskItemCreateType::class,
                'label' => 'list.items',
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'form.save'
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                /** @var TaskList $taskList */
                $taskList = $event->getData();
                /** @var TaskItem $taskItem */
                foreach ($taskList->getTaskItems() as $taskItem) {
                    $taskItem->setTaskList($taskList);
                }
            })
        ;

        if ($taskList->getCreator() === $user) {
            $builder
                ->add('favouriteUsers', ChoiceType::class, [
                    'required' => false,
                    'mapped' => false,
                    'multiple' => true,
                    'choices' => $user->getFavouriteUsers(),
                    'choice_label' => function ($choice, $key, $value) {
                        return $choice->getEmail();
                    },
                    'data' => $user->getFavouriteUsers()->isEmpty() ? [] : $taskList->getShared()->toArray(),
                ])
                ->add('users', CollectionType::class, [
                    'entry_type' => ShareListEmailType::class,
                    'required' => false,
                    'mapped' => false,
                    'allow_add' => true,
                    'data' => $taskList->getSimpleUsersEmails(),
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskList::class,
        ]);
    }
}
