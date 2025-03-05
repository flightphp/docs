# O que é Flight?

Flight é um framework rápido, simples e extensível para PHP. É bastante versátil e pode ser usado para construir qualquer tipo de aplicação web. Foi criado com simplicidade em mente e é escrito de uma forma que é fácil de entender e usar.

Flight é um ótimo framework para iniciantes que estão novos no PHP e querem aprender como construir aplicações web. Também é um excelente framework para desenvolvedores experientes que desejam mais controle sobre suas aplicações web. É projetado para construir facilmente uma API RESTful, uma aplicação web simples ou uma aplicação web complexa.

## Início Rápido

Primeiro, instale-o com o Composer

```bash
composer require flightphp/core
```

ou você pode baixar um zip do repositório [aqui](https://github.com/flightphp/core). Então você teria um arquivo básico `index.php` como o seguinte:

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

É isso! Você tem uma aplicação básica Flight. Agora você pode executar este arquivo com `php -S localhost:8000` e visitar `http://localhost:8000` em seu navegador para ver a saída.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Player de vídeo do YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Simples o suficiente certo?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Saiba mais sobre Flight na documentação!</a>

    </div>
  </div>
</div>

## É rápido?

Sim! Flight é rápido. É um dos frameworks PHP mais rápidos disponíveis. Você pode ver todos os benchmarks em [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Veja o benchmark abaixo com alguns outros frameworks PHP populares.

| Framework | Reqs/seg em texto simples | Reqs/seg em JSON |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238	   | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen	      | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## Aplicativo Skeleton/Boilerplate

Há um aplicativo de exemplo que pode ajudá-lo a começar com o Framework Flight. Vá para [flightphp/skeleton](https://github.com/flightphp/skeleton) para instruções sobre como começar! Você também pode visitar a página [exemplos](examples) para se inspirar sobre algumas das coisas que você pode fazer com Flight.

# Comunidade

Estamos no Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

E no Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contribuindo

Existem duas maneiras de você contribuir para o Flight:

1. Você pode contribuir para o framework principal visitando o [repositório principal](https://github.com/flightphp/core).
1. Você pode contribuir para a documentação. Este site de documentação está hospedado no [Github](https://github.com/flightphp/docs). Se você notar um erro ou quiser melhorar algo, fique à vontade para corrigir e enviar um pull request! Tentamos nos manter atualizados, mas atualizações e traduções são bem-vindas.

# Requisitos

Flight requer PHP 7.4 ou superior.

**Nota:** PHP 7.4 é suportado porque, no momento da redação (2024), o PHP 7.4 é a versão padrão para algumas distribuições Linux LTS. Forçar uma mudança para PHP >8 causaria muitos problemas para esses usuários. O framework também suporta PHP >8.

# Licença

Flight é lançado sob a licença [MIT](https://github.com/flightphp/core/blob/master/LICENSE).