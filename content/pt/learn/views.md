# Visualizações

Flight fornece alguma funcionalidade básica de modelagem por padrão. Para exibir um modelo de visualização, chame o método `render` com o nome do arquivo de modelo e dados de modelo opcionais:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Os dados do modelo passados são automaticamente injetados no modelo e podem ser referenciados como uma variável local. Os arquivos de modelo são simplesmente arquivos PHP. Se o conteúdo do arquivo de modelo `hello.php` for:

```php
Olá, <?= $name ?>!
```

A saída seria:

```
Olá, Bob!
```

Você também pode definir manualmente variáveis de visualização usando o método set:

```php
Flight::view()->set('name', 'Bob');
```

A variável `name` agora está disponível em todas as suas visualizações. Então você pode simplesmente fazer:

```php
Flight::render('hello');
```

Observe que ao especificar o nome do modelo no método de renderização, você pode omitir a extensão `.php`.

Por padrão, o Flight procurará um diretório `views` para arquivos de modelo. Você pode definir um caminho alternativo para seus modelos configurando o seguinte:

```php
Flight::set('flight.views.path', '/caminho/para/views');
```

## Layouts

É comum os sites terem um único arquivo de modelo de layout com conteúdo alternante. Para renderizar conteúdo a ser usado em um layout, você pode passar um parâmetro opcional para o método `render`.

```php
Flight::render('header', ['heading' => 'Olá'], 'headerContent');
Flight::render('body', ['body' => 'Mundo'], 'bodyContent');
```

Sua visualização então terá variáveis salvas chamadas `headerContent` e `bodyContent`. Você pode então renderizar seu layout fazendo:

```php
Flight::render('layout', ['title' => 'Página Inicial']);
```

Se os arquivos de modelo forem assim:

`header.php`:

```php
<h1><?= $heading ?></h1>
```

`body.php`:

```php
<div><?= $body ?></div>
```

`layout.php`:

```php
<html>
  <head>
    <title><?= $title ?></title>
  </head>
  <body>
    <?= $headerContent ?>
    <?= $bodyContent ?>
  </body>
</html>
```

A saída seria:
```html
<html>
  <head>
    <title>Página Inicial</title>
  </head>
  <body>
    <h1>Olá</h1>
    <div>Mundo</div>
  </body>
</html>
```

## Visualizações Personalizadas

Flight permite que você altere a engine de visualização padrão simplesmente registrando sua própria classe de visualização. Aqui está como você usaria a [Smarty](http://www.smarty.net/) template engine para suas visualizações:

```php
// Carregar biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar o Smarty como a classe de visualização
// Também passe uma função de retorno de chamada para configurar o Smarty ao carregar
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Atribuir dados do modelo
Flight::view()->assign('name', 'Bob');

// Exibir o modelo
Flight::view()->display('hello.tpl');
```

Para completude, você também deve substituir o método de renderização padrão do Flight:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```  