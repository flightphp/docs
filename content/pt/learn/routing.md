# Roteamento

O roteamento no Flight é feito combinando um padrão de URL com uma função de retorno.

```php
Flight::route('/', function(){
    echo 'olá mundo!';
});
```

A função de retorno pode ser qualquer objeto que seja chamável. Assim, você pode usar uma função regular:

```php
function hello(){
    echo 'olá mundo!';
}

Flight::route('/', 'hello');
```

Ou um método de classe:

```php
class Saudacao {
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
        $this->name = 'Fulano de Tal';
    }

    public function hello() {
        echo "Olá, {$this->name}!";
    }
}

$saudacao = new Saudacao();

Flight::route('/', array($saudacao, 'hello'));
```

As rotas são combinadas na ordem em que são definidas. A primeira rota a corresponder a uma solicitação será invocada.

## Roteamento de Método

Por padrão, os padrões de rota são combinados com todos os métodos de solicitação. Você pode responder a métodos específicos colocando um identificador antes da URL.

```php
Flight::route('GET /', function () {
  echo 'Recebi uma solicitação GET.';
});

Flight::route('POST /', function () {
  echo 'Recebi uma solicitação POST.';
});
```

Você também pode mapear vários métodos para um único retorno usando o delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recebi uma solicitação GET ou POST.';
});
```

## Expressões Regulares

Você pode usar expressões regulares em suas rotas:

```php
Flight::route('/user/[0-9]+', function () {
  // Isso corresponderá a /user/1234
});
```

## Parâmetros Nomeados

Você pode especificar parâmetros nomeados em suas rotas que serão passados para
sua função de retorno.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "olá, $name ($id)!";
});
```

Você também pode incluir expressões regulares com seus parâmetros nomeados usando
o delimitador `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Isso corresponderá a /bob/123
  // Mas não corresponderá a /bob/12345
});
```

Combinar grupos de regex `()` com parâmetros nomeados não é suportado.

## Parâmetros Opcionais

Você pode especificar parâmetros nomeados que são opcionais para correspondência envolvendo
os segmentos em parênteses.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Isso corresponderá às seguintes URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Quaisquer parâmetros opcionais que não tenham correspondência serão passados como NULL.

## Curetas

A correspondência é feita apenas em segmentos individuais de URL. Se você deseja corresponder a vários
segmentos, você pode usar o curinga `*`.

```php
Flight::route('/blog/*', function () {
  // Isso corresponderá a /blog/2000/02/01
});
```

Para rotear todas as solicitações para um único retorno, você pode fazer:

```php
Flight::route('*', function () {
  // Faça algo
});
```

## Passagem

Você pode passar a execução para a próxima rota correspondente retornando `true` de
sua função de retorno.

```php
Flight::route('/user/@name', function (string $name) {
  // Verifique alguma condição
  if ($name !== "Bob") {
    // Continue para a próxima rota
    return true;
  }
});

Flight::route('/user/*', function () {
  // Isso será chamado
});
```

## Informações de Rota

Se você deseja inspecionar as informações de rota correspondente, pode solicitar a rota
objeto a ser passado para sua função de retorno passando `true` como terceiro parâmetro em
o método de rota. O objeto de rota sempre será o último parâmetro passado para sua
função de retorno.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Matriz de métodos HTTP correspondentes
  $route->methods;

  // Matriz de parâmetros nomeados
  $route->params;

  // Expressão regular correspondente
  $route->regex;

  // Contém o conteúdo de qualquer curinga usado no padrão de URL
  $route->splat;
}, true);
```

## Agrupamento de Rotas

Pode haver momentos em que você deseja agrupar rotas relacionadas juntas (como `/api/v1`).
Você pode fazer isso usando o método `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Corresponde a /api/v1/users
  });

  Flight::route('/posts', function () {
	// Corresponde a /api/v1/posts
  });
});
```

Você também pode aninhar grupos de grupos:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtém variáveis, não define uma rota! Ver contexto do objeto abaixo
	Flight::route('GET /users', function () {
	  // Corresponde a GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Corresponde a POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Corresponde a PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtém variáveis, não define uma rota! Ver contexto do objeto abaixo
	Flight::route('GET /users', function () {
	  // Corresponde a GET /api/v2/users
	});
  });
});
```

### Agrupamento com Contexto de Objeto

Você ainda pode usar o agrupamento de rotas com o objeto `Engine` da seguinte forma:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// Corresponde a GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Corresponde a POST /api/v1/posts
  });
});
```

## Aliasing de Rota

Você pode atribuir um alias a uma rota, de modo que a URL possa ser gerada dinamicamente mais tarde em seu código (como um modelo, por exemplo).

```php
Flight::route('/users/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');

// mais tarde no código em algum lugar
Flight::getUrl('visualizacao_usuario', [ 'id' => 5 ]); // retornará '/usuários/5'
```

Isso é especialmente útil se sua URL mudar. No exemplo acima, digamos que os usuários foram movidos para `/admin/users/@id`. Com o alias em vigor, você não precisa mudar em nenhum lugar onde referencie o alias, pois o alias agora retornará `/admin/users/5` como no
exemplo acima.

O alias de rota ainda funciona em grupos também:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');
});


// mais tarde no código em algum lugar
Flight::getUrl('visualizacao_usuario', [ 'id' => 5 ]); // retornará '/usuários/5'
```

## Middleware de Rota
O Flight suporta middleware de rota e grupo de rotas. O middleware é uma função que é executada antes (ou depois) da função de retorno da rota. Esta é uma ótima maneira de adicionar verificações de autenticação de API em seu código ou verificar se o usuário tem permissão para acessar a rota.

Aqui está um exemplo básico:

```php
// Se você fornecer apenas uma função anônima, ela será executada antes da função de retorno da rota.
// não existem funções de middleware "depois" exceto para classes (veja abaixo)
Flight::route('/path', function() { echo ' Aqui estou!'; })->addMiddleware(function() {
	echo 'Middleware primeiro!';
});

Flight::start();

// Isso irá produzir "Middleware primeiro! Aqui estou!"
```

Existem algumas notas muito importantes sobre middleware que você deve estar ciente antes de usá-los:
- As funções de middleware são executadas na ordem em que são adicionadas à rota. A execução é semelhante à forma como o [Slim Framework lida com isso](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Os Befores são executados na ordem adicionada, e os Afters são executados na ordem inversa.
- Se sua função de middleware retornar false, toda a execução será interrompida e um erro 403 Forbidden será lançado. Provavelmente você vai querer lidar com isso de forma mais graciosa com um `Flight::redirect()` ou algo semelhante.
- Se você precisar de parâmetros de sua rota, eles serão passados em um único array para sua função de middleware. (`function($params) { ... }` ou `public function before($params) {}`). A razão para isso é que você pode estruturar seus parâmetros em grupos e em alguns daqueles grupos, seus parâmetros podem realmente aparecer em uma ordem diferente que quebraria a função de middleware ao se referir ao parâmetro errado. Dessa forma, você pode acessá-los pelo nome em vez de posição.

### Classes de Middleware

O middleware pode ser registrado como uma classe também. Se você precisar da funcionalidade "depois", você deve usar uma classe.

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
Flight::route('/path', function() { echo ' Aqui estou! '; })->addMiddleware($MeuMiddleware); // também ->addMiddleware([ $MeuMiddleware, $MeuMiddleware2 ]);

Flight::start();

// Isso exibirá "Middleware primeiro! Aqui estou! Middleware último!"
```

### Grupos de Middleware

Você pode adicionar um grupo de rota e, em seguida, cada rota nesse grupo terá o mesmo middleware também. Isso é útil se você precisar agrupar várias rotas, digamos, por um middleware Auth para verificar a chave da API no cabeçalho.

```php

// adicionado no final do método de grupo
Flight::group('/api', function() {
    Flight::route('/users', function() { echo 'usuários'; }, false, 'usuários');
	Flight::route('/users/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');
}, [ new MiddlewareAuthApi() ]);