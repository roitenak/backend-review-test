<?php

namespace App\Dto\SearchOutput;

class Meta
{
    public function __construct(
        public int $totalEvents,
        public int $totalPullRequests = 0,
        public int $totalCommits = 0,
        public int $totalComments = 0,
    ) {
    }
}
