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