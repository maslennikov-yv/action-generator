<?php

declare(strict_types=1);

namespace Maslennikov\LaravelActions\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Maslennikov\LaravelActions\Traits\HasAction;
use Maslennikov\LaravelActions\Traits\HasImport;
use Symfony\Component\Console\Input\InputOption;

class ActionMakeCommand extends GeneratorCommand
{
    use HasAction;
    use HasImport;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action {name} {--force} {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Action';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Action';

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

    protected function getStub(): bool|string
    {
        if (is_dir($stubsPath = $this->laravel->basePath('stubs'))) {
            $special = $stubsPath . '/action.' . Str::snake($this->getVerb()) . '.stub';
            if (file_exists($special)) {
                return $special;
            }

            $published = $stubsPath . '/action.stub';
            if (file_exists($published)) {
                return $published;
            }
        }
        return realpath(__DIR__ . '/../stubs/action.stub');
    }

    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());
        return $this->injectImport($this->getDataNamespace(), $stub, '{{data}}')
            ->injectImport($this->getInterfaceNamespace(), $stub, '{{interface}}')
            ->injectImport($this->getModelNamespace(), $stub, '{{model}}')
            ->replaceNamespace($stub, $name)
            ->replaceInterface($stub)
            ->replaceModel($stub)
            ->replaceData($stub)
            ->replaceId($stub)
            ->replaceClass($stub, $name);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $this->args = $this->parseName($name);

        if (parent::handle() === false && !$this->option('force')) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function getNameInput(): string
    {
        return $this->getAction();
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Actions\\' . $this->getFolder();
    }

    protected function getPath($name)
    {
        return $this->laravel['path'] . '/Actions/' . $this->getFolder() . '/' . $this->getNameInput() . '.php';
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the action already exists'],
            ['test', null, InputOption::VALUE_NONE, 'Create a DataSet and Test for the action'],
        ];
    }
}
