<?php

namespace Maslennikov\LaravelActions\Commands;

use Illuminate\Console\GeneratorCommand;
use Maslennikov\LaravelActions\Traits\HasAction;

class ActionInterfaceMakeCommand extends GeneratorCommand
{
    use HasAction;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action:interface {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Interface for Action';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Interface';

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
            $published = $stubsPath . '/action.stub';
            if (file_exists($published)) {
                return $published;
            }
        }

        return __DIR__ . '/../stubs/interface.stub';
    }

    protected function buildClass($name)
    {
        $this->args = $this->parseName($this->argument('name'));
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceDataQualifiedName($stub)
            ->replaceData($stub)
            ->replaceClass($stub, $name);
    }

    protected function replaceDataQualifiedName(&$stub): static
    {
        $dataQualifiedName =
            $this->laravel->getNamespace() .
            'Data' . '\\' .
            $this->getFolder() . '\\' .
            $this->getModelName() . 'Data';

        $stub = str_replace('{{dataQualifiedName}}', $dataQualifiedName, $stub);

        return $this;
    }

    protected function getModelName(): string
    {
        return $this->getVerb() . $this->getModel();
    }

    protected function getNameInput(): string
    {
        return $this->getThird() . $this->getModel();
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Contracts\\Actions\\' . $this->getFolder();
    }

    protected function getPath($name)
    {
        return $this->laravel['path'] . '/' . 'Contracts/Actions' . '/' . $this->getFolder(
            ) . '/' . $this->getNameInput() . '.php';
    }
}
