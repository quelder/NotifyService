<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramNotification;
use App\Models\User;
use App\Services\NotifierInterface;
use App\Services\TaskFetcherInterface;
use App\Services\TaskFormatter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyTasks extends Command
{
    protected $signature = 'notify:tasks';
    protected $description = 'Notify users about tasks from external API';

    public function __construct(
        private TaskFetcherInterface $fetcher,
        private TaskFormatter $formatter,
        private NotifierInterface $notifier
    ) {
        parent::__construct();
    }


    public function handle(): int
    {
        try {
            $tasks = $this->fetcher->fetch();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }


        $users = User::where('subscribed', true)->get();
        foreach ($users as $user) {
            App::setLocale($user->language_code ?? 'en');
            $message = __('general.new_tasks') . "\n" . $this->formatter->format($tasks);

            $this->notifier->send($user, $message);
        }

        $this->info("Dispatched notifications to " . $users->count() . " users.");
        return 0;
    }
}
