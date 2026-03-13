---
name: laravel-actions-generator
description: Guides agents in using this Laravel action generator package to create single Actions or full CRUD stacks with DTO data classes, interfaces (contracts), and Pest tests inside a Laravel application. Use when working in this repository or when the user asks to generate or modify Actions/CRUD/DTO/tests using the make:action commands.
---

# Laravel Actions Generator

Этот skill объясняет, как использовать пакет `maslennikov-yv/action-generator` внутри Laravel‑приложения для генерации:
- отдельных **Action**‑классов,
- полного **CRUD**‑набора (несколько Actions для одной модели),
- связанных **DTO (Data) классов**,
- **интерфейсов (contracts)**,
- и **Pest‑тестов с dataset’ами**.

Все примеры ниже предполагают рабочий Laravel‑проект, в который установлен этот пакет.

## When to use this skill

Use this skill when:
- you work in a Laravel app that has `maslennikov-yv/action-generator` installed;
- the user asks to create or modify **Actions** (business logic layer);
- the user wants a **CRUD stack** of Actions for a model (Create/Index/Show/Update/Destroy);
- the user mentions **DTO/Data classes**, **Contracts/Interfaces** for Actions, or **Pest tests** for Actions;
- you need to understand where generated files are placed (Actions, Data, Contracts, Tests) and how to extend them with business logic.

If the task is about typical Laravel controllers/models only (without Actions/DTO/contracts/tests from this package), this skill is likely not needed.

## Artisan publish (prepare Laravel app)

Перед активным использованием генератора в конкретном Laravel‑приложении рекомендуется:

- Опубликовать конфиг:

```bash
php artisan vendor:publish --tag=action-config
```

- Опубликовать stubs (если нужно переопределить шаблоны):

```bash
php artisan action:stub:publish
```

Агент, настраивающий проект, должен убедиться, что эти команды выполнены хотя бы один раз на окружении разработки, прежде чем полагаться на структуру, описанную в этом skill.

## Concepts

Кратко, в терминах этого пакета:

- **Action**
  - Лёгкий слой бизнес‑логики.
  - Генерируется командой `php artisan make:action VerbModel`.
  - Располагается в `app/Actions/...` с неймспейсом `App\Actions\...`.
  - При необходимости типизируется `Data`‑классом и контрактом.

- **CRUD stack**
  - Набор Actions для одной модели: `Create`, `Index`, `Show`, `Update`, `Destroy`.
  - Может генерироваться одной командой с `*` или комбинациями глаголов (см. раздел Workflows).

- **Data (DTO) class**
  - Класс‑DTO на основе `Spatie\LaravelData\Data`.
  - Генерируется к командой `make:action:data` (обычно вызывается автоматически из `make:action`).
  - Располагается в `app/Data/...` с неймспейсом `App\Data\...`.
  - Имя по умолчанию: `VerbSingleData` (например, `CreateArticleData`).

- **Interface (Contract)**
  - Контракт для Action, реализуемый самим Action‑классом.
  - Генерируется командой `make:action:interface` (автоматически вызывается из `make:action`).
  - Располагается в `app/Contracts/Actions/...` с неймспейсом `App\Contracts\Actions\...`.
  - Имя по умолчанию: `ThirdPersonSingle` (например, `CreatesArticle`).
  - В конфиге `config/action.php` задаются соответствия глаголов и third‑form:
    - `verbs`: `'create' => 'Create', ...`
    - `interfaces`: `'create' => 'Creates', ...`

- **Dataset + Pest test**
  - Dataset‑файл с тестовыми наборами и Pest‑тест для Action.
  - Dataset: `tests/Feature/Actions/.../Datasets.php`.
  - Test: `tests/Feature/Actions/.../VerbSingleTest.php` (например, `CreateArticleTest`).
  - Генерируются командами `make:action:dataset` и `make:action:test`, которые автоматически вызываются из `make:action` при опции `--test`.

## Workflows

### 1. Generate a single Action

Russian summary: одиночный Action для одной операции над моделью.

**Command (from README):**

```bash
php artisan make:action VerbModel --test --force
```

- `VerbModel` — PascalCase, начинается с глагола (`GetArticle`, `UpdateUser`, `DestroyOrder` и т.п.).
- Опция `--test` (optional):
  - если указана — будут сгенерированы Data, Interface, Dataset и Test;
  - если не указана — будут сгенерированы только Action + Data + Interface.
- Опция `--force` (optional) перезаписывает существующие файлы.

**Resulting files (пример: `GetArticle`):**

- `app/Actions/Articles/GetArticle.php`
  - реализует интерфейс, принимает `GetArticleData` в `__invoke`.
- `app/Data/Articles/GetArticleData.php`
- `app/Contracts/Actions/Articles/GetsArticle.php`
- (if `--test`):
  - `tests/Feature/Actions/Articles/Datasets.php`
  - `tests/Feature/Actions/Articles/GetArticleTest.php`

Фактические пути и имена вычисляются через трейты `HasAction` и команды `ActionMakeCommand`, `ActionDataMakeCommand`, `ActionDatasetMakeCommand`, `ActionTestMakeCommand`, но для агентской работы достаточно придерживаться этой схемы.

### 2. Generate a CRUD stack of Actions

Russian summary: полный CRUD‑набор для модели с Actions/DTO/contracts/tests.

Из README:

```bash
php artisan make:action {*}Model --test --force
```

или, с более тонким контролем глаголов (см. `preProcessVerb` в `ActionMakeCommand`):

- `*` — все глаголы: `destroy`, `update`, `show`, `create`, `index`.
- `cuis` / `c+u+i+s` / и т.п. — только указанные глаголы (`+` для включения).
- `*-d` — все, кроме `destroy` (`-` для исключения).

**Example:**

```bash
php artisan make:action {*}Article --test
```

Создаст набор Actions:
- `CreateArticle`, `IndexArticle`, `ShowArticle`, `UpdateArticle`, `DestroyArticle`
с их Data, Interfaces, Datasets и Tests по тем же правилам, что и для одиночного Action.

После генерации команда выведет подсказки вида:

```text
Don't forget to link the interface to the implementation:
App\Contracts\Actions\Articles\CreatesArticle::class => App\Actions\Articles\CreateArticle::class,
...
```

Агент при доработках кода должен убедиться, что эти подсказки учтены (обычно в сервис‑провайдере/контейнере).

## Implementation guidelines

Russian summary: как агентам правильно дорабатывать сгенерированные файлы.

- **Actions (`app/Actions/...`)**
  - Внутрь метода `__invoke({VerbSingle}Data $data)` добавлять бизнес‑логику (работу с моделями, валидацию доменных инвариантов, события и т.п.).
  - Соблюдать сигнатуру, не менять типы параметров/возврата без необходимости.
  - При изменении зависимостей обновлять и тесты, и, при необходимости, DTO.

- **Data (DTO) classes (`app/Data/...`)**
  - Расширяют `Spatie\LaravelData\Data`.
  - В конструктор добавлять необходимые свойства:
    - идентификаторы (`int $articleId`),
    - значения полей (`string $title`, и т.п.).
  - DTO служит контрактом данных между внешним слоем (например, контроллером) и Action.

- **Contracts/Interfaces (`app/Contracts/Actions/...`)**
  - Не вкладывать бизнес‑логику внутрь интерфейса; он описывает только сигнатуру.
  - Следить, чтобы Action‑класс реализовывал этот интерфейс.
  - При изменении сигнатуры Action в первую очередь менять её в контракте и синхронизировать реализацию.

- **Tests (`tests/Feature/Actions/...`)**
  - Использовать сгенерированный Pest‑тест как основу.
  - Проверять как минимум:
    - успешный сценарий (dataset `can verb a single` и т.п.),
    - граничные случаи (отсутствие модели, некорректные входные данные, права доступа, и т.д.).
  - При добавлении нового поведения в Action расширять Tests и Dataset соответствующими кейсами.

- **Config (`config/action.php`)**
  - При необходимости менять человекочитаемые алиасы глаголов:
    - `verbs` — что пойдёт в названия классов Actions (`Create`, `Destroy` и т.п.).
    - `interfaces` — что пойдёт в контракты (`Creates`, `Destroys` и т.п.).
  - Агент не должен плодить произвольный набор глаголов без согласования; лучше использовать уже заданные в конфиге.

## Testing

Russian summary: как запускать тесты для Actions.

- Генератор предполагает использование **Pest**:
  - README: `If you plan to use the --test option, you must install pest: https://pestphp.com/docs/installation`.
- В типичном Laravel‑проекте тесты запускаются командами:

```bash
php artisan test
```

или, если настроен `vendor/bin/pest`:

```bash
./vendor/bin/pest
```

Для целей этого skill:
- Предпочтительно использовать ту команду, которая уже отражена в `README` конкретного приложения или в CI‑конфиге.
- Если информация отсутствует, безопасно предлагать `php artisan test`.

## Examples

### Example 1: Single Action for Article

Goal: создать Action `GetArticle` с DTO, контрактом и тестами.

Steps:
1. Generate:
   ```bash
   php artisan make:action GetArticle --test
   ```
2. Locate generated files:
   - `app/Actions/Articles/GetArticle.php`
   - `app/Data/Articles/GetArticleData.php`
   - `app/Contracts/Actions/Articles/GetsArticle.php`
   - `tests/Feature/Actions/Articles/Datasets.php`
   - `tests/Feature/Actions/Articles/GetArticleTest.php`
3. Implement business logic inside `GetArticle::__invoke(GetArticleData $data)`:
   - загрузить `Article` по ID,
   - вернуть результат или бросить исключение.
4. Adjust DTO and tests:
   - добавить нужные поля в `GetArticleData`,
   - обновить dataset и тесты под реальные сценарии.
5. Run tests with `php artisan test` (or project‑specific command).

### Example 2: Full CRUD for Article

Goal: создать CRUD‑набор Actions для статьи.

Steps:
1. Generate all CRUD verbs:
   ```bash
   php artisan make:action {*}Article --test
   ```
2. Expect Actions:
   - `CreateArticle`, `IndexArticle`, `ShowArticle`, `UpdateArticle`, `DestroyArticle`
   - с соответствующими Data, Contracts, Datasets, Tests.
3. For each Action:
   - заполнить бизнес‑логику в `__invoke`,
   - актуализировать DTO поля,
   - расширить тесты и datasets.
4. Выполнить подсказку из консоли:
   - добавить связи интерфейс → реализация в контейнере (например, в провайдере).
5. Запустить все тесты и убедиться, что CRUD‑поведение корректно.

## Additional resources

Для более подробных сведений о командах и структуре (если потребуется расширять skill):
- README текущего пакета: `README.md` в корне репозитория.
- Исходники команд:
  - `src/Commands/ActionMakeCommand.php`
  - `src/Commands/ActionDataMakeCommand.php`
  - `src/Commands/ActionDatasetMakeCommand.php`
  - `src/Commands/ActionTestMakeCommand.php`
- Трейт с разбором имени и построением неймспейсов:
  - `src/Traits/HasAction.php`

Агент может при необходимости прочитать эти файлы для уточнения деталей, но в типичном сценарии достаточно информации из этого `SKILL.md`.

## Checklist

Use this checklist when generating or editing Actions/CRUD with this package:

- [ ] Package `maslennikov-yv/action-generator` installed and configured (config & stubs published if needed).
- [ ] Chosen correct `make:action` command (single Action or CRUD with `*`/combinations).
- [ ] Generated Action, Data, Contract files are located and opened.
- [ ] Business logic implemented in Action `__invoke` methods.
- [ ] DTO/Data classes updated with required fields.
- [ ] Contracts/interfaces kept in sync with Action signatures.
- [ ] Dataset and Pest tests created/updated for positive and edge cases.
- [ ] Interface → implementation bindings added (from console tips).
- [ ] Test suite executed and passing.

