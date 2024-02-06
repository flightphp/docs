# Фильтрация

Flight позволяет фильтровать методы до и после их вызова. Здесь нет
заранее определенных хуков, которые нужно запоминать. Вы можете фильтровать любые методы
фреймворка, а также любые пользовательские методы, которые вы отобразили.

Функция фильтра выглядит следующим образом:

```php
function (array &$params, string &$output): bool {
  // Код фильтра
}
```

Используя переданные переменные, вы можете изменять входные параметры и/или выходные данные.

Вы можете запустить фильтр перед методом, выполнив:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Сделать что-то
});
```

Вы можете запустить фильтр после метода, выполнив:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Сделать что-то
});
```

Вы можете добавить столько фильтров, сколько захотите к любому методу. Они будут вызваны
в порядке объявления.

Вот пример процесса фильтрации:

```php
// Отобразить пользовательский метод
Flight::map('hello', function (string $name) {
  return "Привет, $name!";
});

// Добавить дофильтр
Flight::before('hello', function (array &$params, string &$output): bool {
  // Изменить параметр
  $params[0] = 'Фред';
  return true;
});

// Добавить послефильтр
Flight::after('hello', function (array &$params, string &$output): bool {
  // Изменить вывод
  $output .= " Хорошего дня!";
  return true;
});

// Вызвать пользовательский метод
echo Flight::hello('Боб');
```

Это должно вывести:

```
Привет, Фред! Хорошего дня!
```

Если вы определили несколько фильтров, вы можете прервать цепочку, вернув `false`
в любой из ваших функций фильтра:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'один';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'два';

  // Это завершит цепочку
  return false;
});

// Это не будет вызвано
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'три';
  return true;
});
```

Обратите внимание, что базовые методы, такие как `map` и `register`, нельзя фильтровать, потому что
они вызываются напрямую и не вызываются динамически.