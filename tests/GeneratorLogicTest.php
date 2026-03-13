<?php

declare(strict_types=1);

namespace Maslennikov\LaravelActions\Tests;

use Maslennikov\LaravelActions\Commands\ActionMakeCommand;
use Maslennikov\LaravelActions\Exceptions\ActionParseException;
use Maslennikov\LaravelActions\LaravelActionsServiceProvider;
use Orchestra\Testbench\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class GeneratorLogicTest extends TestCase
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelActionsServiceProvider::class,
        ];
    }

    protected function makeActionCommand(): ActionMakeCommand
    {
        /** @var ActionMakeCommand $command */
        $command = $this->app->make(ActionMakeCommand::class);

        $command->setLaravel($this->app);

        return $command;
    }

    protected function callProtected(object $object, string $method, array $args = []): mixed
    {
        $ref = new ReflectionMethod($object, $method);
        $ref->setAccessible(true);

        return $ref->invokeArgs($object, $args);
    }

    protected function setProtected(object $object, string $property, mixed $value): void
    {
        $ref = new ReflectionProperty($object, $property);
        $ref->setAccessible(true);
        $ref->setValue($object, $value);
    }

    public function test_parse_name_for_simple_action(): void
    {
        $command = $this->makeActionCommand();

        $parsed = $this->callProtected($command, 'parseName', ['GetArticle']);

        $this->assertSame('Get', $parsed['verb']);
        $this->assertSame('Gets', $parsed['third']);
        $this->assertSame('Article', $parsed['single']);
        $this->assertSame('Articles', $parsed['plural']);
        $this->assertSame([], $parsed['dirs']);
    }

    public function test_parse_name_for_crud_star_syntax(): void
    {
        $command = $this->makeActionCommand();

        $parsed = $this->callProtected($command, 'parseName', ['{*}Article']);

        $this->assertSame('*', $parsed['verb']);
        $this->assertSame('Article', $parsed['single']);
        $this->assertSame('Articles', $parsed['plural']);
    }

    public function test_parse_name_for_custom_third_and_plural(): void
    {
        $command = $this->makeActionCommand();

        $parsed = $this->callProtected(
            $command,
            'parseName',
            ['{create:Creates}{Article:Articles}']
        );

        $this->assertSame('create', $parsed['verb']);
        $this->assertSame('Creates', $parsed['third']);
        $this->assertSame('Article', $parsed['single']);
        $this->assertSame('Articles', $parsed['plural']);
    }

    public function test_parse_name_with_subdirectories(): void
    {
        $command = $this->makeActionCommand();

        $parsed = $this->callProtected($command, 'parseName', ['Admin/Blog/GetArticle']);

        $this->assertSame('Get', $parsed['verb']);
        $this->assertSame('Article', $parsed['single']);
        $this->assertSame(['Admin', 'Blog'], $parsed['dirs']);
    }

    public function test_parse_name_throws_on_invalid_colon_usage(): void
    {
        $this->expectException(ActionParseException::class);

        $command = $this->makeActionCommand();

        $this->callProtected($command, 'parseName', ['Get:Article']);
    }

    public function test_pre_process_verb_star_returns_all_crud_verbs(): void
    {
        $command = $this->makeActionCommand();

        $result = $this->callProtected($command, 'preProcessVerb', ['*']);

        $this->assertSame(
            ['destroy', 'update', 'show', 'create', 'index'],
            $result
        );
    }

    public function test_pre_process_verb_with_plus_syntax_filters_selected_verbs(): void
    {
        $command = $this->makeActionCommand();

        $result = $this->callProtected($command, 'preProcessVerb', ['c+u+i+s']);

        $this->assertSame(
            ['update', 'show', 'create', 'index'],
            $result
        );
    }

    public function test_pre_process_verb_with_minus_syntax_excludes_verbs(): void
    {
        $command = $this->makeActionCommand();

        $result = $this->callProtected($command, 'preProcessVerb', ['*-d']);

        $this->assertSame(
            ['update', 'show', 'create', 'index'],
            $result
        );
    }

    public function test_namespaces_and_paths_are_generated_correctly(): void
    {
        $command = $this->makeActionCommand();

        $parsed = $this->callProtected($command, 'parseName', ['GetArticle']);
        $this->setProtected($command, 'args', $parsed);

        $actionNamespace = $this->callProtected($command, 'getActionNamespace');
        $dataNamespace = $this->callProtected($command, 'getDataNamespace');
        $interfaceNamespace = $this->callProtected($command, 'getInterfaceNamespace');

        $this->assertSame('App\\Actions\\Articles\\GetArticle', $actionNamespace);
        $this->assertSame('App\\Data\\Articles\\GetArticleData', $dataNamespace);
        $this->assertSame('App\\Contracts\\Actions\\Articles\\GetsArticle', $interfaceNamespace);

        $ref = new ReflectionClass($command);
        $getPath = $ref->getMethod('getPath');
        $getPath->setAccessible(true);

        $path = $getPath->invoke($command, 'GetArticle');

        $this->assertSame(
            $this->app['path'] . '/Actions/Articles/GetArticle.php',
            $path
        );
    }
}

