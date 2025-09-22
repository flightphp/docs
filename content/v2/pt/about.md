# O que é Flight?

Flight é um framework rápido, simples e extensível para PHP.  
Flight permite que você construa aplicações web RESTful de forma rápida e fácil.

``` php
require 'flight/Flight.php';

// Define a rota para a raiz
Flight::route('/', function(){
  echo 'hello world!';
});

// Inicia o Flight
Flight::start();
```

[Saiba mais](learn)

# Requisitos

Flight requer PHP 7.4 ou superior.

# Licença

Flight é liberado sob a licença [MIT](https://github.com/mikecao/flight/blob/master/LICENSE).

# Comunidade

Estamos no Matrix! Converse conosco em [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuindo

Este site está hospedado no [Github](https://github.com/mikecao/flightphp.com).  
Atualizações e traduções de idiomas são bem-vindas.
