# O que é o Flight?

Flight é um framework rápido, simples e extensível para PHP. É bastante versátil e pode ser usado para construir qualquer tipo de aplicação web. Foi desenvolvido com simplicidade em mente e escrito de uma forma que é fácil de entender e usar.

Flight é um ótimo framework para iniciantes que são novos em PHP e querem aprender como construir aplicações web. Também é um excelente framework para desenvolvedores experientes que desejam mais controle sobre suas aplicações web. É projetado para construir facilmente uma API RESTful, uma aplicação web simples ou uma aplicação web complexa.

## Começo Rápido

```php
<?php

// se instalado com composer
require 'vendor/autoload.php';
// ou se instalado manualmente por arquivo zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Reprodutor de vídeo do YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Simples o suficiente, certo?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Saiba mais sobre o Flight na documentação!</a>

    </div>
  </div>
</div>

### Aplicativo Skeleton/Boilerplate

Há um aplicativo de exemplo que pode te ajudar a começar com o Flight Framework. Visite [flightphp/skeleton](https://github.com/flightphp/skeleton) para instruções sobre como começar! Você também pode visitar a página de [exemplos](examples) para inspiração sobre algumas das coisas que você pode fazer com o Flight.

# Comunidade

Estamos no Matrix. Converse conosco em [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuindo

Existem duas maneiras de você contribuir para o Flight:

1. Você pode contribuir para o núcleo do framework visitando o [repositório principal](https://github.com/flightphp/core).
1. Você pode contribuir para a documentação. Este site de documentação é hospedado no [Github](https://github.com/flightphp/docs). Se você notar um erro ou quiser desenvolver algo melhor, sinta-se à vontade para corrigi-lo e enviar um pull request! Tentamos nos manter atualizados, mas atualizações e traduções de idiomas são bem-vindas.

# Requisitos

O Flight requer PHP 7.4 ou superior.

**Nota:** O PHP 7.4 é suportado porque, no momento da redação (2024), o PHP 7.4 é a versão padrão para algumas distribuições Linux LTS. Forçar uma mudança para PHP >8 causaria muitos problemas para esses usuários. O framework também suporta PHP >8.

# Licença

O Flight é lançado sob a licença [MIT](https://github.com/flightphp/core/blob/master/LICENSE).