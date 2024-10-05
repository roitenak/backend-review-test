<?php

namespace App\Dto\SearchOutput;

class Data
{
    public function __construct(
        public array $events,
        public array $stats,
    ) {
    }
}
