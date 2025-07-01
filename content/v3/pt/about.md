# O que é Flight?

Flight é um framework rápido, simples e extensível para PHP—construído para desenvolvedores que querem realizar tarefas rapidamente, sem complicações. Seja você construindo um aplicativo web clássico, uma API ultrarrápida ou experimentando as mais recentes ferramentas alimentadas por IA, o baixo consumo de recursos e o design direto do Flight o tornam uma escolha perfeita.

## Por que escolher Flight?

- **Amigável para iniciantes:** Flight é um ótimo ponto de partida para novos desenvolvedores PHP. Sua estrutura clara e sintaxe simples ajudam você a aprender desenvolvimento web sem se perder em códigos desnecessários.
- **Adorado por profissionais:** Desenvolvedores experientes adoram Flight por sua flexibilidade e controle. Você pode escalar de um protótipo pequeno para um aplicativo completo sem precisar mudar de framework.
- **Amigável para IA:** O baixo overhead e a arquitetura limpa do Flight o tornam ideal para integrar ferramentas e APIs de IA. Seja você construindo chatbots inteligentes, painéis impulsionados por IA ou apenas querendo experimentar, Flight sai do caminho para você se concentrar no que importa. [Saiba mais sobre o uso de IA com Flight](/learn/ai)

## Início Rápido

Primeiro, instale-o com Composer:

```bash
composer require flightphp/core
```

Ou você pode baixar um zip do repositório [aqui](https://github.com/flightphp/core). Em seguida, você terá um arquivo básico `index.php` como o seguinte:

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

Pronto! Você tem um aplicativo básico do Flight. Agora, você pode executar este arquivo com `php -S localhost:8000` e visitar `http://localhost:8000` no seu navegador para ver a saída.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Simples o suficiente, não?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Saiba mais sobre Flight na documentação!</a>
      <br>
      <button href="/learn/ai" class="btn btn-primary mt-3">Descubra como Flight facilita a IA</button>
    </div>
  </div>
</div>

## É rápido?

Absolutamente! Flight é um dos frameworks PHP mais rápidos por aí. Seu núcleo leve significa menos overhead e mais velocidade—perfeito para aplicativos tradicionais e projetos modernos impulsionados por IA. Você pode ver todos os benchmarks em [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Veja o benchmark abaixo com alguns outros frameworks PHP populares.

| Framework | Plaintext Reqs/sec | JSON Reqs/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238    | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen       | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## Aplicativo Esqueleto/Modelo

Há um exemplo de aplicativo para ajudar você a começar com Flight. Confira [flightphp/skeleton](https://github.com/flightphp/skeleton) para um projeto pronto para uso, ou visite a página de [exemplos](examples) para inspiração. Quer ver como a IA se encaixa? [Explore exemplos impulsionados por IA](/learn/ai).

# Comunidade

Estamos no Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

E no Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contribuindo

Existem duas maneiras de contribuir para Flight:

1. Contribua para o framework principal visitando o [repositório principal](https://github.com/flightphp/core).
2. Ajude a melhorar os documentos! Este site de documentação é hospedado no [Github](https://github.com/flightphp/docs). Se você encontrar um erro ou quiser melhorar algo, sinta-se à vontade para enviar um pull request. Adoramos atualizações e novas ideias—especialmente em torno de IA e novas tecnologias!

# Requisitos

Flight requer PHP 7.4 ou superior.

**Nota:** PHP 7.4 é suportado porque, no momento da escrita (2024), o PHP 7.4 é a versão padrão para algumas distribuições Linux LTS. Forçar uma mudança para PHP >8 causaria muitos problemas para esses usuários. O framework também suporta PHP >8.

# Licença

Flight é lançado sob a [licença MIT](https://github.com/flightphp/core/blob/master/LICENSE).