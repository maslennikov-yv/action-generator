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

    protected function getStub()
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

    protected function buildClass($name)
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
            return false;
        }
    }

    protected function getNameInput(): string
    {
        return $this->getAction();
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace .
            $this->makeNamespace([
                'Actions',
                ...$this->getDirs(),
                $this->getPlural(),
            ]);
    }

    protected function getPath($name): string
    {
        return $this->laravel['path'] .
            $this->makeFolder([
                'Actions',
                ...$this->getDirs(),
                $this->getPlural(),
                $this->getNameInput(),
            ]) . '.php';
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the action already exists'],
            ['test', null, InputOption::VALUE_NONE, 'Create a DataSet and Test for the action'],
        ];
    }

    private function parseName($name)
    {
        $delimiter = false;
        if (Str::contains($name, ['/', '\\'])) {
            $name = str_replace('\\', '/', $name);
            $delimiter = '/';
        }

        $dirs = false;
        $action = $name;
        if ($delimiter) {
            $action = Str::afterLast($name, $delimiter);
            $dirs = preg_split("|$delimiter|", Str::chopEnd($name, $delimiter . $action));
        }

        $verb = $third = $single = $plural = null;
        if (Str::doesntContain($action, ['{', '}'])) {
            if (Str::contains($action, ':')) {
                $this->error('Syntax error near symbol ":"');
                exit(1);
            }
            $matches = Str::ucsplit($action);
            $verb = array_shift($matches);
            $third = $this->thirdPerson($verb);
            $single = implode($matches);
            $plural = Str::plural($single);
        } else {
            preg_match_all('/[{](.+?)[}]/', $action, $matches);
            if (count($matches[0]) === 0 || count($matches[0]) > 2) {
                $this->error('Bracket sequence error');
                exit(1);
            }
            if (count($matches[0]) === 1) {
                $position = Str::position($action, $matches[0][0]);
                if ($position === false) {
                    $this->error(sprintf('Syntax error near "%s', $matches[0][0]));
                    exit(1);
                }
                if ($position === 0) {
                    list($verb, $third) = $this->parseExpression($matches[1][0], [$this, 'thirdPerson']);
                    $single = Str::chopStart($action, $matches[0][0]);
                    $plural = Str::plural($single);
                } else {
                    list($single, $plural) = $this->parseExpression($matches[1][0], [Str::class, 'plural']);
                    $verb = Str::chopEnd($action, $matches[0][0]);
                    $third = $this->thirdPerson($verb);
                }
            } else {
                list($verb, $third) = $this->parseExpression($matches[1][0], [$this, 'thirdPerson']);
                list($single, $plural) = $this->parseExpression($matches[1][1], [Str::class, 'plural']);
            }
        }

        return compact('verb', 'third', 'single', 'plural', 'dirs');
    }

    private function parseExpression(string $expression, callable $cb)
    {
        $parts = preg_split("/:/", $expression);
        if (count($parts) === 1) {
            $first = array_shift($parts);
            $second = $cb($first);
        } elseif (count($parts) === 2) {
            $first = array_shift($parts);
            $second = array_shift($parts);
        } else {
            $this->error(sprintf('Syntax error near "%s"', $expression));
            exit(1);
        }
        return [$first, $second];
    }

    private function thirdPerson($verb)
    {
        if (Str::endsWith($verb, 'y')) {
            return Str::replaceMatches('/y$/', 'ies', $verb);
        } elseif (Str::endsWith($verb, ['o', 'ch', 's', 'sh', 'x', 'z'])) {
            return $verb . 'es';
        } else {
            return $verb . 's';
        }
    }
}
