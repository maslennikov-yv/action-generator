# Laravel action generator

Actions - lightweight business logic layer

## Installation

You can install the package via composer:

```bash
composer require maslennikov-yv/action-generator --dev
```

## Publish

You can publish action config:

```bash
php artisan vendor:publish --tag=action-config 
```

Also, you can publish action stubs:

```bash
php artisan action:stub:publish 
```

You can also publish the Cursor skill that helps agents work with this package (generate single Actions or full CRUD stacks with DTOs, contracts and tests):

```bash
php artisan vendor:publish --tag=action-skill
```

This will copy the skill into your application at:

- `.cursor/skills/laravel-actions-generator/`

After publishing, Cursor agents working in your Laravel app will automatically use this skill when you ask them to create or modify Actions/CRUD/DTO/tests with this package.

## Usage

When you need to create an action, just run the following command:
``` php
php artisan make:action VerbModel --test --force
```

or, to make CreateModel, IndexModel, ShowModel, UpdateModel, DestroyModel actions:
``` php
php artisan make:action {*}Model --test --force
```

For example:

``` php
php artisan make:action GetArticle --test
```

or:
``` php
php artisan make:action {*}Article --test
```

*If you plan to use the --test option, you must install pest:* https://pestphp.com/docs/installation