# O que é o Flight?

O Flight é um framework rápido, simples e extensível para PHP. É bastante versátil e pode ser usado para construir qualquer tipo de aplicação web. É construído com simplicidade em mente e é escrito de forma fácil de entender e usar.

O Flight é um ótimo framework para iniciantes que são novos no PHP e desejam aprender como construir aplicações web. Também é um ótimo framework para desenvolvedores experientes que desejam ter mais controle sobre suas aplicações web. É projetado para facilitar a construção de uma API RESTful, uma aplicação web simples ou uma aplicação web complexa.

## Início Rápido

```php
<?php

// se instalado com o composer
require 'vendor/autoload.php';
// ou se instalado manualmente por arquivo zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'olá mundo!';
});

Flight::route('/json', function() {
  Flight::json(['olá' => 'mundo']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Reprodutor de vídeo do YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

Simples o suficiente, certo? [Saiba mais sobre o Flight na documentação!](learn)

### Esqueleto/Modelo de Aplicativo

Há um aplicativo de exemplo que pode ajudá-lo a começar com o Framework Flight. Visite [flightphp/skeleton](https://github.com/flightphp/skeleton) para instruções sobre como começar! Você também pode visitar a página de [exemplos](examples) para se inspirar em algumas coisas que você pode fazer com o Flight.

# Comunidade

Estamos no Matrix! Converse conosco em [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuindo

Existem duas maneiras de contribuir com o Flight:

1. Você pode contribuir para o framework principal visitando o [repositório principal](https://github.com/flightphp/core).
1. Você pode contribuir para a documentação. Este site de documentação é hospedado no [Github](https://github.com/flightphp/docs). Se notar um erro ou quiser aprimorar algo, sinta-se à vontade para corrigir e enviar um pull request! Tentamos manter as coisas atualizadas, mas atualizações e traduções de idiomas são bem-vindas.

# Requisitos

O Flight requer PHP 7.4 ou superior.

**Nota:** O PHP 7.4 é suportado porque no momento da escrita (2024) o PHP 7.4 é a versão padrão para algumas distribuições LTS do Linux. Forçar a mudança para o PHP >8 causaria muitos problemas para esses usuários. O framework também suporta o PHP >8.

# Licença

O Flight é lançado sob a licença [MIT](https://github.com/flightphp/core/blob/master/LICENSE).