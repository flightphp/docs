# Roteamento

> **Nota:** Quer entender mais sobre roteamento? Confira a página ["por que um framework?"](/learn/why-frameworks) para uma explicação mais aprofundada.

O roteamento básico no Flight é feito correspondendo um padrão de URL com uma função de retorno ou um array de uma classe e método.

```php
Flight::route('/', function(){
    echo 'olá mundo!';
});
```

> As rotas são correspondidas na ordem em que são definidas. A primeira rota a corresponder a uma solicitação será invocada.

### Callbacks/Funções
O callback pode ser qualquer objeto que seja chamável. Você pode usar uma função regular:

```php
function hello() {
    echo 'olá mundo!';
}

Flight::route('/', 'hello');
```

### Classes
Você também pode usar um método estático de uma classe:

```php
class Greeting {
    public static function hello() {
        echo 'olá mundo!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Ou criando um objeto primeiro e depois chamando o método:

```php

// Saudacao.php
class Greeting
{
    public function __construct() {
        $this->name = 'Fulano';
    }

    public function hello() {
        echo "Olá, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// Você também pode fazer isso sem criar o objeto primeiro
// Nota: Nenhum argumento será injetado no construtor
Flight::route('/', [ 'Greeting', 'hello' ]);
// Além disso, você pode usar essa sintaxe mais curta
Flight::route('/', 'Greeting->hello');
// ou
Flight::route('/', Greeting::class.'->hello');
```

#### Injeção de Dependência via DIC (Container de Injeção de Dependência)
Se você deseja usar injeção de dependência via um container (PSR-11, PHP-DI, Dice, etc), o único tipo de rotas onde isso está disponível é criando o objeto diretamente e usando o container para criar seu objeto ou você pode usar strings para definir a classe e o método a serem chamados. Você pode ir para a página [Injeção de Dependência](/learn/extending) para obter mais informações.

Aqui está um exemplo rápido:

```php

use flight\database\PdoWrapper;

// Saudacao.php
class Greeting
{
    protected PdoWrapper $pdoWrapper;
    public function __construct(PdoWrapper $pdoWrapper) {
        $this->pdoWrapper = $pdoWrapper;
    }

    public function hello(int $id) {
        // faça algo com $this->pdoWrapper
        $nome = $this->pdoWrapper->fetchField("SELECT nome FROM users WHERE id = ?", [ $id ]);
        echo "Olá, mundo! Meu nome é {$nome}!";
    }
}

// index.php

// Configure o container com os parâmetros de que você precisa
// Veja a página de Injeção de Dependência para mais informações sobre PSR-11
$dice = new \Dice\Dice();

// Não se esqueça de reatribuir a variável com '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
    'shared' => true,
    'constructParams' => [ 
        'mysql:host=localhost;dbname=test', 
        'root',
        'password'
    ]
]);

// Registre o manipulador do container
Flight::registerContainerHandler(function($class, $params) use ($dice) {
    return $dice->create($class, $params);
});

// Rotas como de costume
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// ou
Flight::route('/hello/@id', 'Greeting->hello');
// ou
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Roteamento de Método

Por padrão, os padrões de rota são correspondidos a todos os métodos de solicitação. Você pode responder a métodos específicos colocando um identificador antes da URL.

```php
Flight::route('GET /', function () {
  echo 'Recebi um pedido GET.';
});

Flight::route('POST /', function () {
  echo 'Recebi um pedido POST.';
});

// Você não pode usar Flight::get() para rotas, pois isso é um método
//    para obter variáveis, não criar uma rota.
// Flight::post('/', function() { /* código */ });
// Flight::patch('/', function() { /* código */ });
// Flight::put('/', function() { /* código */ });
// Flight::delete('/', function() { /* código */ });
```

Você também pode mapear vários métodos para um único callback usando um delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recebi um pedido GET ou POST.';
});
```

Além disso, você pode pegar o objeto Router que possui alguns métodos auxiliares para você usar:

```php

$router = Flight::router();

// mapeia todos os métodos
$router->map('/', function() {
    echo 'olá mundo!';
});

// pedido GET
$router->get('/users', function() {
    echo 'usuários';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Expressões Regulares

Você pode usar expressões regulares em suas rotas:

```php
Flight::route('/usuario/[0-9]+', function () {
  // Isso corresponderá a /usuario/1234
});
```

Embora este método esteja disponível, é recomendado usar parâmetros nomeados, ou
parâmetros nomeados com expressões regulares, pois eles são mais legíveis e mais fáceis de manter.

## Parâmetros Nomeados

Você pode especificar parâmetros nomeados em suas rotas que serão passados para
sua função de retorno.

```php
Flight::route('/@nome/@id', function (string $nome, string $id) {
  echo "olá, $nome ($id)!";
});
```

Você também pode incluir expressões regulares com seus parâmetros nomeados usando
o delimitador `:`:

```php
Flight::route('/@nome/@id:[0-9]{3}', function (string $nome, string $id) {
  // Isso corresponderá a /bob/123
  // Mas não corresponderá a /bob/12345
});
```

> **Nota:** A correspondência de grupos regex `()` com parâmetros nomeados não é suportada. :'\(

## Parâmetros Opcionais

Você pode especificar parâmetros nomeados que são opcionais para corresponder envolvendo
segmentos entre parênteses.

```php
Flight::route(
  '/blog(/@ano(/@mes(/@dia)))',
  function(?string $ano, ?string $mes, ?string $dia) {
    // Isso corresponderá às seguintes URLs:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Quaisquer parâmetros opcionais que não forem correspondidos serão passados como `NULL`.

## Curelas

A correspondência é feita apenas em segmentos individuais de URL. Se você deseja corresponder a vários
segmentos, você pode usar o curinga `*`.

```php
Flight::route('/blog/*', function () {
  // Isso corresponderá a /blog/2000/02/01
});
```

Para rotear todas as solicitações para um único callback, você pode fazer:

```php
Flight::route('*', function () {
  // Faça algo
});
```

## Passando

Você pode passar a execução para a próxima rota correspondente retornando `true` de
sua função de retorno.

```php
Flight::route('/usuario/@nome', function (string $nome) {
  // Verifique alguma condição
  if ($nome !== "João") {
    // Continue para a próxima rota
    return true;
  }
});

Flight::route('/usuario/*', function () {
  // Isso será chamado
});
```

## Alias de Rota

Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente mais tarde em seu código (como em um modelo, por exemplo).

```php
Flight::route('/usuarios/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');

// mais tarde em algum lugar do código
Flight::getUrl('visualizacao_usuario', [ 'id' => 5 ]); // retornará '/usuarios/5'
```

Isso é especialmente útil se sua URL mudar. No exemplo acima, digamos que usuários foram movidos para `/admin/usuarios/@id` ao invés disso.
Com o uso de alias, você não precisa alterar em qualquer lugar que faça referência ao alias, porque o alias agora retornará `/admin/usuarios/5` como no
exemplo acima.

O alias de rota ainda funciona em grupos também:

```php
Flight::group('/usuarios', function() {
    Flight::route('/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');
});


// mais tarde em algum lugar do código
Flight::getUrl('visualizacao_usuario', [ 'id' => 5 ]); // retornará '/usuarios/5'
```

## Informações da Rota

Se você quiser inspecionar as informações da rota correspondente, pode solicitar que o objeto da rota seja passado para sua função de retorno passando `true` como terceiro parâmetro no
método de roteamento. O objeto da rota sempre será o último parâmetro passado para sua
função de retorno.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array de métodos HTTP correspondentes
  $route->methods;

  // Array de parâmetros nomeados
  $route->params;

  // Expressão regular correspondente
  $route->regex;

  // Contém o conteúdo de qualquer '*' usado no padrão de URL
  $route->splat;

  // Mostra o caminho da URL.... se você realmente precisar
  $route->pattern;

  // Mostra qual middleware está atribuído a isto
  $route->middleware;

  // Mostra o alias atribuído a esta rota
  $route->alias;
}, true);
```

## Agrupamento de Rotas

Pode haver momentos em que você deseja agrupar rotas relacionadas juntas (como `/api/v1`).
Você pode fazer isso usando o método `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/usuarios', function () {
    // Corresponde a /api/v1/usuarios
  });

  Flight::route('/posts', function () {
    // Corresponde a /api/v1/posts
  });
});
```

Você pode até aninhar grupos de grupos:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
    // Flight::get() obtém variáveis, não define uma rota! Veja abaixo o contexto do objeto
    Flight::route('GET /usuarios', function () {
      // Corresponde a GET /api/v1/usuarios
    });

    Flight::post('/posts', function () {
      // Corresponde a POST /api/v1/posts
    });

    Flight::put('/posts/1', function () {
      // Corresponde a PUT /api/v1/posts
    });
  });
  Flight::group('/v2', function () {

    // Flight::get() obtém variáveis, não define uma rota! Veja abaixo o contexto do objeto
    Flight::route('GET /usuarios', function () {
      // Corresponde a GET /api/v2/usuarios
    });
  });
});
```

### Agrupamento com Contexto de Objeto

Você ainda pode usar o agrupamento de rotas com o objeto `Engine` da seguinte maneira:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // use a variável $router
  $router->get('/usuarios', function () {
    // Corresponde a GET /api/v1/usuarios
  });

  $router->post('/posts', function () {
    // Corresponde a POST /api/v1/posts
  });
});
```

## Streaming

Agora é possível transmitir respostas para o cliente usando o método `streamWithHeaders()`.
Isso é útil para enviar arquivos grandes, processos em execução longa ou gerar respostas grandes.
A transmissão de uma rota é tratada de forma um pouco diferente de uma rota regular.

> **Nota:** A transmissão de respostas só está disponível se você tiver [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) configurado como false.

### Transmitir com Cabeçalhos Manuais

Você pode transmitir uma resposta para o cliente usando o método `stream()` em uma rota. Se você
fizer isso, deve definir todos os métodos manualmente antes de enviar qualquer coisa para o cliente.
Isso é feito com a função `header()` do PHP ou o método `Flight::response()->setRealHeader()`.

```php
Flight::route('/@nome_arquivo', function($nome_arquivo) {

    // obviamente você sanitizaria o caminho e tudo o mais.
    $nomeArquivoSeguro = basename($nome_arquivo);

    // Se você tiver cabeçalhos adicionais a definir aqui depois que a rota for executada
    // você deve defini-los antes de qualquer coisa ser ecoada.
    // Eles devem todos ser chamada direta para a função header() ou
    // uma chamada para o método Flight::response()->setRealHeader()
    header('Content-Disposition: attachment; filename="'.$nomeArquivoSeguro.'"');
    // ou
    Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$nomeArquivoSeguro.'"');

    $dadosArquivo = file_get_contents('/algum/caminho/para/arquivos/'.$nomeArquivoSeguro);

    // Captura de erro e o que mais
    if(empty($dadosArquivo)) {
        Flight::halt(404, 'Arquivo não encontrado');
    }

    // defina manualmente o tamanho do conteúdo se desejar
    header('Content-Length: '.filesize($nome_arquivo));

    // Transmita os dados para o cliente
    echo $dadosArquivo;

// Esta é a linha mágica aqui
})->stream();
```

### Transmitir com Cabeçalhos

Você também pode usar o método `streamWithHeaders()` para definir os cabeçalhos antes de começar a transmitir.

```php
Flight::route('/stream-usuarios', function() {

    // você pode adicionar quaisquer cabeçalhos adicionais que desejar aqui
    // você só precisa usar header() ou Flight::response()->setRealHeader()

    // como você puxa seus dados, apenas como exemplo...
    $usuarios_stmt = Flight::db()->query("SELECT id, nome, sobrenome FROM usuarios");

    echo '{';
    $contador_usuarios = count($usuarios);
    while($usuario = $usuarios_stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($usuario);
        if(--$contador_usuarios > 0) {
            echo ',';
        }

        // Isso é necessário para enviar os dados para o cliente
        ob_flush();
    }
    echo '}';

// É assim que você definirá os cabeçalhos antes de começar a transmitir.
})->streamWithHeaders([
    'Content-Type' => 'application/json',
    'Content-Disposition' => 'attachment; filename="usuarios.json"',
    // código de status opcional, padrão é 200
    'status' => 200
]);
```