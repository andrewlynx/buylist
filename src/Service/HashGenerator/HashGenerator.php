<?php

namespace App\Service\HashGenerator;

class HashGenerator
{
    /**
     * @var string
     */
    private static $allowedSymbols = 'abcdefghijklmnopqsrtuvwxyz0123456789';

    /**
     * @param int $length
     *
     * @return string
     */
    public static function generate(int $length = 32): string
    {
        $i = 1;
        $hash = '';
        while ($i < $length) :
            $hash .= self::getRandomSymbol();
            $i++;
        endwhile;

        return $hash;
    }

    /**
     * @return string
     */
    private static function getRandomSymbol(): string
    {
        $symbolsArray = str_split(static::$allowedSymbols);

        return $symbolsArray[array_rand($symbolsArray)];
    }
}
