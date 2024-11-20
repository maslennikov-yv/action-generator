<?php

namespace Maslennikov\LaravelActions\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Maslennikov\LaravelActions\Traits\HasAction;
use Maslennikov\LaravelActions\Traits\HasImport;

class ActionTestMakeCommand extends GeneratorCommand
{
    use HasAction;
    use HasImport;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action:test {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Test for Action';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Test';

    /**
     * Args of action
     *
     * @var array
     */
    protected $args = [];

    public function getArgs($key = null): mixed
    {
        if ($key !== null) {
            return $this->args[$key] ?? null;
        }
        return $this->args;
    }

    protected function getStub()
    {
        if (is_dir($stubsPath = $this->laravel->basePath('stubs'))) {
            $special = $stubsPath . '/action.test.' . Str::snake($this->getVerb()) . '.stub';
            if (file_exists($special)) {
                return $special;
            }

            $published = $stubsPath . '/action.test.stub';
            if (file_exists($published)) {
                return $published;
            }
        }

        return realpath(__DIR__ . '/../stubs/action.test.stub');
    }

    public function handle()
    {
        $name = $this->argument('name');
        $this->args = $this->parseName($name);

        return parent::handle();
    }

    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $this->injectImport($this->getDataNamespace(), $stub)
            ->injectImport($this->getModelNamespace(), $stub)
            ->injectImport($this->getActionNamespace(), $stub);

        $search = [
            '{{title}}',
            '{{dataset}}',
            '{{data}}',
            '{{action}}',
            '{{model}}',
            '{{modelKeyName}}',
        ];

        $replace = [
            $this->getDatasetTitle(),
            $this->getDatasetKey(),
            $this->getDataName(),
            $this->getAction(),
            $this->getSingle(),
            $this->getModelKeyName(),
        ];

        return str_replace($search, $replace, $stub);
    }

    protected function getNameInput(): string
    {
        $verb = trim($this->argument('verb'));
        $model = trim($this->argument(($verb === 'Index') ? 'plural' : 'single'));

        return $verb . $model . 'Test';
    }

    protected function getPath($name)
    {
        return base_path('tests') . '/Feature/Actions/' . $this->getFolder() . '/' . $this->getNameInput() . '.php';
    }
}
