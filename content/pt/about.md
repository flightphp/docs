# O que é o Flight?

Flight é um framework rápido, simples e extensível para PHP. É bastante versátil e pode ser usado para construir qualquer tipo de aplicação web. É construído com simplicidade em mente e é escrito de uma forma que é fácil de entender e utilizar.

Flight é um ótimo framework para iniciantes que são novos no PHP e desejam aprender como construir aplicações web. Também é um ótimo framework para desenvolvedores experientes que desejam ter mais controle sobre suas aplicações web. É projetado para construir facilmente uma API RESTful, uma aplicação web simples ou uma aplicação web complexa.

## Início Rápido

```php
<?php

// se instalado com composer
require 'vendor/autoload.php';
// ou se instalado manualmente por arquivo zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'olá mundo!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube player de vídeo" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

Simples o suficiente, certo? [Saiba mais sobre o Flight na documentação!](learn)

### App Esqueleto/Boilerplate

Há um app de exemplo que pode ajudá-lo a começar com o Framework Flight. Acesse [flightphp/skeleton](https://github.com/flightphp/skeleton) para instruções sobre como começar! Você também pode visitar a página [exemplos](examples) para se inspirar em algumas das coisas que você pode fazer com o Flight.

# Comunidade

Estamos no Chat do Matrix, converse conosco em [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuição

Existem duas maneiras pelas quais você pode contribuir com o Flight: 

1. Você pode contribuir com o framework central visitando o [repositório principal](https://github.com/flightphp/core). 
1. Você pode contribuir com a documentação. Este site de documentação é hospedado no [Github](https://github.com/flightphp/docs). Se você notar um erro ou quiser aprimorar algo, sinta-se à vontade para corrigir e enviar um pull request! Tentamos acompanhar as coisas, mas atualizações e traduções de idiomas são bem-vindas.

# Requisitos

Flight requer PHP 7.4 ou superior.

**Nota:** PHP 7.4 é suportado porque no momento atual da escrita (2024), o PHP 7.4 é a versão padrão para algumas distribuições LTS do Linux. Forçar a migração para o PHP >8 causaria muitos problemas para esses usuários. O framework também suporta PHP >8.

# Licença

O Flight é lançado sob a licença [MIT](https://github.com/flightphp/core/blob/master/LICENSE).