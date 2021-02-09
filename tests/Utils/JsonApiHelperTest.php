<?php

namespace App\Tests\Utils;

use App\Utils\JsonApiHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JsonApiHelperTest extends WebTestCase
{
    public function testGetFormField()
    {
        $name = JsonApiHelper::getFormField('form', 'field');

        $this->assertEquals('form[field]', $name);
    }
}
