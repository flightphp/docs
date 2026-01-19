# Framework PHP Flight

Flight é um framework rápido, simples e extensível para PHP — construído para desenvolvedores que querem fazer as coisas rapidamente, sem complicações. Seja você construindo um app web clássico, uma API ultrarrápida ou experimentando com as ferramentas mais recentes alimentadas por IA, o baixo impacto e o design direto do Flight o tornam uma escolha perfeita. Flight é projetado para ser enxuto, mas também pode lidar com requisitos de arquitetura empresarial.

## Por Que Escolher Flight?

- **Amigável para Iniciantes:** Flight é um ótimo ponto de partida para novos desenvolvedores PHP. Sua estrutura clara e sintaxe simples ajudam você a aprender desenvolvimento web sem se perder em boilerplate.
- **Amado por Profissionais:** Desenvolvedores experientes adoram o Flight por sua flexibilidade e controle. Você pode escalar de um protótipo minúsculo para um app completo sem trocar de framework.
- **Compatível com Versões Anteriores:** Nós valorizamos seu tempo. Flight v3 é uma ampliação do v2, mantendo quase toda a mesma API. Acreditamos em evolução, não em revolução — nada de "quebrar o mundo" toda vez que uma versão principal é lançada.
- **Zero Dependências:** O núcleo do Flight é completamente livre de dependências — sem polyfills, sem pacotes externos, nem mesmo interfaces PSR. Isso significa menos vetores de ataque, um footprint menor e nenhuma mudança quebrada surpreendente de dependências upstream. Plugins opcionais podem incluir dependências, mas o núcleo sempre permanecerá enxuto e seguro.
- **Focado em IA:** O overhead mínimo e a arquitetura limpa do Flight o tornam ideal para integrar ferramentas e APIs de IA. Seja você construindo chatbots inteligentes, painéis impulsionados por IA ou apenas querendo experimentar, Flight sai do caminho para que você possa se concentrar no que importa. O [skeleton app](https://github.com/flightphp/skeleton) vem com arquivos de instruções pré-construídos para os principais assistentes de codificação de IA logo de cara! [Saiba mais sobre o uso de IA com Flight](/learn/ai)

## Visão Geral em Vídeo

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">Simples o suficiente, não é?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Saiba mais</a> sobre o Flight na documentação!
    </div>
  </div>
</div>

## Início Rápido

Para uma instalação rápida e básica, instale com Composer:

```bash
composer require flightphp/core
```

Ou você pode baixar um zip do repositório [aqui](https://github.com/flightphp/core). Em seguida, você teria um arquivo `index.php` básico como o seguinte:

```php
<?php

// if installed with composer
require 'vendor/autoload.php';
// or if installed manually by zip file
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json([
	'hello' => 'world'
  ]);
});

Flight::start();
```

É isso! Você tem um aplicativo básico do Flight. Agora você pode executar este arquivo com `php -S localhost:8000` e visitar `http://localhost:8000` no seu navegador para ver a saída.

## Aplicativo Skeleton/Boilerplate

Há um app de exemplo para ajudar você a iniciar seu projeto com o Flight. Ele tem um layout estruturado, configurações básicas todas definidas e lida com scripts do composer logo de cara! Confira [flightphp/skeleton](https://github.com/flightphp/skeleton) para um projeto pronto para uso, ou visite a página de [exemplos](examples) para inspiração. Quer ver como a IA se encaixa? [Explore exemplos impulsionados por IA](/learn/ai).

## Instalando o Aplicativo Skeleton

Fácil o suficiente!

```bash
# Create the new project
composer create-project flightphp/skeleton my-project/
# Enter your new project directory
cd my-project/
# Bring up the local dev-server to get started right away!
composer start
```

Ele criará a estrutura do projeto, configurará os arquivos que você precisa, e você estará pronto para começar!

## Alto Desempenho

Flight é um dos frameworks PHP mais rápidos por aí. Seu núcleo leve significa menos overhead e mais velocidade — perfeito tanto para apps tradicionais quanto para projetos modernos impulsionados por IA. Você pode ver todos os benchmarks em [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

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


## Flight e IA

Curioso sobre como ele lida com IA? [Descubra](/learn/ai) como o Flight torna o trabalho com seu LLM de codificação favorito fácil!

## Estabilidade e Compatibilidade com Versões Anteriores

Nós valorizamos seu tempo. Todos nós vimos frameworks que se reinventam completamente a cada poucos anos, deixando os desenvolvedores com código quebrado e migrações caras. Flight é diferente. Flight v3 foi projetado como uma ampliação do v2, o que significa que a API que você conhece e ama não foi removida. Na verdade, a maioria dos projetos v2 funcionará sem alterações no v3. 

Estamos comprometidos em manter o Flight estável para que você possa se concentrar em construir seu app, não em consertar seu framework.

# Comunidade

Estamos no Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

E no Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contribuindo

Há duas maneiras de contribuir para o Flight:

1. Contribua para o framework principal visitando o [repositório principal](https://github.com/flightphp/core).
2. Ajude a melhorar a documentação! Este site de documentação é hospedado no [Github](https://github.com/flightphp/docs). Se você encontrar um erro ou quiser melhorar algo, sinta-se à vontade para enviar um pull request. Adoramos atualizações e novas ideias — especialmente em torno de IA e novas tecnologias!

# Requisitos

Flight requer PHP 7.4 ou superior.

**Nota:** PHP 7.4 é suportado porque, no momento da redação (2024), o PHP 7.4 é a versão padrão para algumas distribuições Linux LTS. Forçar uma mudança para PHP >8 causaria muitos problemas para esses usuários. O framework também suporta PHP >8.

# Licença

Flight é lançado sob a licença [MIT](https://github.com/flightphp/core/blob/master/LICENSE).