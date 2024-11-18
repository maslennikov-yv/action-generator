<?php

namespace Maslennikov\LaravelActions\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Maslennikov\LaravelActions\Traits\HasAction;

class ActionDataMakeCommand extends GeneratorCommand
{
    use HasAction;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action:data {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Data for Action';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Data';

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
            $special = $stubsPath . '/action.data.' . Str::snake($this->getVerb()) . '.stub';
            if (file_exists($special)) {
                return $special;
            }

            $published = $stubsPath . '/action.data.stub';
            if (file_exists($published)) {
                return $published;
            }
        }

        return __DIR__ . '/../stubs/action.data.stub';
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

        return $this->replaceNamespace($stub, $name)
            ->replaceVars($stub)
            ->replaceClass($stub, $name);
    }

    protected function replaceVars(&$stub)
    {
        $search = [
            '{{modelKeyName}}',
            '{{model}}',
        ];
        $replace = [
            $this->getModelKeyName(),
            $this->getSingle(),
        ];
        $stub = str_replace($search, $replace, $stub);

        return $this;
    }

    protected function getNameInput(): string
    {
        return $this->getDataName();
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Data\\' . $this->getFolder();
    }

    protected function getPath($name)
    {
        return $this->laravel['path'] . '/' . 'Data' . '/' . $this->getFolder() . '/' . $this->getNameInput() . '.php';
    }
}
