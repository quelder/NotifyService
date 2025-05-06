<?php

namespace App\Services;

use App\Models\User;

interface NotifierInterface
{
    public function send(User $user, string $message): void;
}