<?php

namespace Maslennikov\LaravelActions\Commands;

use Illuminate\Console\GeneratorCommand;

class ActionMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected function getStub()
    {
        return __DIR__ . '/stubs/test.stub';
    }
}