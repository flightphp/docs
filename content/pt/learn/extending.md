# Extensão

O Flight foi projetado para ser um framework extensível. O framework vem com um conjunto de métodos e componentes padrão, mas permite que você mapeie seus próprios métodos, registre suas próprias classes ou até mesmo substitua classes e métodos existentes.

Se você está procurando por um DIC (Dependency Injection Container), consulte a página do [Dependency Injection Container](dependency-injection-container).

## Mapeando Métodos

Para mapear seu próprio método customizado simples, você usa a função `map`:

```php
// Mapeie seu método
Flight::map('hello', function (string $name) {
  echo "olá $name!";
});

// Chame seu método customizado
Flight::hello('Bob');
```

Isso é usado mais quando você precisa passar variáveis para o seu método para obter um valor esperado. O uso do método `register()` como abaixo é mais para passar configuração e então chamar sua classe pré-configurada.

## Registrando Classes

Para registrar sua própria classe e configurá-la, você usa a função `register`:

```php
// Registre sua classe
Flight::register('user', User::class);

// Obtenha uma instância da sua classe
$user = Flight::user();
```

O método de registro também permite que você passe parâmetros para o construtor da sua classe. Portanto, ao carregar sua classe customizada, ela será pré-inicializada. Você pode definir os parâmetros do construtor passando um array adicional. Aqui está um exemplo de carregamento de uma conexão de banco de dados:

```php
// Registrar classe com parâmetros do construtor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'usuario', 'senha']);

// Obter uma instância da sua classe
// Isso criará um objeto com os parâmetros definidos
//
// new PDO('mysql:host=localhost;dbname=test', 'usuario', 'senha');
//
$db = Flight::db();

// e se você precisar dele mais tarde no seu código, basta chamar o mesmo método novamente
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Se você passar um parâmetro de retorno de chamada adicional, ele será executado imediatamente após a construção da classe. Isso permite que você execute quaisquer procedimentos de configuração para o seu novo objeto. A função de retorno de chamada recebe um parâmetro, uma instância do novo objeto.

```php
// O callback receberá o objeto que foi construído
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'usuario', 'senha'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por padrão, toda vez que você carrega sua classe, você obterá uma instância compartilhada. Para obter uma nova instância de uma classe, basta passar `false` como parâmetro:

```php
// Instância compartilhada da classe
$compartilhado = Flight::db();

// Nova instância da classe
$novo = Flight::db(false);
```

Lembre-se de que métodos mapeados têm precedência sobre classes registradas. Se você declarar ambos com o mesmo nome, apenas o método mapeado será invocado.

## Substituindo Métodos do Framework

O Flight permite que você substitua sua funcionalidade padrão para atender às suas próprias necessidades, sem precisar modificar nenhum código.

Por exemplo, quando o Flight não consegue corresponder a uma URL a uma rota, ele invoca o método `notFound` que envia uma resposta genérica `HTTP 404`. Você pode substituir esse comportamento usando o método `map`:

```php
Flight::map('notFound', function() {
  // Exibir página de erro personalizada 404
  include 'errors/404.html';
});
```

O Flight também permite que você substitua componentes essenciais do framework. Por exemplo, você pode substituir a classe de Roteador padrão por sua própria classe personalizada:

```php
// Registre sua classe personalizada
Flight::register('router', MyRouter::class);

// Quando o Flight carregar a instância do Roteador, ele carregará sua classe
$meuRoteador = Flight::router();
```

No entanto, métodos do framework como `map` e `register` não podem ser substituídos. Você receberá um erro se tentar fazer isso.