# O que é Voo?

Voo é um framework rápido, simples e extensível para PHP.
Voo permite que você construa rapidamente e facilmente aplicações web RESTful.

```php
<?php

// se instalado com o composer
require 'vendor/autoload.php';
// ou se instalado manualmente pelo arquivo zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'olá mundo!';
});

Flight::start();
```

Simples o suficiente, certo? [Saiba mais sobre Voo!](learn)

## Aplicativo Esqueleto
Há um aplicativo de exemplo que pode ajudá-lo a começar com o Framework Voo. Visite [flightphp/skeleton](https://github.com/flightphp/skeleton) para instruções sobre como começar!

# Comunidade

Estamos no Matrix! Converse conosco em [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuição

Este site é hospedado no [Github](https://github.com/flightphp/docs). Se você notar um erro, sinta-se à vontade para corrigi-lo e enviar um pull request!
Tentamos nos manter atualizados, mas atualizações e traduções de idiomas são bem-vindas.

# Requisitos

Voo requer PHP 7.4 ou superior.

**Nota:** O PHP 7.4 é suportado porque, no momento da redação deste texto (2024), o PHP 7.4 é a versão padrão para algumas distribuições LTS do Linux. Forçar uma migração para o PHP >8 causaria muitos problemas para esses usuários. O framework também suporta o PHP >8.

# Licença

Voo é lançado sob a licença [MIT](https://github.com/flightphp/core/blob/master/LICENSE).