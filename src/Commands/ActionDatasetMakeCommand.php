<?php

namespace Maslennikov\LaravelActions\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Maslennikov\LaravelActions\Traits\HasAction;
use Maslennikov\LaravelActions\Traits\HasImport;

class ActionDatasetMakeCommand extends GeneratorCommand
{
    use HasAction;
    use HasImport;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action:dataset {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Dataset for Action test';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Dataset';

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
            $special = $stubsPath . '/action.dataset.' . Str::snake($this->getVerb()) . '.stub';
            if (file_exists($special)) {
                return $special;
            }

            $published = $stubsPath . '/action.dataset.stub';
            if (file_exists($published)) {
                return $published;
            }
        }

        return realpath(__DIR__ . '/../stubs/action.dataset.stub');
    }

    protected function getNameInput()
    {
        return 'Datasets';
    }

    protected function getPath($name)
    {
        return base_path('tests') . '/Feature/Actions/' . $this->getFolder() . '/' . $this->getNameInput() . '.php';
    }

    protected function buildClass($name)
    {
        $path = $this->getPath($name);
        $current = file_get_contents($path);
        $stub = $this->files->get($this->getStub());

        $this->injectImport($this->getDataNamespace(), $current, str_contains($stub, '{{data}}'))
            ->injectImport($this->getModelNamespace(), $current, str_contains($stub, '{{model}}'));

        $key = $this->getDatasetKey();
        if (str_contains($current, $key)) {
            return $current;
        }

        $search = [
            '{{key}}',
            '{{data}}',
            '{{modelKeyName}}',
            '{{model}}',
        ];
        $replace = [
            $key,
            $this->getDataName(),
            $this->getModelKeyName(),
            $this->getSingle(),
        ];
        $dataset = str_replace($search, $replace, $stub);

        return preg_replace('#^//$#m', $dataset, $current, 1);
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        // First we need to ensure that the given name is not a reserved word within the PHP
        // language and that the class name will actually be valid. If it is not valid we
        // can error now and prevent from polluting the filesystem using invalid files.
        if ($this->isReservedName($this->getNameInput())) {
            $this->components->error('The name "' . $this->getNameInput() . '" is reserved by PHP.');

            return false;
        }

        $this->args = $this->parseName($this->argument('name'));

        $name = $this->getNameInput();
        $path = $this->getPath($name);

        if (!$this->alreadyExists($this->getNameInput())) {
            $this->makeDirectory($path);
            $this->files->put($path, "<?php\n\n//use\n\n//");
        }

        $this->files->put($path, $this->sortImports($this->buildClass($name)));

        $info = $this->type;
        $this->components->info(sprintf('%s [%s] created successfully.', $info, $path));

        return true;
    }
}
