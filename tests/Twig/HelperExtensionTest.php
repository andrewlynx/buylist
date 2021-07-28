<?php

namespace App\Tests\Twig;

use App\Twig\HelperExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFilter;

class HelperExtensionTest extends WebTestCase
{
    public function testGetFilters()
    {
        $token = $this->createMock(TokenStorageInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $helperExtension = new HelperExtension($token, $translator);
        $this->assertTrue($helperExtension->getFilters()[0] instanceof TwigFilter);
    }
}
