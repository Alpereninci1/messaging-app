<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\EloquentMessageRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MessageRepositoryInterface::class, EloquentMessageRepository::class);
    }
}
