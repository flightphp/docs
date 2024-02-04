# Roteamento

O roteamento no Flight é feito correspondendo a um padrão de URL com uma função de retorno.

```php
Flight::route('/', function(){
    echo 'olá mundo!';
});
```

A função de retorno pode ser qualquer objeto que seja chamável. Então você pode usar uma função regular:

```php
function hello(){
    echo 'olá mundo!';
}

Flight::route('/', 'hello');
```

Ou um método de classe:

```php
class Greeting {
    public static function hello() {
        echo 'olá mundo!';
    }
}

Flight::route('/', array('Saudacao','hello'));
```

Ou um método de objeto:

```php
class Saudacao
{
    public function __construct() {
        $this->nome = 'Fulano';
    }

    public function hello() {
        echo "Olá, {$this->nome}!";
    }
}

$saudacao = new Saudacao();

Flight::route('/', array($saudacao, 'hello'));
```

As rotas são correspondidas na ordem em que são definidas. A primeira rota a corresponder a uma solicitação será invocada.

## Roteamento por Método

Por padrão, os padrões de rota são correspondidos a todos os métodos de solicitação. Você pode responder a métodos específicos colocando um identificador antes da URL.

```php
Flight::route('GET /', function () {
  echo 'Recebi uma solicitação GET.';
});

Flight::route('POST /', function () {
  echo 'Recebi uma solicitação POST.';
});
```

Você também pode mapear vários métodos para um único retorno usando um delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recebi uma solicitação GET ou POST.';
});
```

## Expressões Regulares

Você pode usar expressões regulares em suas rotas:

```php
Flight::route('/usuario/[0-9]+', function () {
  // Isso corresponderá a /usuario/1234
});
```

## Parâmetros Nomeados

Você pode especificar parâmetros nomeados em suas rotas que serão passados para a função de retorno.

```php
Flight::route('/@nome/@id', function (string $nome, string $id) {
  echo "olá, $nome ($id)!";
});
```

Você também pode incluir expressões regulares com seus parâmetros nomeados usando o delimitador `:`:

```php
Flight::route('/@nome/@id:[0-9]{3}', function (string $nome, string $id) {
  // Isso corresponderá a /joao/123
  // Mas não corresponderá a /joao/12345
});
```

Corresponder grupos de regex `()` com parâmetros nomeados não é suportado.

## Parâmetros Opcionais

Você pode especificar parâmetros nomeados que são opcionais para corresponder, envolvendo
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

Quaisquer parâmetros opcionais que não correspondam serão passados como NULL.

## Curenas

A correspondência é feita apenas em segmentos individuais de URL. Se você deseja corresponder a vários
segmentos, você pode usar o curinga `*`.

```php
Flight::route('/blog/*', function () {
  // Isso corresponderá a /blog/2000/02/01
});
```

Para direcionar todas as solicitações para um único retorno, você pode fazer:

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
  if ($nome !== "Joao") {
    // Continuar para a próxima rota
    return true;
  }
});

Flight::route('/usuario/*', function () {
  // Isso será chamado
});
```

## Informações da Rota

Se você quiser inspecionar as informações da rota correspondente, pode solicitar a rota
objeto para ser passado para sua função de retorno passando `true` como terceiro parâmetro em
o método de roteamento. O objeto de rota será sempre o último parâmetro passado para sua
função de retorno.

```php
Flight::route('/', function(\flight\net\Route $rota) {
  // Array de métodos HTTP correspondidos
  $rota->metodos;

  // Array de parâmetros nomeados
  $rota->params;

  // Expressão regular correspondente
  $rota->regex;

  // Contém o conteúdo de qualquer '*' usado no padrão de URL
  $rota->splat;
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
	// Flight::get() obtém variáveis, não define uma rota! Veja o contexto do objeto abaixo
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

	// Flight::get() obtém variáveis, não define uma rota! Veja o contexto do objeto abaixo
	Flight::route('GET /usuarios', function () {
	  // Corresponde a GET /api/v2/usuarios
	});
  });
});
```

### Agrupando com Contexto do Objeto

Você ainda pode usar o agrupamento de rota com o objeto `Engine` da seguinte maneira:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $roteador) {
  $roteador->get('/usuarios', function () {
	// Corresponde a GET /api/v1/usuarios
  });

  $roteador->post('/posts', function () {
	// Corresponde a POST /api/v1/posts
  });
});
```

## Aliasing de Rotas

Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente posteriormente em seu código (como um modelo, por exemplo).

```php
Flight::route('/usuarios/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');

// mais tarde no código em algum lugar
Flight::getUrl('visualizacao_usuario', [ 'id' => 5 ]); // retornará '/usuarios/5'
```

Isso é especialmente útil se sua URL mudar. No exemplo acima, digamos que os usuários foram movidos para `/admin/usuarios/@id` em vez disso.
Com o uso de alias, você não precisa alterar em nenhum lugar que você faça referência ao alias porque o alias agora retornará `/admin/usuarios/5` como no
exemplo acima.

O alias de rota ainda funciona em grupos também:

```php
Flight::group('/usuarios', function() {
    Flight::route('/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');
});

// mais tarde no código em algum lugar
Flight::getUrl('visualizacao_usuario', [ 'id' => 5 ]); // retornará '/usuarios/5'
```

## Middleware de Rota
O Flight suporta middleware de rota e grupo de middleware de rota. O middleware é uma função que é executada antes (ou depois) do retorno da rota. Esta é uma ótima maneira de adicionar verificações de autenticação de API em seu código ou validar se o usuário tem permissão para acessar a rota.

Aqui está um exemplo básico:

```php
// Se você fornecer apenas uma função anônima, ela será executada antes do retorno da rota. 
// não existem funções de middleware "depois" exceto para classes (veja abaixo)
Flight::route('/caminho', function() { echo ' Aqui estou!'; })->addMiddleware(function() {
	echo 'Middleware primeiro!';
});

Flight::start();

// Isso resultará em "Middleware primeiro! Aqui estou!"
```

Existem algumas notas muito importantes sobre middleware das quais você deve estar ciente antes de usá-los:
- As funções do middleware são executadas na ordem em que são adicionadas à rota. A execução é semelhante à forma como [Slim Framework manipula isso](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Os Antes são executados na ordem adicionada e os Depois são executados na ordem reversa.
- Se sua função de middleware retornar false, toda a execução será interrompida e será lançado um erro 403 Proibido. Provavelmente você vai querer lidar com isso de forma mais graciosa com um `Flight::redirect()` ou algo semelhante.
- Se você precisar de parâmetros da sua rota, eles serão passados em um array único para sua função de middleware. (`function($params) { ... }` ou `public function before($params) {}`). A razão para isso é que você pode estruturar seus parâmetros em grupos e em alguns desses grupos, seus parâmetros podem realmente aparecer em uma ordem diferente que quebraria a função de middleware referindo-se ao parâmetro errado. Dessa forma, você pode acessá-los pelo nome em vez da posição.

### Classes de Middleware

O middleware também pode ser registrado como uma classe. Se você precisar da funcionalidade "depois", você deve usar uma classe.

```php
class MeuMiddleware {
	public function before($params) {
		echo 'Middleware primeiro!';
	}

	public function after($params) {
		echo 'Middleware último!';
	}
}

$MeuMiddleware = new MeuMiddleware();
Flight::route('/caminho', function() { echo ' Aqui estou! '; })->addMiddleware($MeuMiddleware); // também ->addMiddleware([ $MeuMiddleware, $MeuMiddleware2 ]);

Flight::start();

// Isso exibirá "Middleware primeiro! Aqui estou! Middleware último!"
```

### Grupos de Middleware

Você pode adicionar um grupo de rota e, em seguida, cada rota nesse grupo terá o mesmo middleware também. Isso é útil se você precisar agrupar um monte de rotas por um middleware de autenticação de API para verificar a chave da API no cabeçalho.

```php

// adicionado ao final do método de grupo
Flight::group('/api', function() {
    Flight::route('/usuarios', function() { echo 'usuários'; }, false, 'usuarios');
	Flight::route('/usuarios/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');
}, [ new ApiAuthMiddleware() ]);
```  