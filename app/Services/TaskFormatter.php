<?php

namespace App\Services;

use Illuminate\Support\Collection;

class TaskFormatter
{
    public function format(Collection $tasks): string
    {
        return $tasks->map(fn($task) => "- " . $task['title'])->implode("\n");
    }
}