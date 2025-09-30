# Estendendo

## Visão Geral

Flight é projetado para ser um framework extensível. O framework vem com um
conjunto de métodos e componentes padrão, mas permite que você mapeie seus próprios métodos,
registre suas próprias classes ou até substitua classes e métodos existentes.

## Compreendendo

Existem 2 maneiras de você estender a funcionalidade do Flight:

1. Mapeamento de Métodos - Isso é usado para criar métodos personalizados simples que você pode chamar
   de qualquer lugar em sua aplicação. Esses são tipicamente usados para funções utilitárias
   que você deseja poder chamar de qualquer lugar em seu código. 
2. Registrando Classes - Isso é usado para registrar suas próprias classes com o Flight. Isso é
   tipicamente usado para classes que têm dependências ou requerem configuração.

Você também pode substituir métodos existentes do framework para alterar seu comportamento padrão para melhor
atender às necessidades do seu projeto. 

> Se você está procurando um DIC (Container de Injeção de Dependência), pule para a
página [Container de Injeção de Dependência](/learn/dependency-injection-container).

## Uso Básico

### Substituindo Métodos do Framework

Flight permite que você substitua sua funcionalidade padrão para atender às suas próprias necessidades,
sem precisar modificar nenhum código. Você pode ver todos os métodos que pode substituir [abaixo](#mappable-framework-methods).

Por exemplo, quando o Flight não consegue corresponder uma URL a uma rota, ele invoca o método `notFound`
que envia uma resposta genérica `HTTP 404`. Você pode substituir esse comportamento
usando o método `map`:

```php
Flight::map('notFound', function() {
  // Exibe página 404 personalizada
  include 'errors/404.html';
});
```

Flight também permite que você substitua componentes principais do framework.
Por exemplo, você pode substituir a classe Router padrão pela sua própria classe personalizada:

```php
// crie sua classe Router personalizada
class MyRouter extends \flight\net\Router {
	// substitua métodos aqui
	// por exemplo, um atalho para requisições GET para remover
	// o recurso de passagem de rota
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// Registre sua classe personalizada
Flight::register('router', MyRouter::class);

// Quando o Flight carrega a instância do Router, ele carregará sua classe
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

Métodos do framework como `map` e `register`, no entanto, não podem ser substituídos. Você receberá
um erro se tentar fazer isso (novamente, veja [abaixo](#mappable-framework-methods) para uma lista de métodos).

### Métodos Mapeáveis do Framework

A seguir está o conjunto completo de métodos para o framework. Ele consiste em métodos principais, 
que são métodos estáticos regulares, e métodos extensíveis, que são métodos mapeados que podem 
ser filtrados ou substituídos.

#### Métodos Principais

Esses métodos são principais para o framework e não podem ser substituídos.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Cria um método personalizado do framework.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registra uma classe a um método do framework.
Flight::unregister(string $name) // Desregistra uma classe de um método do framework.
Flight::before(string $name, callable $callback) // Adiciona um filtro antes de um método do framework.
Flight::after(string $name, callable $callback) // Adiciona um filtro após um método do framework.
Flight::path(string $path) // Adiciona um caminho para carregamento automático de classes.
Flight::get(string $key) // Obtém uma variável definida por Flight::set().
Flight::set(string $key, mixed $value) // Define uma variável dentro do engine do Flight.
Flight::has(string $key) // Verifica se uma variável está definida.
Flight::clear(array|string $key = []) // Limpa uma variável.
Flight::init() // Inicializa o framework com suas configurações padrão.
Flight::app() // Obtém a instância do objeto de aplicação
Flight::request() // Obtém a instância do objeto de requisição
Flight::response() // Obtém a instância do objeto de resposta
Flight::router() // Obtém a instância do objeto de roteador
Flight::view() // Obtém a instância do objeto de visualização
```

#### Métodos Extensíveis

```php
Flight::start() // Inicia o framework.
Flight::stop() // Para o framework e envia uma resposta.
Flight::halt(int $code = 200, string $message = '') // Para o framework com um código de status e mensagem opcionais.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL para um callback.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de requisição POST para um callback.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de requisição PUT para um callback.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de requisição PATCH para um callback.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de requisição DELETE para um callback.
Flight::group(string $pattern, callable $callback) // Cria agrupamento para URLs, o padrão deve ser uma string.
Flight::getUrl(string $name, array $params = []) // Gera uma URL baseada em um alias de rota.
Flight::redirect(string $url, int $code) // Redireciona para outra URL.
Flight::download(string $filePath) // Baixa um arquivo.
Flight::render(string $file, array $data, ?string $key = null) // Renderiza um arquivo de template.
Flight::error(Throwable $error) // Envia uma resposta HTTP 500.
Flight::notFound() // Envia uma resposta HTTP 404.
Flight::etag(string $id, string $type = 'string') // Realiza cache HTTP ETag.
Flight::lastModified(int $time) // Realiza cache HTTP de última modificação.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSONP.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSON e para o framework.
Flight::onEvent(string $event, callable $callback) // Registra um ouvinte de evento.
Flight::triggerEvent(string $event, ...$args) // Dispara um evento.
```

Quaisquer métodos personalizados adicionados com `map` e `register` também podem ser filtrados. Para exemplos de como filtrar esses métodos, veja o guia [Filtrando Métodos](/learn/filtering).

#### Classes Extensíveis do Framework

Existem várias classes que você pode substituir a funcionalidade estendendo-as e
registrando sua própria classe. Essas classes são:

```php
Flight::app() // Classe de Aplicação - estenda a classe flight\Engine
Flight::request() // Classe de Requisição - estenda a classe flight\net\Request
Flight::response() // Classe de Resposta - estenda a classe flight\net\Response
Flight::router() // Classe de Roteador - estenda a classe flight\net\Router
Flight::view() // Classe de Visualização - estenda a classe flight\template\View
Flight::eventDispatcher() // Classe de Dispatcher de Eventos - estenda a classe flight\core\Dispatcher
```

### Mapeando Métodos Personalizados

Para mapear seu próprio método personalizado simples, você usa a função `map`:

```php
// Mapeie seu método
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Chame seu método personalizado
Flight::hello('Bob');
```

Embora seja possível criar métodos personalizados simples, é recomendado apenas criar
funções padrão em PHP. Isso tem autocompletar em IDEs e é mais fácil de ler.
O equivalente do código acima seria:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

Isso é usado mais quando você precisa passar variáveis para o seu método para obter um
valor esperado. Usar o método `register()` como abaixo é mais para passar configuração
e depois chamar sua classe pré-configurada.

### Registrando Classes Personalizadas

Para registrar sua própria classe e configurá-la, você usa a função `register`. A vantagem que isso tem sobre map() é que você pode reutilizar a mesma classe quando chamar essa função (seria útil com `Flight::db()` para compartilhar a mesma instância).

```php
// Registre sua classe
Flight::register('user', User::class);

// Obtenha uma instância da sua classe
$user = Flight::user();
```

O método register também permite que você passe parâmetros para o
construtor da sua classe. Então, quando você carrega sua classe personalizada, ela virá pré-inicializada.
Você pode definir os parâmetros do construtor passando um array adicional.
Aqui está um exemplo de carregamento de uma conexão de banco de dados:

```php
// Registre a classe com parâmetros do construtor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtenha uma instância da sua classe
// Isso criará um objeto com os parâmetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// e se você precisar dela mais tarde no seu código, você apenas chama o mesmo método novamente
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Se você passar um parâmetro de callback adicional, ele será executado imediatamente
após a construção da classe. Isso permite que você execute qualquer procedimento de configuração para o seu
novo objeto. A função de callback recebe um parâmetro, uma instância do novo objeto.

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

Por padrão, toda vez que você carrega sua classe, você obterá uma instância compartilhada.
Para obter uma nova instância de uma classe, simplesmente passe `false` como parâmetro:

```php
// Instância compartilhada da classe
$shared = Flight::db();

// Nova instância da classe
$new = Flight::db(false);
```

> **Nota:** Lembre-se de que os métodos mapeados têm precedência sobre as classes registradas. Se você
declarar ambos usando o mesmo nome, apenas o método mapeado será invocado.

### Exemplos

Aqui estão alguns exemplos de como você pode estender o Flight com funcionalidades que não estão integradas no núcleo.

#### Logging

Flight não tem um sistema de logging integrado, no entanto, é realmente fácil
usar uma biblioteca de logging com o Flight. Aqui está um exemplo usando a
biblioteca Monolog:

```php
// services.php

// Registre o logger com o Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Agora que está registrado, você pode usá-lo em sua aplicação:

```php
// Em seu controlador ou rota
Flight::log()->warning('This is a warning message');
```

Isso registrará uma mensagem no arquivo de log que você especificou. E se você quiser registrar algo quando um
erro ocorrer? Você pode usar o método `error`:

```php
// Em seu controlador ou rota
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Exiba sua página de erro personalizada
	include 'errors/500.html';
});
```

Você também poderia criar um sistema básico de APM (Monitoramento de Desempenho de Aplicação)
usando os métodos `before` e `after`:

```php
// Em seu arquivo services.php

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// Você também poderia adicionar os cabeçalhos de requisição ou resposta
	// para registrá-los também (tenha cuidado, pois isso seria um 
	// monte de dados se você tiver muitas requisições)
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### Cache

Flight não tem um sistema de cache integrado, no entanto, é realmente fácil
usar uma biblioteca de cache com o Flight. Aqui está um exemplo usando a
[PHP File Cache](/awesome-plugins/php_file_cache) biblioteca:

```php
// services.php

// Registre o cache com o Flight
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

Agora que está registrado, você pode usá-lo em sua aplicação:

```php
// Em seu controlador ou rota
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// Faça algum processamento para obter os dados
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // cache por 1 hora
}
```

#### Instanciação Fácil de Objetos DIC

Se você está usando um DIC (Container de Injeção de Dependência) em sua aplicação,
você pode usar o Flight para ajudá-lo a instanciar seus objetos. Aqui está um exemplo usando
a biblioteca [Dice](https://github.com/level-2/Dice):

```php
// services.php

// crie um novo container
$container = new \Dice\Dice;
// não esqueça de reatribuí-lo a si mesmo como abaixo!
$container = $container->addRule('PDO', [
	// shared significa que o mesmo objeto será retornado a cada vez
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// agora podemos criar um método mapeável para criar qualquer objeto. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// Isso registra o manipulador de container para que o Flight saiba usá-lo para controladores/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// digamos que tenhamos a seguinte classe de exemplo que recebe um objeto PDO no construtor
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// código que envia um email
	}
}

// E finalmente você pode criar objetos usando injeção de dependência
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

Legal, né?

## Veja Também
- [Container de Injeção de Dependência](/learn/dependency-injection-container) - Como usar um DIC com o Flight.
- [File Cache](/awesome-plugins/php_file_cache) - Exemplo de uso de uma biblioteca de cache com o Flight.

## Solução de Problemas
- Lembre-se de que os métodos mapeados têm precedência sobre as classes registradas. Se você
declarar ambos usando o mesmo nome, apenas o método mapeado será invocado.

## Changelog
- v2.0 - Lançamento Inicial.