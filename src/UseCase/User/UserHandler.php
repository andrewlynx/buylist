<?php

namespace App\UseCase\User;

use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param User $user
     * @param User $friend
     */
    public function addToFavourites(User $user, User $friend): void
    {
        $user->addFavouriteUser($friend);
        $this->em->flush();
    }

    /**
     * @param User $user
     * @param User $friend
     */
    public function removeFromFavourites(User $user, User $friend): void
    {
        $user->removeFromFavouriteUsers($friend);
        $this->em->flush();
    }

    /**
     * @param User $user
     * @param User $friend
     */
    public function blockUser(User $user, User $friend): void
    {
        $user->banUser($friend);
        $taskLists = $user->getTaskLists()->filter(
            function (TaskList $taskList) use ($friend) {
                return $taskList->getShared()->contains($friend);
            }
        );
        /** @var TaskList $taskList */
        foreach ($taskLists as $taskList) {
            $taskList->removeShared($friend);
        }

        $friendTaskLists = $friend->getTaskLists()->filter(
            function (TaskList $taskList) use ($user) {
                return $taskList->getShared()->contains($user);
            }
        );
        /** @var TaskList $taskList */
        foreach ($friendTaskLists as $taskList) {
            $taskList->removeShared($user);
        }

        $user->removeFromFavouriteUsers($friend);
        $friend->removeFromFavouriteUsers($user);

        $this->em->flush();
    }

    /**
     * @param User $user
     * @param User $friend
     */
    public function unblockUser(User $user, User $friend): void
    {
        $user->removeFromBan($friend);
        $this->em->flush();
    }
}
