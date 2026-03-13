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
            ], 'action-config');

            $this->publishes([
                __DIR__ . '/../.cursor/skills/laravel-actions-generator' => base_path('.cursor/skills/laravel-actions-generator'),
            ], 'action-skill');

            $this->commands([
                Commands\ActionStubsPublishCommand::class,
                Commands\ActionMakeCommand::class,
                Commands\ActionInterfaceMakeCommand::class,
                Commands\ActionDataMakeCommand::class,
                Commands\ActionDatasetMakeCommand::class,
                Commands\ActionTestMakeCommand::class,
            ]);
        }
    }
}