## Ampliação

Flight é projetado para ser um framework extensível. O framework vem com um conjunto
de métodos e componentes padrão, mas permite mapear seus próprios métodos,
registrar suas próprias classes ou até mesmo substituir classes e métodos existentes.

Se você está procurando por um DIC (Dependency Injection Container), vá para a
página de [Dependency Injection Container](dependency-injection-container).

## Mapeamento de Métodos

Para mapear seu próprio método simples personalizado, você usa a função `map`:

```php
// Mapear seu método
Flight::map('hello', function (string $name) {
  echo "olá $name!";
});

// Chamar seu método personalizado
Flight::hello('Bob');
```

Isso é usado mais quando você precisa passar variáveis para o seu método para obter um valor esperado.
Usar o método `register()` como abaixo é mais para passar configurações
e então chamar sua classe pré-configurada.

## Registrando Classes

Para registrar sua própria classe e configurá-la, você usa a função `register`:

```php
// Registrar sua classe
Flight::register('user', User::class);

// Obter uma instância da sua classe
$user = Flight::user();
```

O método de registro também permite passar parâmetros para o construtor da sua classe
Assim, ao carregar sua classe personalizada, ela virá pré-inicializada.
Você pode definir os parâmetros do construtor passando em um array adicional.
Aqui está um exemplo de carregamento de uma conexão de banco de dados:

```php
// Registrar classe com parâmetros de construtor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obter uma instância da sua classe
// Isso criará um objeto com os parâmetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// e se você precisar mais tarde em seu código, basta chamar o mesmo método novamente
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Se você passar um parâmetro de retorno de chamada adicional, ele será executado imediatamente
após a construção da classe. Isso permite que você execute quaisquer procedimentos de configuração para seu
novo objeto. A função de retorno de chamada recebe um parâmetro, uma instância do novo objeto.

```php
// O retorno de chamada receberá o objeto que foi construído
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por padrão, toda vez que você carregar sua classe, você obterá uma instância compartilhada.
Para obter uma nova instância de uma classe, basta passar `false` como parâmetro:

```php
// Instância compartilhada da classe
$compartilhado = Flight::db();

// Nova instância da classe
$novo = Flight::db(false);
```

Lembre-se de que métodos mapeados têm precedência sobre classes registradas. Se você
declarar ambos usando o mesmo nome, apenas o método mapeado será invocado.

## Substituindo Métodos do Framework

Flight permite que você substitua sua funcionalidade padrão para atender às suas necessidades,
sem ter que modificar nenhum código.

Por exemplo, quando o Flight não consegue corresponder uma URL a uma rota, ele chama o método `notFound`,
o qual envia uma resposta genérica de `HTTP 404`. Você pode substituir esse comportamento
usando o método `map`:

```php
Flight::map('notFound', function() {
  // Exibir página de erro 404 personalizada
  include 'errors/404.html';
});
```

O Flight também permite substituir componentes principais do framework.
Por exemplo, você pode substituir a classe Router padrão pela sua própria classe personalizada:

```php
// Registrar sua classe personalizada
Flight::register('router', MyRouter::class);

// Quando o Flight carrega a instância do Router, carregará sua classe
$meuroteador = Flight::router();
```

No entanto, métodos do framework como `map` e `register` não podem ser substituídos. Você receberá
um erro se tentar fazer isso.