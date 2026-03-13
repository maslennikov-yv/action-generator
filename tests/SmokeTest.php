<?php

declare(strict_types=1);

namespace Maslennikov\LaravelActions\Tests;

use Maslennikov\LaravelActions\LaravelActionsServiceProvider;
use Orchestra\Testbench\TestCase;

class SmokeTest extends TestCase
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelActionsServiceProvider::class,
        ];
    }

    public function test_make_action_command_runs_successfully(): void
    {
        $this->artisan('make:action GetArticle --test --force')
            ->assertExitCode(0);
    }
}

