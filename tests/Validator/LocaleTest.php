<?php

namespace App\Tests\Validator;

use App\Validator\Locale;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LocaleTest extends WebTestCase
{
    public function testValidateLocale()
    {
        $this->assertTrue(Locale::validateLocale('en'));
        $this->assertTrue(Locale::validateLocale('ua'));
        $this->assertFalse(Locale::validateLocale('kk'));

        $this->expectException(\InvalidArgumentException::class);
        Locale::validateLocale('kk', true);
    }
}
