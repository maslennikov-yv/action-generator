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

    protected function getActionNamespace(): string
    {
        return app()->getNamespace() .
            $this->makeNamespace([
                'Actions',
                ...$this->getDirs(),
                $this->getPlural(),
                $this->getAction(),
            ]);
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

    protected function getDatasetKey(): string
    {
        return Str::snake($this->getSingle()) . '.' . Str::snake($this->getVerb());
    }

    protected function getDatasetTitle(): string
    {
        return sprintf('can %s a %s', $this->getVerb(), $this->getSingle());
    }

    protected function getRaw(): string
    {
        return trim($this->getArgs('raw'));
    }

    protected function getVerbsAlias(?string $key = null)
    {
        $verbs = config('action.verbs', [
            'destroy' => 'Destroy',
            'update' => 'Update',
            'show' => 'Show',
            'create' => 'Create',
            'index' => 'Index',
        ]);

        if ($key !== null) {
            return $verbs[$key] ?? null;
        }

        return $verbs;
    }

    protected function getInterfacesAlias(?string $key = null)
    {
        $interfaces = config('action.interfaces', [
            'destroy' => 'Destroys',
            'update' => 'Updates',
            'show' => 'Shows',
            'create' => 'Creates',
            'index' => 'Indexes',
        ]);

        if ($key !== null) {
            return $interfaces[$key] ?? null;
        }

        return $interfaces;
    }

    protected function getFolder(...$parts): string
    {
        return $this->makeFolder([
            ...$this->getDirs(),
            $this->getPlural(),
            ...$parts
        ]);
    }

    protected function getFolderNamespace(...$parts): string
    {
        return $this->makeNamespace([
            ...$this->getDirs(),
            $this->getPlural(),
            ...$parts
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

    protected function parseName($name)
    {
        $delimiter = false;
        if (Str::contains($name, ['/', '\\'])) {
            $name = str_replace('\\', '/', $name);
            $delimiter = '/';
        }

        $dirs = [];
        $action = $name;
        if ($delimiter) {
            $action = Str::afterLast($name, $delimiter);
            $dirs = explode(
                $delimiter,
                Str::chopStart(
                    Str::chopEnd($name, $delimiter . $action),
                    $delimiter
                )
            );
        }

        if (Str::doesntContain($action, ['{', '}'])) {
            if (Str::contains($action, ':')) {
                $this->error('Syntax error near symbol ":"');
                exit(1);
            }
            $matches = Str::ucsplit($action);
            $verb = array_shift($matches);
            $third = $this->third($verb);
            $raw = $single = implode($matches);
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
                    list($verb, $third) = $this->parseExpression($matches[1][0], [$this, 'third']);
                    $raw = $single = Str::chopStart($action, $matches[0][0]);
                    $plural = Str::plural($single);
                } else {
                    list($single, $plural) = $this->parseExpression($matches[1][0], [Str::class, 'plural']);
                    $raw = $matches[0][0];
                    $verb = Str::chopEnd($action, $matches[0][0]);
                    $third = $this->third($verb);
                }
            } else {
                list($verb, $third) = $this->parseExpression($matches[1][0], [$this, 'third']);
                list($single, $plural) = $this->parseExpression($matches[1][1], [Str::class, 'plural']);
                $raw = $matches[0][1];
            }
        }
        $raw = implode('/', array_merge($dirs, ['%s' . $raw]));

        return compact('verb', 'third', 'single', 'plural', 'dirs', 'raw');
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

    /**
     * Third-person verb form
     *
     * @param $verb
     * @return string
     */
    private function third($verb): string
    {
        if (Str::endsWith($verb, 'y')) {
            if ($this->isVowel(Str::substr($verb,-2, 1))) {
                return $verb . 's';
            } else {
                return Str::replaceMatches('/y$/', 'ies', $verb);
            }
        } elseif (Str::endsWith($verb, ['o', 'ch', 's', 'sh', 'x', 'z'])) {
            return $verb . 'es';
        } else {
            return $verb . 's';
        }
    }

    private function isVowel($letter): bool
    {
        return in_array(strtolower($letter), ['a','e','i','o','u']);
    }

    abstract public function getArgs($key = null);
}