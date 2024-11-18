<?php

declare(strict_types=1);

namespace Maslennikov\LaravelActions\Traits;

use Illuminate\Support\Str;

trait HasAction
{
    protected function getAction(): string
    {
        return $this->getVerb() . $this->getSingle();
    }

    protected function getInterface(): string
    {
        return $this->getThird() . $this->getSingle();
    }

    protected function getInterfaceNamespace(): string
    {
        return app()->getNamespace() .
            $this->makeNamespace([
                'Contracts',
                'Actions',
                ...$this->getDirs(),
                $this->getPlural(),
                $this->getInterface(),
            ]);
    }

    protected function replaceInterface(&$stub): static
    {
        $stub = str_replace('{{interface}}', $this->getInterface(), $stub);

        return $this;
    }

    protected function getDataName(): string
    {
        return $this->getVerb() . $this->getSingle() . 'Data';
    }

    protected function getDataNamespace(): string
    {
        return app()->getNamespace() .
            $this->makeNamespace([
                'Data',
                ...$this->getDirs(),
                $this->getPlural(),
                $this->getDataName(),
            ]);
    }

    protected function replaceData(&$stub): static
    {
        $stub = str_replace('{{data}}', $this->getDataName(), $stub);

        return $this;
    }

    protected function getSingle(): string
    {
        return trim($this->getArgs('single'));
    }

    protected function getModelNamespace(): string
    {
        return app()->getNamespace() .
            $this->makeNamespace([
                'Models',
                ...$this->getDirs(),
                $this->getSingle(),
            ]);
    }

    protected function replaceModel(&$stub): static
    {
        $stub = str_replace('{{model}}', $this->getSingle(), $stub);

        return $this;
    }

    protected function getModelKeyName(): string
    {
        return Str::snake($this->getSingle()) . '_id';
    }

    protected function replaceId(&$stub): static
    {
        $stub = str_replace('{{id}}', $this->getModelKeyName(), $stub);

        return $this;
    }

    protected function getVerb(): string
    {
        return trim($this->getArgs('verb'));
    }

    protected function getThird(): string
    {
        return trim($this->getArgs('third'));
    }

    protected function getPlural(): string
    {
        return trim($this->getArgs('plural'));
    }

    protected function getFolder(): string
    {
        return $this->makeFolder([
            ...$this->getDirs(),
            $this->getPlural(),
        ]);
    }

    protected function getFolderNamespace(): string
    {
        return $this->makeNamespace([
            ...$this->getDirs(),
            $this->getPlural(),
        ]);
    }

    private function makeFolder($parts)
    {
        return implode('/', $parts);
    }

    private function makeNamespace($parts)
    {
        return implode('\\', $parts);
    }

    protected function getDirs(): array
    {
        return $this->getArgs('dirs') ?
            $this->getArgs('dirs') : [];
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
            $dirs = explode($delimiter, Str::chopEnd($name, $delimiter . $action));
        }

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
        $parts = explode(':', $expression);
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

    private function thirdPerson($verb): string
    {
        if (Str::endsWith($verb, 'y')) {
            return Str::replaceMatches('/y$/', 'ies', $verb);
        } elseif (Str::endsWith($verb, ['o', 'ch', 's', 'sh', 'x', 'z'])) {
            return $verb . 'es';
        } else {
            return $verb . 's';
        }
    }

    abstract public function getArgs($key = null);
}