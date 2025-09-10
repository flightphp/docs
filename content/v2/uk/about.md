# Що таке Flight?

Flight - це швидкий, простий, розширюваний фреймворк для PHP.
Flight дозволяє вам швидко та легко створювати RESTful веб-додатки.

``` php
require 'flight/Flight.php'; // підключення фреймворку Flight

Flight::route('/', function(){ // визначення маршруту
  echo 'hello world!'; // виведення привітання
});

Flight::start(); // запуск додатку
```

[Дізнатися більше](learn)

# Вимоги

Flight вимагає PHP 7.4 або новішої версії.

# Ліцензія

Flight випущений під ліцензією [MIT](https://github.com/mikecao/flight/blob/master/LICENSE).

# Спільнота

Ми в Matrix! Спілкуйтеся з нами на [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Співпраця

Цей веб-сайт розміщений на [Github](https://github.com/mikecao/flightphp.com).
Оновлення та переклади мов вітаються.
