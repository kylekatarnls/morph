<?php

declare(strict_types=1);

namespace Morph\Tests\Fixtures;

final class User
{
    #[Exposed]
    public int $id;

    /**
     * First name(s) / Surname(s).
     *
     * Includes middle name(s).
     *
     * @exposed
     */
    public string $firstName;

    /**
     * Last (family) name(s).
     */
    public string $lastName;

    /**
     * Bank account number.
     */
    protected string $bankAccountNumber;

    /**
     * Login password.
     */
    private string $password;

    public function __construct(
        int $id,
        string $firstName,
        string $lastName,
        string $bankAccountNumber,
        string $password
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->bankAccountNumber = $bankAccountNumber;
        $this->password = $password;
    }

    /**
     * Complete first and last name.
     */
    public function getName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function isSafe(): bool
    {
        return strlen($this->password) >= 8;
    }
}
