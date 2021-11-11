<?php

namespace App\Constant;

class AppConstant
{
    public const JSON_STATUS_SUCCESS = 'success';
    public const JSON_STATUS_ERROR = 'error';

    public const DEFAULT_LOCALE = 'en';

    public const APP_LOCALES = [
        self::DEFAULT_LOCALE,
        'ua',
    ];

    public const APP_LOCALES_EXTENDED = [
        self::DEFAULT_LOCALE => 'English',
        'ua' => 'Українська',
    ];
}
