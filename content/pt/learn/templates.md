
# Visualizações

O Flight fornece alguma funcionalidade básica de modelagem por padrão.

Se você precisar de necessidades de modelagem mais complexas, consulte exemplos do Smarty e Latte na seção [Visualizações Personalizadas](#custom-views).

Para exibir um modelo de visualização, chame o método `render` com o nome
do arquivo de modelo e dados de modelo opcionais:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

Os dados do modelo que você passa são automaticamente injetados no modelo e podem
ser referenciados como uma variável local. Os arquivos de modelo são simplesmente arquivos PHP. Se o
conteúdo do arquivo de modelo `hello.php` for:

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

A variável `name` agora está disponível em todas as suas visualizações. Portanto, você pode simplesmente fazer:

```php
Flight::render('hello');
```

Observe que ao especificar o nome do modelo no método de renderização, você pode
omitir a extensão `.php`.

Por padrão, o Flight procurará um diretório `views` para arquivos de modelo. Você pode
definir um caminho alternativo para seus modelos configurando o seguinte:

```php
Flight::set('flight.views.path', '/caminho/para/views');
```

## Layouts

É comum que os sites tenham um único arquivo de modelo de layout com conteúdo alternante. Para renderizar conteúdo a ser usado em um layout, você pode passar um parâmetro opcional para o método `render`.

```php
Flight::render('header', ['heading' => 'Olá'], 'headerContent');
Flight::render('body', ['body' => 'Mundo'], 'bodyContent');
```

Sua visualização terá então variáveis salvas chamadas `headerContent` e `bodyContent`.
Você pode então renderizar seu layout fazendo:

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

O Flight permite que você substitua o mecanismo de visualização padrão simplesmente registrando sua
própria classe de visualização.

### Smarty

Veja como você usaria o [Smarty](http://www.smarty.net/)
mecanismo de modelo para suas visualizações:

```php
// Carregar biblioteca Smarty
requerer './Smarty/libs/Smarty.class.php';

// Registrar o Smarty como a classe de visualização
// Passe também uma função de retorno de chamada para configurar o Smarty ao carregar
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
});

// Atribuir dados de modelo
Flight::view()->assign('name', 'Bob');

// Exibir o modelo
Flight::view()->display('hello.tpl');
```

Para completude, você também deve substituir o método de renderização padrão do Flight:

```php
Flight::map('render', função (string $template, array $data): vazio {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

Veja como você usaria o [Latte](https://latte.nette.org/)
mecanismo de modelo para suas visualizações:

```php

// Registrar o Latte como a classe de visualização
// Passe também uma função de retorno de chamada para configurar o Latte ao carregar
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Aqui é onde o Latte irá armazenar em cache seus modelos para acelerar as coisas
  // Uma coisa legal sobre o Latte é que ele atualiza automaticamente seu
  // cache quando você faz alterações em seus modelos!
  $latte->setTempDirectory(__DIR__ . '/../cache/');

  // Diga ao Latte onde o diretório raiz de suas visualizações estará.
  $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// E envolva-o para que você possa usar Flight::render() corretamente
Flight::map('render', função (string $template, array $data): vazio {
  // Isso é como $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```