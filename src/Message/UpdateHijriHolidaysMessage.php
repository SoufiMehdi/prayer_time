<?php

namespace App\Message;

class UpdateHijriHolidaysMessage
{
    public function __construct(
        private readonly ?\DateTimeInterface $forMonth = null
    ) {
    }
    public function getForMonth(): ?\DateTimeInterface
    {
        return $this->forMonth;
    }
}
