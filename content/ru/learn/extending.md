# Расширение

Flight разработан для того, чтобы быть расширяемым фреймворком. Фреймворк поставляется вместе с набором стандартных методов и компонентов, но позволяет вам отображать ваши собственные методы, регистрировать ваши собственные классы или даже переопределять существующие классы и методы.

Если вам нужен DIC (контейнер внедрения зависимостей), перейдите на страницу [Контейнер внедрения зависимостей](dependency-injection-container).

## Отображение Методов

Для отображения вашего собственного простого пользовательского метода используйте функцию `map`:

```php
// Отображение вашего метода
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Вызов вашего пользовательского метода
Flight::hello('Bob');
```

Это используется больше тогда, когда вам нужно передавать переменные в ваш метод, чтобы получить ожидаемое значение. Использование метода `register()` ниже предназначено больше для передачи конфигурации, а затем вызова вашего предварительно настроенного класса.

## Регистрация Классов

Для регистрации вашего собственного класса и его конфигурации используйте функцию `register`:

```php
// Регистрация вашего класса
Flight::register('user', User::class);

// Получение экземпляра вашего класса
$user = Flight::user();
```

Метод регистрации также позволяет вам передавать параметры в конструктор вашего класса. Таким образом, при загрузке вашего пользовательского класса он будет инициализирован заранее. Вы можете определить параметры конструктора, передав дополнительный массив. Вот пример загрузки подключения к базе данных:

```php
// Регистрация класса с параметрами конструктора
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Получение экземпляра вашего класса
// Это создаст объект с определенными параметрами
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// и если позже вам это понадобится в вашем коде, просто снова вызовите тот же метод
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Если вы передаете дополнительный параметр обратного вызова, он будет выполнен сразу после создания класса. Это позволяет вам выполнить любые процедуры установки для вашего нового объекта. Функция обратного вызова принимает один параметр, экземпляр нового объекта.

```php
// Обратный вызов получит созданный объект
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

По умолчанию каждый раз, когда вы загружаете ваш класс, вы получите общий экземпляр. Чтобы получить новый экземпляр класса, просто передайте `false` как параметр:

```php
// Общий экземпляр класса
$shared = Flight::db();

// Новый экземпляр класса
$new = Flight::db(false);
```

Имейте в виду, что отображенные методы имеют приоритет над зарегистрированными классами. Если вы объявите оба с одним и тем же именем, будет вызван только отображенный метод.

## Переопределение Методов Фреймворка

Flight позволяет вам переопределять его стандартную функциональность, чтобы адаптировать ее под свои собственные нужды, не модифицируя при этом код.

Например, когда Flight не может соответствовать URL маршруту, он вызывает метод `notFound`, который отправляет общий ответ с кодом `HTTP 404`. Вы можете переопределить это поведение, используя метод `map`:

```php
Flight::map('notFound', function() {
  // Показать пользовательскую страницу 404
  include 'errors/404.html';
});
```

Flight также позволяет вам заменить основные компоненты фреймворка. Например, вы можете заменить класс маршрутизатора по умолчанию своим собственным пользовательским классом:

```php
// Регистрация вашего пользовательского класса
Flight::register('router', MyRouter::class);

// Когда Flight загружает экземпляр Router, он загружает ваш класс
$myrouter = Flight::router();
```

Однако методы фреймворка, такие как `map` и `register`, не могут быть переопределены. Вы получите ошибку, если попытаетесь это сделать.