# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate) é um poderoso motor de templates PHP com compilação de assets, herança de templates e processamento de variáveis. Ele fornece uma maneira simples, mas flexível, de gerenciar templates com minificação integrada de CSS/JS e cache.

## Recursos

- **Herança de Templates**: Use layouts e inclua outros templates
- **Compilação de Assets**: Minificação e cache automáticos de CSS/JS
- **Processamento de Variáveis**: Variáveis de template com filtros e comandos
- **Codificação Base64**: Assets inline como URIs de dados
- **Integração com o Framework Flight**: Integração opcional com o framework PHP Flight

## Instalação

Instale com o composer.

```bash
composer require knifelemon/comment-template
```

## Configuração Básica

Existem algumas opções de configuração básicas para começar. Você pode ler mais sobre elas no [Repositório CommentTemplate](https://github.com/KnifeLemon/CommentTemplate).

### Método 1: Usando Função de Callback

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // Onde seus arquivos de template estão armazenados
    $engine->setTemplatesPath(__DIR__ . '/views');
    
    // Onde seus assets públicos serão servidos
    $engine->setPublicPath(__DIR__ . '/public');
    
    // Onde os assets compilados serão armazenados
    $engine->setAssetPath('assets');
    
    // Extensão do arquivo de template
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### Método 2: Usando Parâmetros do Construtor

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__ . '/public',    // publicPath - onde os assets serão servidos
    __DIR__ . '/views',     // skinPath - onde os arquivos de template estão armazenados  
    'assets',               // assetPath - onde os assets compilados serão armazenados
    '.php'                  // fileExtension - extensão do arquivo de template
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## Diretivas de Template

### Herança de Layout

Use layouts para criar uma estrutura comum:

**layout/global_layout.php**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <!--@contents-->
</body>
</html>
```

**view/page.php**:
```html
<!--@layout(layout/global_layout)-->
<h1>{$title}</h1>
<p>{$content}</p>
```

### Gerenciamento de Assets

#### Arquivos CSS
```html
<!--@css(/css/styles.css)-->          <!-- Minificado e em cache -->
<!--@cssSingle(/css/critical.css)-->  <!-- Arquivo único, não minificado -->
```

#### Arquivos JavaScript
O CommentTemplate suporta diferentes estratégias de carregamento de JavaScript:

```html
<!--@js(/js/script.js)-->             <!-- Minificado, carregado no final -->
<!--@jsAsync(/js/analytics.js)-->     <!-- Minificado, carregado no final com async -->
<!--@jsDefer(/js/utils.js)-->         <!-- Minificado, carregado no final com defer -->
<!--@jsTop(/js/critical.js)-->        <!-- Minificado, carregado no head -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- Minificado, carregado no head com async -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- Minificado, carregado no head com defer -->
<!--@jsSingle(/js/widget.js)-->       <!-- Arquivo único, não minificado -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- Arquivo único, não minificado, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- Arquivo único, não minificado, defer -->
```

#### Diretivas de Assets em Arquivos CSS/JS

O CommentTemplate também processa diretivas de assets dentro de arquivos CSS e JavaScript durante a compilação:

**Exemplo CSS:**
```css
/* Em seus arquivos CSS */
@font-face {
    font-family: 'CustomFont';
    src: url('<!--@asset(fonts/custom.woff2)-->') format('woff2');
}

.background-image {
    background: url('<!--@asset(images/bg.jpg)-->');
}

.inline-icon {
    background: url('<!--@base64(icons/star.svg)-->');
}
```

**Exemplo JavaScript:**
```javascript
/* Em seus arquivos JS */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Codificação Base64
```html
<!--@base64(images/logo.png)-->       <!-- Inline como URI de dados -->
```
** Exemplo: **
```html
<!-- Inline de imagens pequenas como URIs de dados para carregamento mais rápido -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    Ícone pequeno como fundo
</div>
```

#### Cópia de Assets
```html
<!--@asset(images/photo.jpg)-->       <!-- Copia asset único para o diretório público -->
<!--@assetDir(assets)-->              <!-- Copia diretório inteiro para o diretório público -->
```
** Exemplo: **
```html
<!-- Copia e referencia assets estáticos -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Banner Heroico">
<a href="<!--@asset(documents/brochure.pdf)-->" download>Baixar Brochura</a>

<!-- Copia diretório inteiro (fontes, ícones, etc.) -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### Inclusões de Template
```html
<!--@import(components/header)-->     <!-- Inclui outros templates -->
```
** Exemplo: **
```html
<!-- Inclui componentes reutilizáveis -->
<!--@import(components/header)-->

<main>
    <h1>Bem-vindo ao nosso site</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>Conteúdo principal aqui...</p>
    </div>
</main>

<!--@import(components/footer)-->
```

### Processamento de Variáveis

#### Variáveis Básicas
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### Filtros de Variáveis
```html
{$title|upper}                       <!-- Converte para maiúsculas -->
{$content|lower}                     <!-- Converte para minúsculas -->
{$html|striptag}                     <!-- Remove tags HTML -->
{$text|escape}                       <!-- Escapa HTML -->
{$multiline|nl2br}                   <!-- Converte quebras de linha em <br> -->
{$html|br2nl}                        <!-- Converte tags <br> em quebras de linha -->
{$description|trim}                  <!-- Remove espaços em branco -->
{$subject|title}                     <!-- Converte para título -->
```

#### Comandos de Variáveis
```html
{$title|default=Default Title}       <!-- Define valor padrão -->
{$name|concat= (Admin)}              <!-- Concatena texto -->
```

#### Comandos de Variáveis
```html
{$content|striptag|trim|escape}      <!-- Encadeia múltiplos filtros -->
```

## Estrutura de Projeto Exemplo

```
project/
├── source/
│   ├── layouts/
│   │   └── default.php
│   ├── components/
│   │   ├── header.php
│   │   └── footer.php
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── custom.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.min.js
│   └── homepage.php
├── public/
│   └── assets/           # Assets gerados
│       ├── css/
│       └── js/
└── vendor/
```