<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class JsonPlaceholderTaskFetcher implements TaskFetcherInterface
{
    public function fetch(): \Illuminate\Support\Collection
    {
        $response = Http::get('https://jsonplaceholder.typicode.com/todos');
        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch tasks');
        }

        return collect($response->json())
            ->filter(fn($t) => !$t['completed'] && $t['userId'] <= 5);
    }
}