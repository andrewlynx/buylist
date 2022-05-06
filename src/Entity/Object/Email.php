<?php

namespace App\Entity\Object;

use App\Exceptions\UserException;

class Email
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     *
     * @throws UserException
     */
    public function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new UserException('validation.incorrect_email');
        }
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
