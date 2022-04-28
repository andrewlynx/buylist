<?php

namespace App\DataFixtures;

use App\Constant\TaskListTypes;
use App\Entity\TaskList;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        $this->makeUsers();
        $this->makeAdmin();
        $this->em->flush();

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find(1);
        /** @var User $user2 */
        $user2 = $this->em->getRepository(User::class)->find(2);

        $this->createTaskListByType(TaskListTypes::DEFAULT, $user);
        $this->createTaskListByType(TaskListTypes::COUNTER, $user, $user2);
        $this->createTaskListByType(TaskListTypes::COUNTER, $user2, $user);

        $this->em->flush();
    }

    /**
     * @param int       $type
     * @param User      $user
     * @param User|null $shared
     *
     * @return TaskList
     *
     * @throws Exception
     */
    private function createTaskListByType(int $type, User $user, ?User $shared = null): TaskList
    {
        $taskList = (new TaskList())
            ->setName($this->getTaskListName($type))
            ->setDescription('Simple Description')
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime())
            ->setCreator($user)
            ->setType($type);

        if ($shared) {
            $taskList->addShared($shared);
        }

        $this->em->persist($taskList);

        return $taskList;
    }



    /**
     * @param int $type
     *
     * @return string
     */
    private function getTaskListName(int $type): string
    {
        switch ($type) {
            case TaskListTypes::COUNTER:
                $name = 'New Counter List';
                break;
            case TaskListTypes::TODO:
                $name = 'New Todo List';
                break;
            default:
                $name = 'New Task List';
        }

        return $name;
    }

    private function makeUsers(): void
    {
        for ($i = 1; $i < 5; $i++) {
            $name = 'user'.$i;
            $$name = new User();

            $$name->setEmail($name.'@example.com');
            $$name->setPassword($this->passwordEncoder->encodePassword(
                $$name,
                'test'
            ));

            $this->em->persist($$name);
        }
    }

    private function makeAdmin(): void
    {
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setPassword($this->passwordEncoder->encodePassword(
            $admin,
            'test'
        ));
        $admin->addRole(User::ROLE_ADMIN);
        $admin->setHelpers(false);
        $this->em->persist($admin);
    }
}
