<?php

declare(strict_types=1);

namespace Maslennikov\LaravelActions\Traits;

use Illuminate\Support\Str;

trait HasAction
{
    protected function getAction(): string
    {
        return $this->getVerb() . $this->getModel();
    }

    protected function getInterface(): string
    {
        return $this->getThird() . $this->getModel();
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
        return $this->getVerb() . $this->getModel() . 'Data';
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

    protected function getModel(): string
    {
        return trim($this->getArgs('single'));
    }

    protected function getModelNamespace(): string
    {
        return app()->getNamespace() .
            $this->makeNamespace([
                'Models',
                ...$this->getDirs(),
                $this->getModel(),
            ]);
    }

    protected function replaceModel(&$stub): static
    {
        $stub = str_replace('{{model}}', $this->getModel(), $stub);

        return $this;
    }

    protected function getModelKeyName(): string
    {
        return Str::snake($this->getModel()) . '_id';
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

    abstract public function getArgs($key = null);
}