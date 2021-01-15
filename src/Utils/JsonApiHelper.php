<?php

namespace App\Utils;

class JsonApiHelper
{
    /**
     * @param string $form
     * @param string $field
     *
     * @return string
     */
    public static function getFormField(string $form, string $field): string
    {
        return $form.'['.$field.']';
    }
}