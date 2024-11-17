<?php

namespace Maslennikov\LaravelActions\Traits;

trait HasImport
{
    protected function injectImport($namespace, &$current, bool|string $condition = true): static
    {
        if ($condition === true || str_contains($current, $condition)) {
            if (!str_contains($current, $namespace)) {
                $current = preg_replace('#^//use#m', "//use\nuse " . $namespace . ';', $current, 1);
            }
        }

        return $this;
    }
}