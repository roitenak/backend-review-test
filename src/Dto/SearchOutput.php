<?php

namespace App\Dto;

use App\Dto\SearchOutput\Data;
use App\Dto\SearchOutput\Meta;

class SearchOutput
{
    public function __construct(
        public Meta $meta,
        public Data $data,
    ) {
    }
}
