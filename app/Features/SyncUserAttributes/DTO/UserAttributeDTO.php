<?php

namespace App\Features\SyncUserAttributes\DTO;

use Spatie\LaravelData\Data;

class UserAttributeDTO extends Data
{
    public function __construct(
        private string $email,
        private ?string $timeZone,
        private ?string $name
    )
    {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getTimeZone(): ?string
    {
        return $this->timeZone;
    }

    public function setTimeZone(?string $timeZone): void
    {
        $this->timeZone = $timeZone;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

}
