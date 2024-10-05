<?php

namespace App\Dto;

class SearchInput
{
    public \DateTimeImmutable $date;

    public function __construct(
        public string $keyword = '',
    ) {
        $this->date ??= new \DateTimeImmutable();
    }
}
