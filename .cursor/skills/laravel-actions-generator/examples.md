# Laravel Actions Generator – Examples

Абстрактные сценарии использования пакета. Подставьте имя своей модели/сущности и scope вместо `Entity`, `Scope` и т.п.

## Example A: CRUD в отдельном scope (например, админка)

Goal: создать CRUD‑набор Actions для сущности в отдельном неймспейсе (например, `Scope/Entity`).

### Step 1: Generate CRUD

```bash
php artisan make:action {*}Scope/Entity --test
```

Ожидаемые файлы:
- `app/Actions/Scope/Entities/CreateEntity.php`
- `app/Actions/Scope/Entities/IndexEntity.php`
- `app/Actions/Scope/Entities/ShowEntity.php`
- `app/Actions/Scope/Entities/UpdateEntity.php`
- `app/Actions/Scope/Entities/DestroyEntity.php`
- `app/Data/Scope/Entities/{Verb}EntityData.php`
- `app/Contracts/Actions/Scope/Entities/{Third}{Entity}.php`
- `tests/Feature/Actions/Scope/Entities/Datasets.php`
- `tests/Feature/Actions/Scope/Entities/{Verb}EntityTest.php`

### Step 2: Implement business logic

Для каждого Action:
- заполнить метод `__invoke({Verb}EntityData $data)` (один параметр — DTO);
- при нескольких операциях с БД оборачивать логику в `DB::transaction(function () use ($data) { ... })`;
- при необходимости вызывать другие Actions — инжектить их интерфейсы в конструктор или использовать `app(Interface::class)`, не дублировать код;
- учесть права доступа, валидацию, события.

### Step 3: Wire interfaces to implementations

В сервис‑провайдере (или через атрибуты — один стиль по всему приложению):
- связать контракты с реализациями по подсказке из консоли (например, `App\Contracts\Actions\Scope\Entities\CreatesEntity::class => App\Actions\Scope\Entities\CreateEntity::class` и т.п.);
- в контроллерах и других Actions инжектить интерфейсы (например, `CreatesEntity`), а не классы реализаций.

### Step 4: Adjust datasets & tests

- В `Datasets.php` определить наборы данных для успешных и неуспешных сценариев.
- В тестах использовать эти datasets для проверки CRUD‑поведения.

## Example B: Одиночный Action с кастомным глаголом

Goal: создать Action с нестандартным глаголом (вне набора CRUD), например `PublishEntity` или `ArchiveEntity`.

### Step 1: Generate

```bash
php artisan make:action PublishEntity --test
```

### Step 2: Adjust config if needed

Если нужен согласованный интерфейс (например, `PublishesEntity`):
- при необходимости добавить запись в `config/action.php` в `verbs`/`interfaces`;
- либо использовать синтаксис с фигурными скобками и явным указанием форм.

### Step 3: Implement and test

- Реализовать `PublishEntity::__invoke(PublishEntityData $data)` (один параметр — DTO); при нескольких операциях с БД — `DB::transaction(...)`.
- при вызове других Actions — инжектить их интерфейсы в конструктор;
- обновить Data, Dataset и Test под нужную бизнес‑логику.

## Example C: Action вызывает другой Action и транзакция

- В конструктор Action инжектить **интерфейс** другого Action (например, `CreatesEntity $createEntity`), в `__invoke` вызывать `($this->createEntity)(CreateEntityData::from([...]))`.
- Если в одном Action выполняется несколько операций с БД (создание + обновление, несколько моделей) — оборачивать тело в `return DB::transaction(function () use ($data) { ... });`.

