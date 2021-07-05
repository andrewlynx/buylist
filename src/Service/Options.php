<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Options
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

    /**
     * @return bool
     */
    public function helpers(): bool
    {
        /** @var User $user */
        $user = $this->token->getToken()->getUser();

        return $user->getHelpers();
    }
}