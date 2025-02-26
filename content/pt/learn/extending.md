# Estendendo

Flight foi projetado para ser um framework extensível. O framework vem com um conjunto de métodos e componentes padrão, mas permite que você mapeie seus próprios métodos, registre suas próprias classes ou até mesmo substitua classes e métodos existentes.

Se você está procurando um DIC (Contêiner de Injeção de Dependência), vá para a página [Contêiner de Injeção de Dependência](dependency-injection-container).

## Mapeando Métodos

Para mapear seu próprio método customizado simples, você usa a função `map`:

```php
// Mapeie seu método
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Chame seu método customizado
Flight::hello('Bob');
```

Embora seja possível criar métodos customizados simples, é recomendado apenas criar funções padrão em PHP. Isso tem autocompletar em IDEs e é mais fácil de ler. O equivalente do código acima seria:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

Isso é mais utilizado quando você precisa passar variáveis para o seu método para obter um valor esperado. Usar o método `register()` como abaixo é mais para passar configurações e então chamar sua classe pré-configurada.

## Registrando Classes

Para registrar sua própria classe e configurá-la, você usa a função `register`:

```php
// Registre sua classe
Flight::register('user', User::class);

// Obtenha uma instância da sua classe
$user = Flight::user();
```

O método de registro também permite que você passe parâmetros para o construtor da sua classe. Assim, quando você carrega sua classe customizada, ela virá pré-inicializada. Você pode definir os parâmetros do construtor passando um array adicional. Aqui está um exemplo de carregamento de uma conexão de banco de dados:

```php
// Registre a classe com parâmetros de construtor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtenha uma instância da sua classe
// Isso criará um objeto com os parâmetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// e se você precisar dele mais tarde em seu código, você só precisa chamar o mesmo método novamente
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Se você passar um parâmetro de callback adicional, ele será executado imediatamente após a construção da classe. Isso permite que você execute qualquer procedimento de configuração para o seu novo objeto. A função de callback recebe um parâmetro, uma instância do novo objeto.

```php
// O callback receberá o objeto que foi construído
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por padrão, toda vez que você carregar sua classe, você receberá uma instância compartilhada. Para obter uma nova instância de uma classe, basta passar `false` como parâmetro:

```php
// Instância compartilhada da classe
$shared = Flight::db();

// Nova instância da classe
$new = Flight::db(false);
```

Tenha em mente que métodos mapeados têm precedência sobre classes registradas. Se você declarar ambos usando o mesmo nome, apenas o método mapeado será invocado.

## Registro

Flight não possui um sistema de registro embutido, no entanto, é muito fácil usar uma biblioteca de registro com o Flight. Aqui está um exemplo usando a biblioteca Monolog:

```php
// index.php ou bootstrap.php

// Registre o logger com o Flight
Flight::register('log', Monolog\Logger::class, ['name'], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Agora que está registrado, você pode usá-lo em sua aplicação:

```php
// No seu controlador ou rota
Flight::log()->warning('Esta é uma mensagem de aviso');
```

Isso registrará uma mensagem no arquivo de log que você especificou. E se você quiser registrar algo quando um erro ocorrer? Você pode usar o método `error`:

```php
// No seu controlador ou rota

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Exiba sua página de erro customizada
	include 'errors/500.html';
});
```

Você também poderia criar um sistema básico de APM (Monitoramento de Performance de Aplicação) usando os métodos `before` e `after`:

```php
// No seu arquivo de bootstrap

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' levou ' . round($end - $start, 4) . ' segundos');

	// Você também poderia adicionar seus cabeçalhos de requisição ou resposta
	// para registrá-los também (cuidado, pois isso geraria uma
	// grande quantidade de dados se você tiver muitas requisições)
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

## Substituindo Métodos do Framework

Flight permite que você substitua sua funcionalidade padrão para atender às suas próprias necessidades, sem precisar modificar nenhum código. Você pode visualizar todos os métodos que pode substituir [aqui](/learn/api).

Por exemplo, quando o Flight não consegue corresponder uma URL a uma rota, ele invoca o método `notFound` que envia uma resposta genérica `HTTP 404`. Você pode substituir esse comportamento usando o método `map`:

```php
Flight::map('notFound', function() {
  // Exiba a página 404 customizada
  include 'errors/404.html';
});
```

O Flight também permite que você substitua componentes principais do framework. Por exemplo, você pode substituir a classe Router padrão pela sua própria classe customizada:

```php
// Registre sua classe customizada
Flight::register('router', MyRouter::class);

// Quando o Flight carrega a instância do Router, ele carregará sua classe
$myrouter = Flight::router();
```

No entanto, métodos do framework como `map` e `register` não podem ser substituídos. Você receberá um erro se tentar fazê-lo.