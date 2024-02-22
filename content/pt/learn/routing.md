# Roteamento

> **Nota:** Quer entender mais sobre roteamento? Confira a página ["por que um framework?"](/learn/why-frameworks) para uma explicação mais aprofundada.

O roteamento básico no `Flight` é feito combinando um padrão de URL com uma função de retorno ou uma matriz de uma classe e método.

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

// Saudacao.php
class Saudacao
{
    public function __construct() {
        $this->nome = 'Fulano de Tal';
    }

    public function hello() {
        echo "Olá, {$this->nome}!";
    }
}

// index.php
$saudacao = new Saudacao();

Flight::route('/', array($saudacao, 'hello'));
```

Os roteamentos são correspondidos na ordem em que são definidos. O primeiro roteamento a corresponder a uma solicitação será invocado.

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

Você também pode mapear vários métodos para um único retorno usando um delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recebi uma solicitação GET ou POST.';
});
```

Além disso, você pode obter o objeto Roteador que possui alguns métodos auxiliares para você usar:

```php

$roteador = Flight::router();

// mapeia todos os métodos
$roteador->map('/', function() {
	echo 'olá mundo!';
});

// solicitação GET
$roteador->get('/usuarios', function() {
	echo 'usuários';
});
// $roteador->post();
// $roteador->put();
// $roteador->delete();
// $roteador->patch();
```

## Expressões Regulares

Você pode usar expressões regulares em suas rotas:

```php
Flight::route('/usuario/[0-9]+', function () {
  // Isso corresponderá a /usuario/1234
});
```

Embora esse método esteja disponível, é recomendado usar parâmetros nomeados ou
parâmetros nomeados com expressões regulares, pois são mais legíveis e mais fáceis de manter.

## Parâmetros Nomeados

Você pode especificar parâmetros nomeados em suas rotas que serão repassados para
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

> **Nota:** Oparemeters opcionais

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

Quaisquer parâmetros opcionais que não forem correspondidos serão passados como `NULL`.

## Cure-lha

A correspondência é feita apenas em segmentos individuais de URL. Se desejar corresponder a múltiplos
segmentos, você pode usar um cure-lha `*`.

```php
Flight::route('/blog/*', function () {
  // Isso corresponderá a /blog/2000/02/01
});
```

Para encaminhar todas as solicitações para um único retorno, você pode fazer:

```php
Flight::route('*', function () {
  // Faça algo
});
```

## Passando

Você pode passar a execução para o próximo roteamento correspondente retornando `true` de
sua função de retorno.

```php
Flight::route('/usuario/@nome', function (string $nome) {
  // Verifique alguma condição
  if ($nome !== "Bob") {
    // Continue para o próximo roteamento
    return true;
  }
});

Flight::route('/usuario/*', function () {
  // Isso será chamado
});
```

## Alias de Rota

Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente mais tarde em seu código (como um modelo, por exemplo).

```php
Flight::route('/usuarios/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');

// depois em algum lugar do código
Flight::getUrl('visualizacao_usuario', [ 'id' => 5 ]); // retornará '/usuarios/5'
```

Isso é especialmente útil se sua URL mudar. No exemplo acima, digamos que `usuarios` tenha sido movido para `/admin/usuarios/@id` em vez disso.
Com o uso de alias, você não precisa alterar em nenhum lugar que faça referência ao alias, pois o alias agora retornará `/admin/usuarios/5` como no
exemplo acima.

O alias de rota também funciona em grupos:

```php
Flight::group('/usuarios', function() {
    Flight::route('/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');
});

// depois em algum lugar do código
Flight::getUrl('visualizacao_usuario', [ 'id' => 5 ]); // retornará '/usuarios/5'
```

## Informações de Rota

Se desejar inspecionar as informações de rota correspondente, você pode solicitar que o objeto rota
seja passado para sua função de retorno passando `true` como terceiro parâmetro no
método de roteamento. O objeto rota sempre será o último parâmetro passado para sua
função de retorno.

```php
Flight::route('/', function(\flight\net\Route $rota) {
  // Array de métodos HTTP correspondidos
  $rota->methods;

  // Array de parâmetros nomeados
  $rota->params;

  // Expressão regular correspondente
  $rota->regex;

  // Contém o conteúdo de qualquer '*' utilizado no padrão de URL
  $rota->splat;

  // Mostra o caminho URL....se você realmente precisar dele
  $rota->pattern;

  // Mostra que middleware está atribuído a isso
  $rota->middleware;

  // Mostra o alias atribuído a esta rota
  $rota->alias;
}, true);
```

## Agrupamento de Rota

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

Você também pode aninhar grupos de grupos:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtém variáveis, não define um roteamento! Consulte o contexto do objeto abaixo
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

	// Flight::get() obtém variáveis, não define um roteamento! Consulte o contexto do objeto abaixo
	Flight::route('GET /usuarios', function () {
	  // Corresponde a GET /api/v2/usuarios
	});
  });
});
```

### Agrupamento com Contexto de Objeto

Ainda é possível usar o agrupamento de rotas com o objeto `Engine` da seguinte forma:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $roteador) {

  // use a variável $roteador
  $roteador->get('/usuarios', function () {
	// Corresponde a GET /api/v1/usuarios
  });

  $roteador->post('/posts', function () {
	// Corresponde a POST /api/v1/posts
  });
});
```

## Streaming

Agora você pode enviar respostas ao cliente usando o método `streamWithHeaders()`.
Isso é útil para enviar arquivos grandes, processos longos ou gerar respostas grandes.
O streaming de uma rota é tratado um pouco diferente de uma rota regular.

> **Nota:** O envio de respostas em fluxo está disponível apenas se você tiver [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) definido como false.

```php
Flight::route('/stream-usuarios', function() {

	// como você obtém seus dados, apenas como exemplo...
	$usuarios_stmt = Flight::db()->query("SELECT id, primeiro_nome, sobrenome FROM usuarios");

	echo '{';
	$user_count = count($usuarios);
	while($usuario = $usuarios_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($usuario);
		if(--$contador_usuario > 0) {
			echo ',';
		}

		// Isso é necessário para enviar os dados ao cliente
		ob_flush();
	}
	echo '}';

// Aqui está como você configurará os cabeçalhos antes de começar o streaming.
})->streamWithHeaders([
	'Tipo de Conteúdo' => 'application/json',
	// código de status opcional, padrão para 200
	'status' => 200
]);
```