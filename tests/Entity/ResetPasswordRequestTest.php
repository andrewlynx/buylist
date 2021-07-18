<?php

namespace App\Tests\Entity;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetPasswordRequestTest extends WebTestCase
{
    public function testEntity()
    {
        $user = new User();
        $date = new \DateTime();
        $resetPasswordRequest = new ResetPasswordRequest($user, $date, 'selector', 'hash');

        $this->assertSame(null, $resetPasswordRequest->getId());
        $this->assertSame($user, $resetPasswordRequest->getUser());
    }
}