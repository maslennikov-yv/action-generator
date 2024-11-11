<?php

namespace Maslennikov\LaravelActions;

use Illuminate\Support\ServiceProvider;
use Maslennikov\LaravelActions\Commands\ActionMakeCommand;

class LaravelActionsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ActionMakeCommand::class,
            ]);
        }
    }
}