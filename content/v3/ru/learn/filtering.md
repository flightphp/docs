# Фильтрация

Flight позволяет вам фильтровать методы до и после их вызова. Нет
заранее определенных хуков, которые нужно запоминать. Вы можете фильтровать любые из методов фреймворка по умолчанию
а также любые пользовательские методы, которые вы объявили.

Функция фильтра выглядит следующим образом:

```php
function (array &$params, string &$output): bool {
  // Код фильтрации
}
```

Используя передаваемые переменные, вы можете изменять входные параметры и/или вывод.

Вы можете запустить фильтр перед вызовом метода следующим образом:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Сделать что-то
});
```

Вы можете запустить фильтр после вызова метода следующим образом:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Сделать что-то
});
```

Вы можете добавлять столько фильтров, сколько захотите, к любому методу. Они будут вызваны
в порядке объявления.

Вот пример процесса фильтрации:

```php
// Объявляем пользовательский метод
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// Добавляем фильтр до
Flight::before('hello', function (array &$params, string &$output): bool {
  // Изменяем параметр
  $params[0] = 'Fred';
  return true;
});

// Добавляем фильтр после
Flight::after('hello', function (array &$params, string &$output): bool {
  // Изменяем вывод
  $output .= " Have a nice day!";
  return true;
});

// Вызываем пользовательский метод
echo Flight::hello('Bob');
```

Это должно вывести:

```
Hello Fred! Have a nice day!
```

Если у вас определено несколько фильтров, вы можете прервать цепочку, вернув `false`
в любой из ваших фильтров:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // Это прервет цепочку
  return false;
});

// Этот фильтр не будет вызван
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

Обратите внимание, что основные методы, такие как `map` и `register`, не могут быть фильтрованы, потому что
они вызываются непосредственно и не динамически вызываются.