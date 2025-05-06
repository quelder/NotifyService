<?php

namespace App\Services;

use Illuminate\Support\Collection;

interface TaskFetcherInterface
{
    public function fetch(): Collection;
}