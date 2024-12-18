<?php

declare(strict_types=1);

namespace Maslennikov\LaravelActions\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\Cache;
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
        $this->args = $this->parseName($this->argument('name'));
        $crud = $this->preProcessVerb($this->getVerb());
        $key = 'make_action_tips_'  . $this->getSingle();
        if (is_array($crud)) {

            Cache::put($key, []);

            foreach ($crud as $verb) {
                $action = implode('/', [
                    ...$this->getDirs(),
                    sprintf(
                        '{%s:%s}{%s:%s}',
                        $this->getVerbsAlias($verb),
                        $this->getInterfacesAlias($verb),
                        $this->getSingle(),
                        $this->getPlural(),
                    ),
                ]);
                $this->call('make:action', [
                    'name' => $action,
                    '--test' => (bool)$this->option('test'),
                    '--force' => (bool)$this->option('force'),
                ]);
            }

            $tips = Cache::pull($key);

            if (is_array($tips)) {
                $this->info("Don't forget to link the interface to the implementation:");
                foreach ($tips as $tip) {
                    $this->comment($tip);
                }
                $this->newLine();
            }

            return self::SUCCESS;
        }

        if (parent::handle() === false && !$this->option('force')) {
            return self::FAILURE;
        }

        $this->createData();
        $this->createInterface();

        if ($this->option('test')) {
            $this->createDataset();
            $this->createTest();
        }

        $link = $this->getInterfaceNamespace() . '::class => ' .
            $this->getActionNamespace() . '::class,';

        $tips = Cache::get($key);

        if (is_array($tips)) {
            $tips[] = $link;
            Cache::put($key, $tips);
        } else {
            $this->info("Don't forget to link the interface to the implementation:");
            $this->comment($link);
            $this->newLine();
        }

        return self::SUCCESS;
    }

    protected function createData()
    {
        $this->call('make:action:data', [
            'name' => $this->argument('name'),
            '--force' => (bool)$this->option('force'),
        ]);
    }

    protected function createInterface()
    {
        $this->call('make:action:interface', [
            'name' => $this->argument('name'),
            '--force' => (bool)$this->option('force'),
        ]);
    }

    protected function createDataset()
    {
        $this->call('make:action:dataset', [
            'name' => $this->argument('name'),
            '--force' => (bool)$this->option('force'),
        ]);
    }

    protected function createTest()
    {
        $this->call('make:action:test', [
            'name' => $this->argument('name'),
            '--force' => (bool)$this->option('force'),
        ]);
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

    protected function preProcessVerb(string $verb): array|string
    {
        $verb = strtolower($verb);
        $crud = [
            'destroy',
            'update',
            'show',
            'create',
            'index',
        ];
        if ($verb === '*') {
            return $crud;
        } elseif (str_contains($verb, '+')) {
            return array_values(
                array_filter($crud, function ($action) use ($verb) {
                    return str_contains($verb, mb_substr($action, 0, 1));
                })
            );
        } elseif (str_contains($verb, '-')) {
            return array_values(
                array_filter($crud, function ($action) use ($verb) {
                    return !str_contains($verb, mb_substr($action, 0, 1));
                })
            );
        }
        return $verb;
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the action already exists'],
            ['test', null, InputOption::VALUE_NONE, 'Create a DataSet and Test for the action'],
        ];
    }
}
