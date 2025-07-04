# Система событий в Flight PHP (v3.15.0+)

Flight PHP представляет собой легкую и интуитивную систему событий, которая позволяет регистрировать и вызывать пользовательские события в вашем приложении. С добавлением методов `Flight::onEvent()` и `Flight::triggerEvent()`, вы теперь можете подключаться к ключевым моментам жизненного цикла вашего приложения или определять свои собственные события, чтобы сделать код более модульным и расширяемым. Эти методы являются частью **mappable methods** Flight, что означает, что вы можете переопределять их поведение в соответствии с вашими потребностями.

Это руководство охватывает все, что вам нужно знать, чтобы начать работать с событиями, включая причины их использования, способы применения и практические примеры, которые помогут новичкам понять их мощь.

## Почему использовать события?

События позволяют разделять разные части вашего приложения, чтобы они не зависели друг от друга слишком сильно. Это разделение — часто называемое **decoupling** — делает ваш код проще в обновлении, расширении или отладке. Вместо того чтобы писать все в одном большом блоке, вы можете разбить логику на меньшие, независимые фрагменты, которые реагируют на конкретные действия (события).

Представьте, что вы создаете приложение для блога:
- Когда пользователь публикует комментарий, вы можете захотеть:
  - Сохранить комментарий в базе данных.
  - Отправить email владельцу блога.
  - Зафиксировать действие для безопасности.

Без событий вы бы запихнули все это в одну функцию. С событиями вы можете разделить: одна часть сохраняет комментарий, другая вызывает событие, например `'comment.posted'`, а отдельные слушатели обрабатывают email и логирование. Это делает код чище и позволяет добавлять или удалять функции (например, уведомления) без вмешательства в основную логику.

### Общие применения
- **Logging**: Фиксировать действия, такие как входы или ошибки, без загромождения основного кода.
- **Notifications**: Отправлять email или оповещения, когда что-то происходит.
- **Updates**: Обновлять кэш или уведомлять другие системы о изменениях.

## Регистрация слушателей событий

Чтобы слушать событие, используйте `Flight::onEvent()`. Этот метод позволяет определить, что должно происходить, когда событие наступает.

### Синтаксис
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Название события (например, `'user.login'`).
- `$callback`: Функция, которая запускается при срабатывании события.

### Как это работает
Вы "подписываетесь" на событие, указывая Flight, что делать, когда оно произойдет. Callback может принимать аргументы, передаваемые из вызова события.

Система событий Flight синхронная, что означает, что каждый слушатель выполняется последовательно, один за другим. Когда вы вызываете событие, все зарегистрированные слушатели для этого события выполнятся до конца, прежде чем код продолжит выполнение. Это важно понимать, поскольку это отличается от асинхронных систем событий, где слушатели могут выполняться параллельно или позже.

### Простой пример
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";
});
```
Здесь, когда событие `'user.login'` срабатывает, оно приветствует пользователя по имени.

### Ключевые моменты
- Вы можете добавить несколько слушателей к одному событию — они запустятся в порядке регистрации.
- Callback может быть функцией, анонимной функцией или методом из класса.

## Вызов событий

Чтобы вызвать событие, используйте `Flight::triggerEvent()`. Это заставляет Flight запустить все слушатели, зарегистрированные для этого события, передавая любые данные.

### Синтаксис
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: Название события, которое вы вызываете (должно совпадать с зарегистрированным).
- `...$args`: Необязательные аргументы для передачи слушателям (может быть любое количество).

### Простой пример
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Это вызывает событие `'user.login'` и передает `'alice'` слушателю, который мы определили ранее, что выведет: `Welcome back, alice!`.

### Ключевые моменты
- Если слушатели не зарегистрированы, ничего не произойдет — приложение не сломается.
- Используйте оператор распространения (`...`), чтобы передавать несколько аргументов гибко.

### Регистрация слушателей событий

...

**Остановка дальнейших слушателей**:
Если слушатель возвращает `false`, дополнительные слушатели для этого события не будут выполнены. Это позволяет остановить цепочку событий на основе определенных условий. Помните, что порядок слушателей важен, поскольку первый, вернувший `false`, остановит остальных.

**Пример**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Останавливает последующих слушателей
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // это никогда не отправляется
});
```

## Переопределение методов событий

`Flight::onEvent()` и `Flight::triggerEvent()` доступны для [расширения](/learn/extending), что означает, что вы можете переопределять их работу. Это полезно для продвинутых пользователей, желающих настроить систему событий, например, добавить логирование или изменить способ вызова событий.

### Пример: Настройка `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Логируем каждую регистрацию события
    error_log("New event listener added for: $event");
    // Вызываем поведение по умолчанию (предполагая внутреннюю систему событий)
    Flight::_onEvent($event, $callback);
});
```
Теперь каждый раз, когда вы регистрируете событие, оно логируется перед продолжением.

### Почему переопределять?
- Добавить отладку или мониторинг.
- Ограничить события в определенных средах (например, отключить в тестах).
- Интегрировать с другой библиотекой событий.

## Где размещать ваши события

Как новичок, вы можете задаться вопросом: *где регистрировать все эти события в моем приложении?* Простота Flight означает, что нет строгих правил — вы можете размещать их там, где это логично для вашего проекта. Однако, поддержание порядка помогает поддерживать код по мере роста приложения. Вот некоторые практические варианты и лучшие практики, адаптированные к легковесной природе Flight:

### Вариант 1: В основном файле `index.php`
Для небольших приложений или быстрого прототипа вы можете регистрировать события прямо в файле `index.php` рядом с маршрутами. Это держит все в одном месте, что подходит, когда приоритет — простота.

```php
require 'vendor/autoload.php';

// Регистрация событий
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// Определение маршрутов
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Плюсы**: Просто, без дополнительных файлов, отлично для маленьких проектов.
- **Минусы**: Может стать хаотичным по мере роста приложения с большим количеством событий и маршрутов.

### Вариант 2: В отдельном файле `events.php`
Для чуть большего приложения рассмотрите возможность перемещения регистрации событий в отдельный файл, такой как `app/config/events.php`. Подключите этот файл в `index.php` перед маршрутами. Это имитирует, как часто организуются маршруты в `app/config/routes.php` в проектах Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Плюсы**: Делает `index.php` сосредоточенным на маршрутах, организует события логично, легко находить и редактировать.
- **Минусы**: Добавляет немного структуры, что может показаться излишним для очень маленьких приложений.

### Вариант 3: Рядом с местом вызова
Другой подход — регистрировать события рядом с местом их вызова, например, внутри контроллера или определения маршрута. Это работает хорошо, если событие специфично для одной части приложения.

```php
Flight::route('/signup', function () {
    // Регистрация события здесь
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **Плюсы**: Держит связанный код вместе, хорошо для изолированных функций.
- **Минусы**: Распределяет регистрации событий, что затрудняет обзор всех событий; риск дублирования регистраций, если не быть осторожным.

### Лучшие практики для Flight
- **Начните просто**: Для маленьких приложений размещайте события в `index.php`. Это быстро и соответствует минимализму Flight.
- **Растите умно**: По мере расширения приложения (например, более 5-10 событий), используйте файл `app/config/events.php`. Это естественный шаг, как организация маршрутов, и поддерживает код аккуратным без добавления сложных фреймворков.
- **Избегайте переусложнения**: Не создавайте полный класс или директорию "менеджера событий", если приложение не очень большое — Flight процветает на простоте, так что держите это легким.

### Совет: Группируйте по цели
В `events.php` группируйте связанные события (например, все, связанные с пользователем) с комментариями для ясности:

```php
// app/config/events.php
// События, связанные с пользователем
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// События, связанные со страницами
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Эта структура хорошо масштабируется и остается дружелюбной для новичков.

## Примеры для новичков

Давайте пройдемся по некоторым реальным сценариям, чтобы показать, как работают события и почему они полезны.

### Пример 1: Логирование входа пользователя
```php
// Шаг 1: Регистрация слушателя
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Шаг 2: Вызов в приложении
Flight::route('/login', function () {
    $username = 'bob'; // Предположим, это приходит из формы
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Почему это полезно**: Код входа не нуждается в знании о логировании — он просто вызывает событие. Позже вы можете добавить больше слушателей (например, отправить приветственный email), не изменяя маршрут.

### Пример 2: Уведомление о новых пользователях
```php
// Слушатель для новых регистраций
Flight::onEvent('user.registered', function ($email, $name) {
    // Симулируем отправку email
    echo "Email sent to $email: Welcome, $name!";
});

// Вызов при регистрации
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Почему это полезно**: Логика регистрации сосредоточена на создании пользователя, в то время как событие обрабатывает уведомления. Вы можете добавить больше слушателей (например, логировать регистрацию) позже.

### Пример 3: Очистка кэша
```php
// Слушатель для очистки кэша
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Очистка кэша сессии, если применимо
    echo "Cache cleared for page $pageId.";
});

// Вызов при редактировании страницы
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Предположим, мы обновили страницу
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Почему это полезно**: Код редактирования не заботится о кэшировании — он просто сигнализирует об обновлении. Другие части приложения могут отреагировать по необходимости.

## Лучшие практики

- **Называйте события четко**: Используйте конкретные имена, такие как `'user.login'` или `'page.updated'`, чтобы было очевидно, что они делают.
- **Делайте слушателей простыми**: Не размещайте в них медленные или сложные задачи — держите приложение быстрым.
- **Тестируйте события**: Вызывайте их вручную, чтобы убедиться, что слушатели работают как ожидается.
- **Используйте события разумно**: Они отличны для разделения, но слишком много из них может сделать код сложным для понимания — используйте, когда это имеет смысл.

Система событий в Flight PHP с `Flight::onEvent()` и `Flight::triggerEvent()` предоставляет простой, но мощный способ создания гибких приложений. Пользуясь тем, что разные части вашего приложения общаются через события, вы можете поддерживать код организованным, reusable и легко расширяемым. Независимо от того, логируете ли вы действия, отправляете уведомления или управляете обновлениями, события помогают делать это без запутывания логики. Кроме того, с возможностью переопределять эти методы, у вас есть свобода настроить систему под свои нужды. Начните с одного события и наблюдайте, как это преобразит структуру вашего приложения!

## Встроенные события

Flight PHP поставляется с несколькими встроенными событиями, которые вы можете использовать, чтобы подключиться к жизненному циклу фреймворка. Эти события вызываются в определенные моменты цикла запроса/ответа, позволяя выполнять пользовательскую логику, когда происходят определенные действия.

### Список встроенных событий
- **flight.request.received**: `function(Request $request)` Вызывается, когда запрос получен, разобран и обработан.
- **flight.error**: `function(Throwable $exception)` Вызывается, когда возникает ошибка во время цикла запроса.
- **flight.redirect**: `function(string $url, int $status_code)` Вызывается, когда инициируется перенаправление.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Вызывается, когда проверяется кэш для конкретного ключа и определяется, был ли кэш-хит или промах.
- **flight.middleware.before**: `function(Route $route)` Вызывается после выполнения before middleware.
- **flight.middleware.after**: `function(Route $route)` Вызывается после выполнения after middleware.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Вызывается после выполнения любого middleware.
- **flight.route.matched**: `function(Route $route)` Вызывается, когда маршрут совпадает, но еще не запущен.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Вызывается после выполнения маршрута. `$executionTime` — время, затраченное на выполнение маршрута (вызов контроллера и т.д.).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Вызывается после рендеринга вида. `$executionTime` — время, затраченное на рендеринг шаблона. **Примечание: Если вы переопределяете метод `render`, вам нужно будет повторно вызвать это событие.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Вызывается после отправки ответа клиенту. `$executionTime` — время, затраченное на построение ответа.