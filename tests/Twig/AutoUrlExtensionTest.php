<?php

namespace App\Tests\Twig;

use App\Twig\AutoUrlExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFilter;

class AutoUrlExtensionTest extends WebTestCase
{
    public function testGetFilters()
    {
        $helperExtension = new AutoUrlExtension();
        $this->assertTrue($helperExtension->getFilters()[0] instanceof TwigFilter);
    }
}
