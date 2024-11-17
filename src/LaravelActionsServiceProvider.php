<?php

declare(strict_types=1);

namespace Maslennikov\LaravelActions;

use Illuminate\Support\ServiceProvider;

class LaravelActionsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('action.php'),
            ], 'config');

            $this->commands([
                Commands\ActionStubsPublishCommand::class,
                Commands\ActionMakeCommand::class,
            ]);
        }
    }
}