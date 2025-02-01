# O que é Flight?

Flight é um framework rápido, simples e extensível para PHP. É bastante versátil e pode ser usado para construir qualquer tipo de aplicação web. É construído com simplicidade em mente e escrito de uma maneira que é fácil de entender e usar.

Flight é um ótimo framework para iniciantes que estão novos no PHP e querem aprender a construir aplicações web. Também é um ótimo framework para desenvolvedores experientes que desejam mais controle sobre suas aplicações web. É projetado para construir facilmente uma API RESTful, uma aplicação web simples ou uma aplicação web complexa.

## Começo Rápido

```php
<?php

// se instalado com composer
require 'vendor/autoload.php';
// ou se instalado manualmente através de arquivo zip
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
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Player de vídeo do YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Simples o suficiente, certo?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Saiba mais sobre Flight na documentação!</a>

    </div>
  </div>
</div>

### Aplicativo Skeleton/Boilerplate

Há um aplicativo de exemplo que pode ajudá-lo a começar com o Flight Framework. Vá para [flightphp/skeleton](https://github.com/flightphp/skeleton) para instruções sobre como começar! Você também pode visitar a página de [exemplos](examples) para se inspirar em algumas das coisas que você pode fazer com Flight.

# Comunidade

Estamos no Matrix Chat, converse conosco em [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuindo

Existem duas maneiras de você contribuir com o Flight:

1. Você pode contribuir para o núcleo do framework visitando o [repositório principal](https://github.com/flightphp/core).
1. Você pode contribuir para a documentação. Este site de documentação está hospedado no [Github](https://github.com/flightphp/docs). Se você notar um erro ou quiser melhorar algo, fique à vontade para corrigir e enviar um pull request! Tentamos nos manter atualizados, mas atualizações e traduções de idiomas são bem-vindas.

# Requisitos

Flight requer PHP 7.4 ou superior.

**Nota:** PHP 7.4 é suportado porque, no momento da escrita (2024), o PHP 7.4 é a versão padrão para algumas distribuições Linux LTS. Forçar uma mudança para PHP >8 causaria muitos problemas para esses usuários. O framework também suporta PHP >8.

# Licença

Flight é lançado sob a licença [MIT](https://github.com/flightphp/core/blob/master/LICENSE).