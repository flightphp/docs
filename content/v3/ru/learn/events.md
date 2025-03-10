# Система событий в Flight PHP (v3.15.0+)

Flight PHP вводит легкую и интуитивную систему событий, которая позволяет вам регистрировать и запускать пользовательские события в вашем приложении. С добавлением `Flight::onEvent()` и `Flight::triggerEvent()` вы теперь можете подключаться к ключевым моментам жизненного цикла вашего приложения или определять собственные события, чтобы сделать ваш код более модульным и расширяемым. Эти методы являются частью **настраиваемых методов** Flight, что означает, что вы можете переопределить их поведение в соответствии с вашими потребностями.

В этом руководстве приведено все, что вам нужно знать, чтобы начать работу с событиями, включая причины их ценности, как их использовать и практические примеры, которые помогут новичкам понять их мощь.

## Почему использовать события?

События позволяют вам разделить различные части вашего приложения так, чтобы они не зависели слишком сильно друг от друга. Это разделение, часто называемое **разделением**, упрощает обновление, расширение или отладку вашего кода. Вместо того чтобы писать все в одном большом фрагменте, вы можете разделить свою логику на более мелкие, независимые части, которые реагируют на определенные действия (события).

Представьте, что вы создаете приложение для блога:
- Когда пользователь оставляет комментарий, вы можете захотеть:
  - Сохранить комментарий в базе данных.
  - Отправить электронное письмо владельцу блога.
  - Зафиксировать действие для безопасности.

Без событий вы бы напихали все это в одну функцию. С происшествиями вы можете разделить это: одна часть сохраняет комментарий, другая запускает событие типа `'comment.posted'`, а отдельные слушатели обрабатывают электронную почту и ведение журналов. Это делает ваш код более чистым и позволяет добавлять или удалять функции (например, уведомления), не трогая основную логику.

### Обычные применения
- **Ведение журнала**: Записывайте действия, такие как вход в систему или ошибки, без загромождения основного кода.
- **Уведомления**: Отправляйте электронные письма или оповещения, когда что-то происходит.
- **Обновления**: Обновляйте кеши или уведомляйте другие системы о изменениях.

## Регистрация слушателей событий

Чтобы прослушивать событие, используйте `Flight::onEvent()`. Этот метод позволяет вам определить, что должно произойти, когда событие происходит.

### Синтаксис
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Имя вашего события (например, `'user.login'`).
- `$callback`: Функция, которая будет запущена, когда событие будет вызвано.

### Как это работает
Вы "подписываетесь" на событие, сообщая Flight, что делать, когда оно происходит. Коллбек может принимать аргументы, переданные из триггера события.

Система событий Flight является синхронной, что означает, что каждый слушатель события выполняется последовательно, один за другим. Когда вы инициируете событие, все зарегистрированные слушатели для этого события будут выполнены до завершения, прежде чем ваш код продолжит работу. Это важно понимать, так как это отличается от асинхронных систем событий, где слушатели могут работать параллельно или в более позднее время.

### Простой пример
```php
Flight::onEvent('user.login', function ($username) {
    echo "Добро пожаловать обратно, $username!";
});
```
Здесь, когда событие `'user.login'` инициируется, оно поприветствует пользователя по имени.

### Ключевые моменты
- Вы можете добавить несколько слушателей к одному и тому же событию — они будут работать в порядке, в котором вы их зарегистрировали.
- Коллбек может быть функцией, анонимной функцией или методом класса.

## Инициация событий

Чтобы событие произошло, используйте `Flight::triggerEvent()`. Это сообщает Flight выполнить всех слушателей, зарегистрированных для этого события, передавая любые данные, которые вы предоставляете.

### Синтаксис
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: Имя события, которое вы инициируете (должно соответствовать зарегистрированному событию).
- `...$args`: Необязательные аргументы для передачи слушателям (может быть любое количество аргументов).

### Простой пример
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Это инициирует событие `'user.login'` и отправляет `'alice'` слушателю, который мы определили ранее, что приведет к выводу: `Добро пожаловать обратно, alice!`.

### Ключевые моменты
- Если слушатели не зарегистрированы, ничего не произойдет — ваше приложение не сломается.
- Используйте оператор распространения (`...`), чтобы гибко передавать несколько аргументов.

### Регистрация слушателей событий

...

**Остановка дальнейших слушателей**:
Если слушатель возвращает `false`, никакие дополнительные слушатели для этого события выполняться не будут. Это позволяет вам остановить цепочку событий на основе определенных условий. Помните, что порядок слушателей имеет значение, поскольку первый, кто вернет `false`, остановит последующих от работы.

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

`Flight::onEvent()` и `Flight::triggerEvent()` доступны для [расширения](/learn/extending), что означает, что вы можете перес définir, как они работают. Это прекрасно для продвинутых пользователей, которые хотят настроить систему событий, например, добавив ведение журнала или изменив, как события отправляются.

### Пример: Настройка `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Записывать каждую регистрацию события
    error_log("Добавлен новый слушатель события для: $event");
    // Вызвать стандартное поведение (предполагая наличие внутренней системы событий)
    Flight::_onEvent($event, $callback);
});
```
Теперь каждый раз, когда вы регистрируете событие, оно записывается перед продолжением.

### Почему переопределять?
- Добавить отладку или мониторинг.
- Ограничить события в определенных средах (например, отключить в тестировании).
- Интеграция с другой библиотекой событий.

## Куда поместить ваши события

Как новичок, вы можете задаться вопросом: *где мне регистрировать все эти события в моем приложении?* Простота Flight означает, что нет строгого правила — вы можете разместить их там, где это имеет смысл для вашего проекта. Тем не менее, поддержание их организованными помогает вам поддерживать код по мере роста вашего приложения. Вот некоторые практические варианты и лучшие практики, адаптированные к легковесной природе Flight:

### Вариант 1: В вашем основном `index.php`
Для небольших приложений или быстрых прототипов вы можете зарегистрировать события прямо в вашем файле `index.php` наряду с вашими маршрутами. Это держит все в одном месте, что допустимо, когда простота является вашим приоритетом.

```php
require 'vendor/autoload.php';

// Регистрация событий
Flight::onEvent('user.login', function ($username) {
    error_log("$username вошел в систему в " . date('Y-m-d H:i:s'));
});

// Определение маршрутов
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Вошли в систему!";
});

Flight::start();
```
- **Плюсы**: Просто, нет лишних файлов, отлично для маленьких проектов.
- **Минусы**: Может стать беспорядочным, когда ваше приложение расширяется с большим количеством событий и маршрутов.

### Вариант 2: Отдельный файл `events.php`
Для несколько более крупного приложения подумайте о перемещении регистраций событий в специальный файл, такой как `app/config/events.php`. Включите этот файл в ваш `index.php` до ваших маршрутов. Это имитирует, как маршруты часто организованы в `app/config/routes.php` в проектах Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username вошел в систему в " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Электронное письмо отправлено на $email: Добро пожаловать, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Вошли в систему!";
});

Flight::start();
```
- **Плюсы**: Держит `index.php` сосредоточенным на маршрутах, организует события логически, легко находить и редактировать.
- **Минусы**: Добавляет небольшую структурированность, что может показаться излишним для очень маленьких приложений.

### Вариант 3: Ближе к месту их триггера
Другой подход - регистрировать события рядом с тем, где они триггерятся, например, внутри контроллера или определения маршрута. Это хорошо работает, если событие специфично для одной части вашего приложения.

```php
Flight::route('/signup', function () {
    // Регистрация события здесь
    Flight::onEvent('user.registered', function ($email) {
        echo "Уведомление об приветствии отправлено на $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Зарегистрировано!";
});
```
- **Плюсы**: Держит связанный код вместе, хорошо для изолированных функций.
- **Минусы**: Рассекает регистрации событий, что усложняет возможности видеть все события сразу; риски дубликатов регистраций, если не быть осторожным.

### Лучшие практики для Flight
- **Начните с простого**: Для крошечных приложений размещайте события в `index.php`. Это быстро и соответствует минимализму Flight.
- **Развивайтесь разумно**: По мере расширения вашего приложения (например, более 5-10 событий) используйте файл `app/config/events.php`. Это естественный шаг вверх, подобно организации маршрутов, и держит ваш код аккуратным без добавления сложных фреймворков.
- **Избегайте чрезмерной инженерии**: Не создавайте полный «менеджер событий» или директорию, если ваше приложение не стало огромным — Flight процветает на простоте, поэтому держите его легким.

### Советы: группировка по назначению
В `events.php` группируйте связанные события (например, все события, связанные с пользователем, вместе) с комментариями для ясности:

```php
// app/config/events.php
// События пользователя
Flight::onEvent('user.login', function ($username) {
    error_log("$username вошел в систему");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Добро пожаловать на $email!";
});

// События страницы
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Эта структура хорошо масштабируется и остается удобной для новичков.

## Примеры для новичков

Давайте рассмотрим несколько реальных сценариев, чтобы показать, как работают события и почему они полезны.

### Пример 1: Ведение журнала входа пользователя
```php
// Шаг 1: Зарегистрируйте слушателя
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username вошел в систему в $time");
});

// Шаг 2: Запустить это в вашем приложении
Flight::route('/login', function () {
    $username = 'bob'; // Предположим, это из формы
    Flight::triggerEvent('user.login', $username);
    echo "Привет, $username!";
});
```
**Почему это полезно**: Код входа не должен знать о ведении журнала — он просто инициирует событие. Позже вы можете добавить больше слушателей (например, отправить приветственное письмо) без изменения маршрута.

### Пример 2: Уведомление о новых пользователях
```php
// Слушатель для новых регистраций
Flight::onEvent('user.registered', function ($email, $name) {
    // Симулировать отправку письма
    echo "Электронное письмо отправлено на $email: Добро пожаловать, $name!";
});

// Инициировать, когда кто-то подписывается
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Спасибо за регистрацию!";
});
```
**Почему это полезно**: Логика регистрации сосредоточена на создании пользователя, в то время как событие обрабатывает уведомления. Вы можете добавить другие слушатели (например, вести журнал регистрации) позже.

### Пример 3: Очистка кеша
```php
// Слушатель для очистки кеша
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Очистить сессионный кеш, если применимо
    echo "Кеш очищен для страницы $pageId.";
});

// Инициировать, когда страница редактируется
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Предположим, что мы обновили страницу
    Flight::triggerEvent('page.updated', $pageId);
    echo "Страница $pageId обновлена.";
});
```
**Почему это полезно**: Код редактирования не заботится о кешировании — он просто сигнализирует об обновлении. Другие части приложения могут реагировать по мере необходимости.

## Лучшие практики

- **Ясно именуйте события**: Используйте конкретные имена, такие как `'user.login'` или `'page.updated'`, чтобы было очевидно, что они делают.
- **Держите слушателей простыми**: Не помещайте медленные или сложные задачи в слушателей — держите приложение быстрым.
- **Тестируйте свои события**: Инициируйте их вручную, чтобы убедиться, что слушатели работают правильно.
- **Используйте события разумно**: Они отличны для разделения, но слишком много может усложнить ваш код — используйте их, когда это имеет смысл.

Система событий в Flight PHP с `Flight::onEvent()` и `Flight::triggerEvent()` предоставляет вам простой, но мощный способ создания гибких приложений. Позволяя различным частям вашего приложения взаимодействовать друг с другом через события, вы можете держать код организованным, повторно используемым и простым в расширении. Будь то ведение журнала действий, отправка уведомлений или управление обновлениями, события помогают делать это, не запутывая вашу логику. Кроме того, с возможностью переопределения этих методов у вас есть свобода настраивать систему под ваши нужды. Начните с небольшого события и посмотрите, как это преобразует структуру вашего приложения!

## Встроенные события

Flight PHP поставляется с несколькими встроенными событиями, которые вы можете использовать для подключения к жизненному циклу фреймворка. Эти события инициируются в определенные моменты в цикле запроса/ответа, что позволяет вам выполнять пользовательскую логику, когда происходят определенные действия.

### Список встроенных событий
- `flight.request.received`: Инициируется, когда запрос принимается, обрабатывается и разбирается.
- `flight.route.middleware.before`: Инициируется после выполнения промежуточного программного обеспечения до.
- `flight.route.middleware.after`: Инициируется после выполнения промежуточного программного обеспечения после.
- `flight.route.executed`: Инициируется после выполнения и обработки маршрута.
- `flight.response.sent`: Инициируется после отправки ответа клиенту.