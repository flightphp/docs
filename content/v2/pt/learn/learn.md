# Aprender

Esta página é um guia para aprender Flight. Cobre os fundamentos do framework e como usá-lo.

## <a name="routing"></a> Roteamento

O roteamento no Flight é feito combinando um padrão de URL com uma função de retorno de chamada.

``` php
Flight::route('/', function(){
    echo 'Olá, mundo!';
});
```

A função de retorno de chamada pode ser qualquer objeto que seja chamado. Então você pode usar uma função regular:

``` php
function hello(){
    echo 'Olá, mundo!';
}

Flight::route('/', 'hello');
```

Ou um método de classe:

``` php
class Greeting {
    public static function hello() {
        echo 'Olá, mundo!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

Ou um método de objeto:

``` php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Olá, {$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

As rotas são combinadas na ordem em que são definidas. A primeira rota a combinar com uma solicitação será invocada.

### Roteamento por Método

Por padrão, os padrões de rota são combinados com todos os métodos de solicitação. Você pode responder a métodos específicos colocando um identificador antes da URL.

``` php
Flight::route('GET /', function(){
    echo 'Recebi uma solicitação GET.';
});

Flight::route('POST /', function(){
    echo 'Recebi uma solicitação POST.';
});
```

Você também pode mapear múltiplos métodos para uma única função de retorno de chamada usando um delimitador `|`:

``` php
Flight::route('GET|POST /', function(){
    echo 'Recebi uma solicitação GET ou POST.';
});
```

### Expressões Regulares

Você pode usar expressões regulares em suas rotas:

``` php
Flight::route('/user/[0-9]+', function(){
    // Isso combinará /user/1234
});
```

### Parâmetros Nomeados

Você pode especificar parâmetros nomeados em suas rotas que serão passados para a sua função de retorno de chamada.

``` php
Flight::route('/@name/@id', function($name, $id){
    echo "Olá, $name ($id)!";
});
```

Você também pode incluir expressões regulares com seus parâmetros nomeados usando o delimitador `:`:

``` php
Flight::route('/@name/@id:[0-9]{3}', function($name, $id){
    // Isso combinará /bob/123
    // Mas não combinará /bob/12345
});
```

### Parâmetros Opcionais

Você pode especificar parâmetros nomeados que são opcionais para a correspondência envolvendo segmentos entre parênteses.

``` php
Flight::route('/blog(/@year(/@month(/@day)))', function($year, $month, $day){
    // Isso combinará os seguintes URLs:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

Quaisquer parâmetros opcionais que não forem correspondidos serão passados como NULL.

### Wilcards

A correspondência é feita somente em segmentos de URL individuais. Se você quiser combinar múltiplos segmentos, pode usar o wildcard `*`.

``` php
Flight::route('/blog/*', function(){
    // Isso combinará /blog/2000/02/01
});
```

Para direcionar todas as solicitações a uma única função de retorno de chamada, você pode fazer:

``` php
Flight::route('*', function(){
    // Fazer algo
});
```

### Passagem

Você pode passar a execução para a próxima rota correspondente retornando `true` da sua função de retorno de chamada.

``` php
Flight::route('/user/@name', function($name){
    // Verificar alguma condição
    if ($name != "Bob") {
        // Continuar para a próxima rota
        return true;
    }
});

Flight::route('/user/*', function(){
    // Isso será chamado
});
```

### Informações de Rota

Se você quiser inspecionar as informações da rota correspondente, pode solicitar que o objeto da rota seja passado para sua função de retorno de chamada passando `true` como o terceiro parâmetro no método de rota. O objeto da rota sempre será o último parâmetro passado para sua função de retorno de chamada.

``` php
Flight::route('/', function($route){
    // Array de métodos HTTP correspondidos
    $route->methods;

    // Array de parâmetros nomeados
    $route->params;

    // Expressão regular correspondente
    $route->regex;

    // Contém o conteúdo de qualquer '*' usado no padrão da URL
    $route->splat;
}, true);
```
### Agrupamento de Rotas

Pode haver momentos em que você queira agrupar rotas relacionadas (como `/api/v1`).
Você pode fazer isso usando o método `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Combina /api/v1/users
  });

  Flight::route('/posts', function () {
	// Combina /api/v1/posts
  });
});
```

Você pode até aninhar grupos de grupos:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtém variáveis, não define uma rota! Veja o contexto de objeto abaixo
	Flight::route('GET /users', function () {
	  // Combina GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Combina POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Combina PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtém variáveis, não define uma rota! Veja o contexto de objeto abaixo
	Flight::route('GET /users', function () {
	  // Combina GET /api/v2/users
	});
  });
});
```

#### Agrupamento com Contexto de Objeto

Você ainda pode usar o agrupamento de rotas com o objeto `Engine` da seguinte maneira:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// Combina GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Combina POST /api/v1/posts
  });
});
```

### Alias de Rota

Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente mais tarde em seu código (como um template, por exemplo).

```php
Flight::route('/users/@id', function($id) { echo 'usuário:'.$id; }, false, 'user_view');

// mais tarde em algum lugar do código
Flight::getUrl('user_view', [ 'id' => 5 ]); // retornará '/users/5'
```

Isso é especialmente útil se sua URL mudar. No exemplo acima, digamos que os usuários foram movidos para `/admin/users/@id` em vez disso.
Com o alias em vigor, você não precisa mudar em nenhum lugar onde você faz referência ao alias, porque o alias agora retornará `/admin/users/5`, como no
exemplo acima.

A aliasação de rota ainda funciona em grupos também:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'usuário:'.$id; }, false, 'user_view');
});

// mais tarde em algum lugar do código
Flight::getUrl('user_view', [ 'id' => 5 ]); // retornará '/users/5'
```

## <a name="extending"></a> Estendendo

Flight foi projetado para ser um framework extensível. O framework vem com um conjunto
de métodos e componentes padrão, mas permite que você mapeie seus próprios métodos,
registre suas próprias classes ou até mesmo sobrescreva classes e métodos existentes.

### Mapeamento de Métodos

Para mapear seu próprio método personalizado, você usa a função `map`:

``` php
// Mapeie seu método
Flight::map('hello', function($name){
    echo "Olá $name!";
});

// Chame seu método personalizado
Flight::hello('Bob');
```

### Registro de Classes

Para registrar sua própria classe, você usa a função `register`:

``` php
// Registre sua classe
Flight::register('user', 'User');

// Obtenha uma instância da sua classe
$user = Flight::user();
```

O método de registro também permite que você passe parâmetros para o construtor da sua classe.
Assim, quando você carrega sua classe personalizada, ela virá pré-inicializada.
Você pode definir os parâmetros do construtor passando um array adicional.
Aqui está um exemplo de carregar uma conexão de banco de dados:

``` php
// Registre a classe com parâmetros do construtor
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'));

// Obtenha uma instância da sua classe
// Isso criará um objeto com os parâmetros definidos
//
//     new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Se você passar um parâmetro de callback adicional, ele será executado imediatamente
após a construção da classe. Isso permite que você execute qualquer procedimento de configuração para o seu
novo objeto. A função de retorno de chamada recebe um parâmetro, uma instância do novo objeto.

``` php
// A função de retorno de chamada receberá o objeto que foi construído
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'),
  function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por padrão, toda vez que você carrega sua classe, receberá uma instância compartilhada.
Para obter uma nova instância de uma classe, simplesmente passe `false` como um parâmetro:

``` php
// Instância compartilhada da classe
$shared = Flight::db();

// Nova instância da classe
$new = Flight::db(false);
```

Lembre-se de que os métodos mapeados têm precedência sobre as classes registradas. Se você
declara ambos usando o mesmo nome, apenas o método mapeado será invocado.

## <a name="overriding"></a> Sobrescrevendo

Flight permite que você sobrescreva sua funcionalidade padrão para atender às suas próprias necessidades,
sem ter que modificar nenhum código.

Por exemplo, quando o Flight não consegue combinar uma URL com uma rota, ele invoca o método `notFound`
que envia uma resposta genérica `HTTP 404`. Você pode sobrescrever esse comportamento
usando o método `map`:

``` php
Flight::map('notFound', function(){
    // Exibir página 404 personalizada
    include 'errors/404.html';
});
```

Flight também permite que você substitua componentes principais do framework.
Por exemplo, você pode substituir a classe Router padrão por sua própria classe personalizada:

``` php
// Registre sua classe personalizada
Flight::register('router', 'MyRouter');

// Quando o Flight carrega a instância do Router, ele carregará sua classe
$myrouter = Flight::router();
```

Métodos do framework como `map` e `register` no entanto não podem ser sobrescritos. Você
receberá um erro se tentar fazer isso.

## <a name="filtering"></a> Filtrando

Flight permite que você filtre métodos antes e depois que eles sejam chamados. Não há
ganchos predefinidos que você precisa memorizar. Você pode filtrar qualquer um dos métodos padrão do framework
assim como quaisquer métodos personalizados que você tenha mapeado.

Uma função de filtro se parece com isso:

``` php
function(&$params, &$output) {
    // Código de filtro
}
```

Usando as variáveis passadas, você pode manipular os parâmetros de entrada e/ou a saída.

Você pode ter um filtro rodando antes de um método fazendo:

``` php
Flight::before('start', function(&$params, &$output){
    // Fazer algo
});
```

Você pode ter um filtro rodando depois de um método fazendo:

``` php
Flight::after('start', function(&$params, &$output){
    // Fazer algo
});
```

Você pode adicionar quantos filtros quiser a qualquer método. Eles serão chamados na ordem em que foram declarados.

Aqui está um exemplo do processo de filtragem:

``` php
// Mapeie um método personalizado
Flight::map('hello', function($name){
    return "Olá, $name!";
});

// Adicione um filtro antes
Flight::before('hello', function(&$params, &$output){
    // Manipule o parâmetro
    $params[0] = 'Fred';
});

// Adicione um filtro depois
Flight::after('hello', function(&$params, &$output){
    // Manipule a saída
    $output .= " Tenha um bom dia!";
});

// Invocar o método personalizado
echo Flight::hello('Bob');
```

Isso deve exibir:

``` html
Olá Fred! Tenha um bom dia!
```

Se você definiu múltiplos filtros, pode interromper a cadeia retornando `false`
em qualquer uma das suas funções de filtro:

``` php
Flight::before('start', function(&$params, &$output){
    echo 'um';
});

Flight::before('start', function(&$params, &$output){
    echo 'dois';

    // Isso interromperá a cadeia
    return false;
});

// Isso não será chamado
Flight::before('start', function(&$params, &$output){
    echo 'três';
});
```

Nota: métodos principais como `map` e `register` não podem ser filtrados porque são
chamados diretamente e não invocados dinamicamente.

## <a name="variables"></a> Variáveis

Flight permite que você salve variáveis para que possam ser usadas em qualquer lugar de sua aplicação.

``` php
// Salve sua variável
Flight::set('id', 123);

// Em outro lugar da sua aplicação
$id = Flight::get('id');
```

Para ver se uma variável foi definida, você pode fazer:

``` php
if (Flight::has('id')) {
     // Fazer algo
}
```

Você pode limpar uma variável fazendo:

``` php
// Limpa a variável id
Flight::clear('id');

// Limpa todas as variáveis
Flight::clear();
```

Flight também usa variáveis para fins de configuração.

``` php
Flight::set('flight.log_errors', true);
```

## <a name="views"></a> Visões

Flight fornece algumas funcionalidades de template básicas por padrão. Para exibir um modelo de visão, chame o método `render` com o nome do arquivo de modelo e dados de modelo opcionais:

``` php
Flight::render('hello.php', array('name' => 'Bob'));
```

Os dados do modelo que você passa são automaticamente injetados no modelo e podem ser referenciados como uma variável local. Os arquivos de modelo são simplesmente arquivos PHP. Se o conteúdo do arquivo de modelo `hello.php` for:

``` php
Olá, '<?php echo $name; ?>'!
```

A saída seria:

``` html
Olá, Bob!
```

Você também pode definir manualmente variáveis de visão usando o método set:

``` php
Flight::view()->set('name', 'Bob');
```

A variável `name` agora está disponível em todas as suas visões. Portanto, você pode simplesmente fazer:

``` php
Flight::render('hello');
```

Note que ao especificar o nome do modelo no método render, você pode deixar de fora a extensão `.php`.

Por padrão, o Flight procurará um diretório `views` para arquivos de modelo. Você pode definir um caminho alternativo para seus modelos configurando o seguinte:

``` php
Flight::set('flight.views.path', '/caminho/para/views');
```

### Layouts

É comum que os sites tenham um único arquivo de layout de modelo com conteúdo intercambiável. Para renderizar conteúdo a ser usado em um layout, você pode passar um parâmetro opcional para o método `render`.

``` php
Flight::render('header', array('heading' => 'Olá'), 'header_content');
Flight::render('body', array('body' => 'Mundo'), 'body_content');
```

Sua visão terá então variáveis salvas chamadas `header_content` e `body_content`.
Você pode então renderizar seu layout fazendo:

``` php
Flight::render('layout', array('title' => 'Página Inicial'));
```

Se os arquivos de modelo se parecerem com isto:

`header.php`:

``` php
<h1><?php echo $heading; ?></h1>
```

`body.php`:

``` php
<div><?php echo $body; ?></div>
```

`layout.php`:

``` php
<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php echo $header_content; ?>
<?php echo $body_content; ?>
</body>
</html>
```

A saída seria:

``` html
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

### Visões Personalizadas

Flight permite que você substitua o mecanismo de visualização padrão simplesmente registrando sua própria classe de visão. Aqui está como você usaria o [Smarty](http://www.smarty.net/)
mecanismo de template para suas visões:

``` php
// Carregue a biblioteca Smarty
require './Smarty/libs/Smarty.class.php';

// Registre o Smarty como a classe de visão
// Também passe uma função de retorno de chamada para configurar o Smarty ao carregar
Flight::register('view', 'Smarty', array(), function($smarty){
    $smarty->template_dir = './templates/';
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';
});

// Atribua dados de template
Flight::view()->assign('name', 'Bob');

// Exiba o template
Flight::view()->display('hello.tpl');
```

Para completar, você também deve sobrescrever o método render padrão do Flight:

``` php
Flight::map('render', function($template, $data){
    Flight::view()->assign($data);
    Flight::view()->display($template);
});
```

## <a name="errorhandling"></a> Tratamento de Erros

### Erros e Exceções

Todos os erros e exceções são capturados pelo Flight e passados para o método `error`.
O comportamento padrão é enviar uma resposta genérica `HTTP 500 Internal Server Error`
com algumas informações sobre o erro.

Você pode sobrescrever esse comportamento para suas próprias necessidades:

``` php
Flight::map('error', function(Exception $ex){
    // Tratar erro
    echo $ex->getTraceAsString();
});
```

Por padrão, os erros não são registrados no servidor web. Você pode habilitar isso mudando a configuração:

``` php
Flight::set('flight.log_errors', true);
```

### Não Encontrado

Quando uma URL não pode ser encontrada, o Flight chama o método `notFound`. O comportamento padrão é enviar uma resposta `HTTP 404 Not Found` com uma mensagem simples.

Você pode sobrescrever esse comportamento para suas próprias necessidades:

``` php
Flight::map('notFound', function(){
    // Tratar não encontrado
});
```

## <a name="redirects"></a> Redirecionamentos

Você pode redirecionar a solicitação atual usando o método `redirect` e passando
uma nova URL:

``` php
Flight::redirect('/nova/localizacao');
```

Por padrão, o Flight envia um código de status HTTP 303. Você pode opcionalmente definir um
código personalizado:

``` php
Flight::redirect('/nova/localizacao', 401);
```

## <a name="requests"></a> Solicitações

Flight encapsula a solicitação HTTP em um único objeto, que pode ser
acessado fazendo:

``` php
$request = Flight::request();
```

O objeto de solicitação fornece as seguintes propriedades:

``` html
url - A URL sendo solicitada
base - O subdiretório pai da URL
method - O método de solicitação (GET, POST, PUT, DELETE)
referrer - A URL de referência
ip - Endereço IP do cliente
ajax - Se a solicitação é uma solicitação AJAX
scheme - O protocolo do servidor (http, https)
user_agent - Informações do navegador
type - O tipo de conteúdo
length - O comprimento do conteúdo
query - Parâmetros da string de consulta
data - Dados post ou dados JSON
cookies - Dados de cookies
files - Arquivos enviados
secure - Se a conexão é segura
accept - Parâmetros de aceitação HTTP
proxy_ip - Endereço IP do proxy do cliente
```

Você pode acessar as propriedades `query`, `data`, `cookies` e `files`
como arrays ou objetos.

Portanto, para obter um parâmetro da string de consulta, você pode fazer:

``` php
$id = Flight::request()->query['id'];
```

Ou você pode fazer:

``` php
$id = Flight::request()->query->id;
```

### Corpo da Solicitação RAW

Para obter o corpo da solicitação HTTP raw, por exemplo, ao lidar com solicitações PUT, você pode fazer:

``` php
$body = Flight::request()->getBody();
```

### Entrada JSON

Se você enviar uma solicitação com o tipo `application/json` e os dados `{"id": 123}`, estará disponível
a partir da propriedade `data`:

``` php
$id = Flight::request()->data->id;
```

## <a name="stopping"></a> Parando

Você pode parar o framework a qualquer momento chamando o método `halt`:

``` php
Flight::halt();
```

Você também pode especificar um código de status `HTTP` opcional e uma mensagem:

``` php
Flight::halt(200, 'Volte logo...');
```

Chamar `halt` descartará qualquer conteúdo de resposta até aquele ponto. Se você quiser parar
o framework e apresentar a resposta atual, use o método `stop`:

``` php
Flight::stop();
```

## <a name="httpcaching"></a> Cache HTTP

Flight fornece suporte embutido para caching a nível HTTP. Se a condição de caching
for atendida, o Flight retornará uma resposta HTTP `304 Not Modified`. Da próxima vez que o
cliente solicitar o mesmo recurso, ele será solicitado a usar sua versão em cache local.

### Última Modificação

Você pode usar o método `lastModified` e passar um timestamp UNIX para definir a data
e hora em que uma página foi modificada pela última vez. O cliente continuará a usar seu cache até
que o valor da última modificação seja alterado.

``` php
Flight::route('/news', function(){
    Flight::lastModified(1234567890);
    echo 'Este conteúdo será armazenado em cache.';
});
```

### ETag

A cache `ETag` é semelhante à `Última Modificação`, exceto que você pode especificar qualquer ID que
quiser para o recurso:

``` php
Flight::route('/news', function(){
    Flight::etag('meu-id-unico');
    echo 'Este conteúdo será armazenado em cache.';
});
```

Lembre-se de que chamar `lastModified` ou `etag` tanto define quanto verifica o
valor do cache. Se o valor do cache for o mesmo entre as solicitações, o Flight enviará imediatamente
uma resposta `HTTP 304` e interromperá o processamento.

## <a name="json"></a> JSON

Flight oferece suporte para enviar respostas JSON e JSONP. Para enviar uma resposta JSON você
passa alguns dados para serem codificados em JSON:

``` php
Flight::json(array('id' => 123));
```

Para solicitações JSONP, você pode opcionalmente passar o nome do parâmetro de consulta que está
usando para definir sua função de retorno de chamada:

``` php
Flight::jsonp(array('id' => 123), 'q');
```

Assim, ao fazer uma solicitação GET usando `?q=my_func`, você deve receber a saída:

``` json
my_func({"id":123});
```

Se você não passar um nome de parâmetro de consulta, ele será definido como `jsonp`.

## <a name="configuration"></a> Configuração

Você pode personalizar certos comportamentos do Flight definindo valores de configuração
através do método `set`.

``` php
Flight::set('flight.log_errors', true);
```

A seguir está uma lista de todas as configurações de configuração disponíveis:

``` html 
flight.base_url - Substitui a URL base da solicitação. (padrão: null)
flight.case_sensitive - Correspondência sensível a maiúsculas para URLs. (padrão: false)
flight.handle_errors - Permite que o Flight trate todos os erros internamente. (padrão: true)
flight.log_errors - Registra erros no arquivo de log de erros do servidor web. (padrão: false)
flight.views.path - Diretório contendo arquivos de template de visão. (padrão: ./views)
flight.views.extension - Extensão de arquivo de template de visão. (padrão: .php)
```

## <a name="frameworkmethods"></a> Métodos do Framework

Flight foi projetado para ser fácil de usar e entender. A seguir está o conjunto completo
de métodos para o framework. Consiste em métodos principais, que são métodos estáticos regulares,
e métodos extensíveis, que são métodos mapeados que podem ser filtrados
ou sobrescritos.

### Métodos Principais

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Cria um método de framework personalizado.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registra uma classe a um método de framework.
Flight::before(string $name, callable $callback) // Adiciona um filtro antes de um método de framework.
Flight::after(string $name, callable $callback) // Adiciona um filtro depois de um método de framework.
Flight::path(string $path) // Adiciona um caminho para carregamento automático de classes.
Flight::get(string $key) // Obtém uma variável.
Flight::set(string $key, mixed $value) // Define uma variável.
Flight::has(string $key) // Verifica se uma variável está definida.
Flight::clear(array|string $key = []) // Limpa uma variável.
Flight::init() // Inicializa o framework com suas configurações padrão.
Flight::app() // Obtém a instância do objeto de aplicativo
```

### Métodos Extensíveis

```php
Flight::start() // Inicia o framework.
Flight::stop() // Para o framework e envia uma resposta.
Flight::halt(int $code = 200, string $message = '') // Para o framework com um código de status e mensagem opcionais.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // Mapeia um padrão de URL a uma função de retorno de chamada.
Flight::group(string $pattern, callable $callback) // Cria agrupamento para URLs, o padrão deve ser uma string.
Flight::redirect(string $url, int $code) // Redireciona para outra URL.
Flight::render(string $file, array $data, ?string $key = null) // Renderiza um arquivo de template.
Flight::error(Throwable $error) // Envia uma resposta HTTP 500.
Flight::notFound() // Envia uma resposta HTTP 404.
Flight::etag(string $id, string $type = 'string') // Realiza caching ETag HTTP.
Flight::lastModified(int $time) // Realiza caching HTTP de última modificação.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSONP.
```

Quaisquer métodos personalizados adicionados com `map` e `register` também podem ser filtrados.

## <a name="frameworkinstance"></a> Instância do Framework

Em vez de executar o Flight como uma classe estática global, você pode optar por executá-lo
como uma instância de objeto.

``` php
require 'flight/autoload.php';

use flight\Engine;

$app = new Engine();

$app->route('/', function(){
    echo 'Olá, mundo!';
});

$app->start();
```

Assim, em vez de chamar o método estático, você chamaria o método da instância com
o mesmo nome no objeto Engine.