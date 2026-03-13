# Laravel Actions Generator – Reference

Краткая справка по ключевым командам и структуре, основанная на исходниках пакета.

## Commands overview

- `php artisan make:action {name} {--force} {--test}`
  - Основная команда для генерации Action.
  - Использует `Maslennikov\LaravelActions\Commands\ActionMakeCommand`.
  - При `--test` дополнительно создаёт DataSet и Test.

- `php artisan make:action:data {name} {--force}`
  - Генерирует Data (DTO) для Action.
  - Использует `ActionDataMakeCommand`.

- `php artisan make:action:interface {name} {--force}`
  - Генерирует контракт (interface) для Action.
  - Использует `ActionInterfaceMakeCommand`.

- `php artisan make:action:dataset {name} {--force}`
  - Генерирует или обновляет dataset для Pest‑тестов.
  - Использует `ActionDatasetMakeCommand`.

- `php artisan make:action:test {name} {--force}`
  - Генерирует Pest‑тест для Action.
  - Использует `ActionTestMakeCommand`.

- `php artisan action:stub:publish`
  - Публикует кастомные stubs в `stubs/`.

- `php artisan vendor:publish --tag=action-config`
  - Публикует конфиг `config/action.php`.

## Name syntax for make:action

См. `HasAction::parseName`:

- Базовый формат:
  - `VerbModel`, например: `GetArticle`, `CreateUser`.
- Поддержка директорий:
  - `Admin/GetArticle` → директории попадают в `getDirs()`.
- Поддержка выражений в фигурных скобках:
  - `{verb:third}{single:plural}` и вариации с одной/двумя скобками:
    - `Get{Article:Articles}`,
    - `{get:gets}{Article:Articles}` и т.п.

Сгенерированные поля:
- `verb`, `third`, `single`, `plural`, `dirs`, `raw`.

## File locations

- **Actions**
  - Path: `app/Actions/{Dirs}/{Plural}/{VerbSingle}.php`
  - Namespace: `App\Actions\{Dirs}\{Plural}\{VerbSingle}`

- **Data**
  - Path: `app/Data/{Dirs}/{Plural}/{VerbSingle}Data.php`
  - Namespace: `App\Data\{Dirs}\{Plural}\{VerbSingle}Data`

- **Interfaces**
  - Path: `app/Contracts/Actions/{Dirs}/{Plural}/{ThirdSingle}.php`
  - Namespace: `App\Contracts\Actions\{Dirs}\{Plural}\{ThirdSingle}`

- **Datasets**
  - Path: `tests/Feature/Actions/{Dirs}/{Plural}/Datasets.php`

- **Tests**
  - Path: `tests/Feature/Actions/{Dirs}/{Plural}/{VerbSingle}Test.php`

## CRUD verb processing

См. `ActionMakeCommand::preProcessVerb`:

- Базовый набор CRUD‑глаголов:
  - `destroy`, `update`, `show`, `create`, `index`.

- Варианты:
  - `*` → все глаголы.
  - Строка с `+` → фильтр по первой букве:
    - например, `"cui"` + `+`‑логика, и т.п.
  - Строка с `-` → исключение по первой букве.

В типичных сценариях рекомендуется:
- `*` для полного CRUD;
- либо явные одиночные глаголы без `+`/`-`.

## Config mapping

`config/action.php`:

- `verbs`:
  - `'destroy' => 'Destroy'`,
  - `'update' => 'Update'`,
  - `'show' => 'Show'`,
  - `'create' => 'Create'`,
  - `'index' => 'Index'`.

- `interfaces`:
  - `'destroy' => 'Destroys'`,
  - `'update' => 'Updates'`,
  - `'show' => 'Shows'`,
  - `'create' => 'Creates'`,
  - `'index' => 'Indexes'`.

Эти значения влияют на имена Action‑ и Interface‑классов.

