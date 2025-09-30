# Фильтрация

## Обзор

Flight позволяет вам фильтровать [отображенные методы](/learn/extending) до и после их вызова.

## Понимание
Нет предопределенных хуков, которые вам нужно запоминать. Вы можете фильтровать любой из стандартных методов фреймворка, а также любые пользовательские методы, которые вы отобразили.

Функция фильтра выглядит так:

```php
/**
 * @param array $params Параметры, переданные в фильтруемый метод.
 * @param string $output (только буферизация вывода v2) Вывод фильтруемого метода.
 * @return bool Верните true/void или не возвращайте ничего, чтобы продолжить цепочку, false, чтобы прервать цепочку.
 */
function (array &$params, string &$output): bool {
  // Код фильтра
}
```

Используя переданные переменные, вы можете манипулировать входными параметрами и/или выводом.

Вы можете запустить фильтр перед методом, сделав следующее:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Сделайте что-то
});
```

Вы можете запустить фильтр после метода, сделав следующее:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Сделайте что-то
});
```

Вы можете добавить столько фильтров, сколько хотите, к любому методу. Они будут вызваны в порядке их объявления.

Вот пример процесса фильтрации:

```php
// Отобразите пользовательский метод
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// Добавьте фильтр before
Flight::before('hello', function (array &$params, string &$output): bool {
  // Манипулируйте параметром
  $params[0] = 'Fred';
  return true;
});

// Добавьте фильтр after
Flight::after('hello', function (array &$params, string &$output): bool {
  // Манипулируйте выводом
  $output .= " Have a nice day!";
  return true;
});

// Вызовите пользовательский метод
echo Flight::hello('Bob');
```

Это должно отобразить:

```
Hello Fred! Have a nice day!
```

Если вы определили несколько фильтров, вы можете прервать цепочку, вернув `false` в любой из ваших функций фильтра:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // Это завершит цепочку
  return false;
});

// Это не будет вызвано
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

> **Примечание:** Основные методы, такие как `map` и `register`, не могут быть отфильтрованы, потому что они вызываются напрямую и не вызываются динамически. См. [Расширение Flight](/learn/extending) для получения дополнительной информации.

## См. также
- [Расширение Flight](/learn/extending)

## Устранение неисправностей
- Убедитесь, что вы возвращаете `false` из ваших функций фильтра, если хотите, чтобы цепочка остановилась. Если вы ничего не возвращаете, цепочка продолжится.

## Журнал изменений
- v2.0 - Первое издание.