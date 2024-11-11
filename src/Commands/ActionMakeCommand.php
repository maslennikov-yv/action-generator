<?php

namespace Maslennikov\LaravelActions\Commands;

use Illuminate\Console\GeneratorCommand;

class ActionMakeCommand extends GeneratorCommand
{
    protected function getStub()
    {
        return __DIR__ . '/stubs/test.stub';
    }
}