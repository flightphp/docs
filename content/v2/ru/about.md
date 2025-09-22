# Что такое Flight?

Flight — это быстрый, простой, расширяемый фреймворк для PHP.  
Flight позволяет вам быстро и легко создавать RESTful веб-приложения.

``` php
require 'flight/Flight.php';

// Определение маршрута
Flight::route('/', function(){
  echo 'hello world!';
});

// Запуск приложения
Flight::start();
```

[Узнать больше](learn)

# Требования

Flight требует PHP 7.4 или выше.

# Лицензия

Flight выпускается под лицензией [MIT](https://github.com/mikecao/flight/blob/master/LICENSE).

# Сообщество

Мы на Matrix! Общайтесь с нами в [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Участие

Этот сайт размещен на [Github](https://github.com/mikecao/flightphp.com).  
Обновления и переводы на другие языки приветствуются.
