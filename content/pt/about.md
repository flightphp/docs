# O que é Flight?

Flight é um framework rápido, simples e extensível para PHP. É bastante versátil e pode ser usado para construir qualquer tipo de aplicação web. Foi construído com simplicidade em mente e é escrito de uma forma fácil de entender e usar.

Flight é um ótimo framework para iniciantes que são novos no PHP e querem aprender como construir aplicações web. Também é um ótimo framework para desenvolvedores experientes que desejam construir aplicações web de forma rápida e fácil. Ele é projetado para construir facilmente uma API RESTful, uma aplicação web simples ou uma aplicação web complexa.

```php
<?php

// se instalado com composer
require 'vendor/autoload.php';
// ou se instalado manualmente pelo arquivo zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'olá mundo!';
});

Flight::start();
```

Simples o suficiente, certo? [Saiba mais sobre o Flight!](learn)

## Início Rápido
Há um aplicativo de exemplo que pode ajudá-lo a começar com o Framework Flight. Acesse [flightphp/skeleton](https://github.com/flightphp/skeleton) para obter instruções sobre como começar! Você também pode visitar a página de [exemplos](examples) para se inspirar em algumas das coisas que você pode fazer com o Flight.

# Comunidade

Estamos no Matrix! Converse conosco em [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuição

Existem duas maneiras pelas quais você pode contribuir para o Flight:

1. Você pode contribuir para o framework principal visitando o [repositório principal](https://github.com/flightphp/core).
1. Você pode contribuir para a documentação. Este site de documentação é hospedado no [Github](https://github.com/flightphp/docs). Se você notar algum erro ou quiser melhorar algo, sinta-se à vontade para corrigi-lo e enviar um pull request! Tentamos manter tudo atualizado, mas atualizações e traduções de idiomas são bem-vindas.

# Requisitos

O Flight requer PHP 7.4 ou superior.

**Nota:** PHP 7.4 é suportado porque no momento atual da escrita (2024) o PHP 7.4 é a versão padrão para algumas distribuições Linux LTS. Forçar a mudança para PHP >8 causaria muitos problemas para esses usuários. O framework também suporta PHP >8.

# Licença

O Flight é lançado sob a licença [MIT](https://github.com/flightphp/core/blob/master/LICENSE).  