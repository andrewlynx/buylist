<?php

namespace App\Validator;

use App\Constant\AppConstant;
use InvalidArgumentException;

class Locale
{
    /**
     * @param string $locale
     * @param bool   $throwException
     *
     * @return bool
     */
    public static function validateLocale(string $locale, bool $throwException = false): bool
    {
        if (!in_array($locale, AppConstant::APP_LOCALES)) {
            if ($throwException) {
                throw new InvalidArgumentException('validation.incorrect_locale');
            } else {
                return false;
            }
        }

        return true;
    }
}
