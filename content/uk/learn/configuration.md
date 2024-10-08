# Налаштування

Ви можете налаштувати певні поведінки Flight, встановлюючи значення конфігурації
через метод `set`.

```php
Flight::set('flight.log_errors', true);
```

## Доступні налаштування конфігурації

Нижче наведено список усіх доступних налаштувань конфігурації:

- **flight.base_url** `?string` - Переозначте базову URL запиту. (за замовчуванням: null)
- **flight.case_sensitive** `bool` - Чутливе до регістру співвідношення для URL. (за замовчуванням: false)
- **flight.handle_errors** `bool` - Дозволити Flight обробляти всі помилки внутрішньо. (за замовчуванням: true)
- **flight.log_errors** `bool` - Записувати помилки у файл журналу помилок веб-сервера. (за замовчуванням: false)
- **flight.views.path** `string` - Директорія, що містить файли шаблонів для перегляду. (за замовчуванням: ./views)
- **flight.views.extension** `string` - Розширення файлу шаблону для перегляду. (за замовчуванням: .php)
- **flight.content_length** `bool` - Встановити заголовок `Content-Length`. (за замовчуванням: true)
- **flight.v2.output_buffering** `bool` - Використовувати застаріле буферизацію виводу. Див. [міграцію на v3](migrating-to-v3). (за замовчуванням: false)

## Налаштування завантажувача

Також є ще одне налаштування конфігурації для завантажувача. Це дозволить вам 
автозавантажувати класи з `_` в імені класу.

```php
// Увімкнення завантаження класів з підкресленнями
// За замовчуванням: true
Loader::$v2ClassLoading = false;
```

## Змінні

Flight дозволяє зберігати змінні, щоб їх можна було використовувати в будь-якому місці вашого додатка.

```php
// Зберігайте вашу змінну
Flight::set('id', 123);

// В іншому місці вашого додатка
$id = Flight::get('id');
```
Щоб перевірити, чи була встановлена змінна, ви можете зробити:

```php
if (Flight::has('id')) {
  // Виконати дію
}
```

Ви можете очистити змінну, зробивши:

```php
// Очищає змінну id
Flight::clear('id');

// Очищає всі змінні
Flight::clear();
```

Flight також використовує змінні для цілей конфігурації.

```php
Flight::set('flight.log_errors', true);
```

## Обробка помилок

### Помилки та виключення

Всі помилки та виключення перехоплюються Flight і передаються в метод `error`.
За замовчуванням поведінка полягає в тому, щоб надіслати загальний `HTTP 500 Internal Server Error`
відповідь з деякою інформацією про помилку.

Ви можете переозначити цю поведінку для своїх потреб:

```php
Flight::map('error', function (Throwable $error) {
  // Обробка помилки
  echo $error->getTraceAsString();
});
```

За замовчуванням помилки не записуються в веб-сервер. Ви можете активувати це, змінивши конфігурацію:

```php
Flight::set('flight.log_errors', true);
```

### Не знайдено

Коли URL не можна знайти, Flight викликає метод `notFound`. За замовчуванням
поведінка полягає в тому, щоб надіслати відповідь `HTTP 404 Not Found` з простим повідомленням.

Ви можете переозначити цю поведінку для своїх потреб:

```php
Flight::map('notFound', function () {
  // Обробка не знайдено
});
```