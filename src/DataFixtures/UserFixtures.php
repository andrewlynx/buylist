<?php

namespace App\DataFixtures;

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
        for ($i = 1; $i < 5; $i++) {
            $name = 'user'.$i;
            $$name = new User();

            $$name->setEmail($name.'@example.com');
            $$name->setPassword($this->passwordEncoder->encodePassword(
                $$name,
                'test'
            ));

            $manager->persist($$name);
        }

        // Create Admin
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setPassword($this->passwordEncoder->encodePassword(
            $admin,
            'test'
        ));
        $admin->addRole(User::ROLE_ADMIN);
        $manager->persist($admin);

        $manager->flush();

        $user = $manager->getRepository(User::class)->find(1);
        $taskList = (new TaskList())
            ->setName('New Task List')
            ->setCreator($user)
            ->setDescription('Simple Description')
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());

        $manager->persist($taskList);
        $manager->flush();
    }
}
