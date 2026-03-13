# Laravel Actions Generator – Examples

Несколько более подробных сценариев использования пакета.

## Example A: Admin CRUD for Article

Goal: создать CRUD‑набор Actions для статей в админ‑зоне.

### Step 1: Generate CRUD

```bash
php artisan make:action {*}Admin/Article --test
```

Ожидаемые файлы:
- `app/Actions/Admin/Articles/CreateArticle.php`
- `app/Actions/Admin/Articles/IndexArticle.php`
- `app/Actions/Admin/Articles/ShowArticle.php`
- `app/Actions/Admin/Articles/UpdateArticle.php`
- `app/Actions/Admin/Articles/DestroyArticle.php`
- `app/Data/Admin/Articles/{Verb}ArticleData.php`
- `app/Contracts/Actions/Admin/Articles/{Third}{Article}.php`
- `tests/Feature/Actions/Admin/Articles/Datasets.php`
- `tests/Feature/Actions/Admin/Articles/{Verb}ArticleTest.php`

### Step 2: Implement business logic

Для каждого Action:
- заполнить метод `__invoke({Verb}ArticleData $data)`;
- использовать модель `App\Models\Admin\Article` (или нужную модель) через DI/репозитории;
- учесть права доступа, валидацию, события.

### Step 3: Wire interfaces to implementations

В вашем сервис‑провайдере:
- связать контракты с реализациями, используя подсказку из консоли (`App\Contracts\Actions\Admin\Articles\CreatesArticle::class => App\Actions\Admin\Articles\CreateArticle::class,` и т.п.).

### Step 4: Adjust datasets & tests

- В `Datasets.php` определить наборы данных для успешных и неуспешных сценариев.
- В тестах использовать эти datasets для проверки CRUD‑поведения.

## Example B: Single Action with custom verbs

Goal: создать нестандартный Action (не полностью CRUD) — например, `PublishArticle`.

### Step 1: Generate

```bash
php artisan make:action PublishArticle --test
```

### Step 2: Adjust config if needed

Если требуется согласованный интерфейс (например, `PublishesArticle`), можно:
- добавить запись в `config/action.php`:
  - в `verbs`/`interfaces` – при необходимости;
- либо использовать синтаксис с фигурными скобками и явным указанием форм.

### Step 3: Implement and test

- Реализовать `PublishArticle::__invoke(PublishArticleData $data)`;
- обновить Data, Dataset и Test под нужную бизнес‑логику публикации.

