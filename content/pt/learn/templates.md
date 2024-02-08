# Vistas

Flight fornece alguma funcionalidade básica de modelagem por padrão.

Se você precisar de necessidades de modelagem mais complexas, consulte os exemplos do Smarty e Latte na seção [Visualizações Personalizadas](#visualizações-personalizadas).

Para exibir um modelo de visualização, chame o método `render` com o nome do arquivo de modelo e os dados de modelo opcionais:

```php
Flight::render('hello.php', ['nome' => 'Bob']);
```

Os dados do modelo que você passa são automaticamente injetados no modelo e podem ser referenciados como uma variável local. Os arquivos de modelo são simplesmente arquivos PHP. Se o conteúdo do arquivo de modelo `hello.php` for:

```php
Olá, <?= $nome ?>!
```

A saída seria:

```
Olá, Bob!
```

Você também pode definir manualmente variáveis de visualização usando o método `set`:

```php
Flight::view()->set('nome', 'Bob');
```

A variável `nome` agora está disponível em todas as suas visualizações. Portanto, você pode simplesmente fazer:

```php
Flight::render('hello');
```

Observe que ao especificar o nome do modelo no método de renderização, você pode omitir a extensão `.php`.

Por padrão, o Flight procurará um diretório `views` para arquivos de modelo. Você pode definir um caminho alternativo para seus modelos configurando o seguinte:

```php
Flight::set('flight.views.path', '/caminho/para/views');
```

## Layouts

É comum os sites terem um único arquivo de modelo de layout com conteúdo intercambiável. Para renderizar conteúdo a ser usado em um layout, você pode passar um parâmetro opcional para o método `render`.

```php
Flight::render('cabeçalho', ['título' => 'Olá'], 'conteúdoCabeçalho');
Flight::render('corpo', ['corpo' => 'Mundo'], 'conteúdoCorpo');
```

Sua visualização então terá variáveis salvas chamadas `conteúdoCabeçalho` e `conteúdoCorpo`. Você pode então renderizar seu layout fazendo:

```php
Flight::render('layout', ['título' => 'Página Inicial']);
```

Se os arquivos de modelo parecerem com isto:

`cabeçalho.php`:

```php
<h1><?= $título ?></h1>
```

`corpo.php`:

```php
<div><?= $corpo ?></div>
```

`layout.php`:

```php
<html>
  <head>
    <title><?= $título ?></title>
  </head>
  <body>
    <?= $conteúdoCabeçalho ?>
    <?= $conteúdoCorpo ?>
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

Flight permite que você substitua o mecanismo de visualização padrão simplesmente registrando sua própria classe de visualização.

### Smarty

Veja como você usaria o [Smarty](http://www.smarty.net/) mecanismo de template para suas visualizações:

```php
// Carregar biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registrar o Smarty como a classe de visualização
// Além disso, passe uma função de retorno de chamada para configurar o Smarty no carregamento
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Atribuir dados do modelo
Flight::view()->assign('nome', 'Bob');

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

### Latte

Veja como você usaria o [Latte](https://latte.nette.org/) mecanismo de template para suas visualizações:

```php

// Registrar o Latte como a classe de visualização
// Além disso, passe uma função de retorno de chamada para configurar o Latte no carregamento
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Aqui é onde o Latte irá armazenar em cache seus modelos para acelerar as coisas
  // Uma coisa legal sobre o Latte é que ele atualiza automaticamente o seu cache
  // quando você faz alterações em seus modelos!
  $latte->setTempDirectory(__DIR__ . '/../cache/');

  // Diga ao Latte onde será o diretório raiz para suas visualizações.
  $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// E finalize para que você possa usar Flight::render() corretamente
Flight::map('render', function(string $template, array $data): void {
  // Isso é como $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```