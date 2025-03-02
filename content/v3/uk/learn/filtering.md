# Фільтрація

Flight дозволяє вам фільтрувати методи до і після їх виклику. Немає
зазначених хуків, які потрібно запам'ятовувати. Ви можете фільтрувати будь-які з методів за замовчуванням фреймворку, а також будь-які користувацькі методи, які ви налаштували.

Функція фільтра виглядає так:

```php
function (array &$params, string &$output): bool {
  // Код фільтра
}
```

Використовуючи передані змінні, ви можете маніпулювати вхідними параметрами та/або виходом.

Ви можете запустити фільтр перед методом, зробивши:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Зробіть щось
});
```

Ви можете запустити фільтр після методу, зробивши:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Зробіть щось
});
```

Ви можете додати скільки завгодно фільтрів до будь-якого методу. Вони будуть викликані в
порядку, в якому вони оголошені.

Ось приклад процесу фільтрації:

```php
// Співвіднести користувацький метод
Flight::map('hello', function (string $name) {
  return "Привіт, $name!";
});

// Додати фільтр перед
Flight::before('hello', function (array &$params, string &$output): bool {
  // Маніпулюйте параметром
  $params[0] = 'Фред';
  return true;
});

// Додати фільтр після
Flight::after('hello', function (array &$params, string &$output): bool {
  // Маніпулюйте виходом
  $output .= " Гарного вам дня!";
  return true;
});

// Викликати користувацький метод
echo Flight::hello('Боб');
```

Це повинно відображати:

```
Привіт Фред! Гарного вам дня!
```

Якщо ви визначили кілька фільтрів, ви можете розірвати ланцюг, повернувши `false`
в будь-якій з ваших функцій фільтра:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'один';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'два';

  // Це завершить ланцюг
  return false;
});

// Це не буде викликано
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'три';
  return true;
});
```

Зверніть увагу, що основні методи, такі як `map` і `register`, не можна фільтрувати, оскільки їх
викликають безпосередньо, а не динамічно.