# Extensão / Containers

O Flight foi projetado para ser um framework extensível. O framework vem com um conjunto
de métodos e componentes padrão, mas permite que você mapeie seus próprios métodos,
registre suas próprias classes ou até substitua classes e métodos existentes.

## Mapeando Métodos

Para mapear seu próprio método personalizado, você usa a função `map`:

```php
// Mapeie seu método
Flight::map('hello', function (string $name) {
  echo "olá $name!";
});

// Chame seu método personalizado
Flight::hello('Bob');
```

## Registrando Classes / Containerização

Para registrar sua própria classe, você usa a função `register`:

```php
// Registre sua classe
Flight::register('user', User::class);

// Obtenha uma instância de sua classe
$user = Flight::user();
```

O método de registro também permite que você passe parâmetros para o construtor de sua classe
para que, ao carregar sua classe personalizada, ela seja pré-inicializada.
Você pode definir os parâmetros do construtor passando em um array adicional.
Aqui está um exemplo de carregamento de uma conexão de banco de dados:

```php
// Registrar classe com parâmetros do construtor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtenha uma instância de sua classe
// Isso criará um objeto com os parâmetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Se você passar um parâmetro de retorno de chamada adicional, ele será executado imediatamente
após a construção da classe. Isso permite que você execute quaisquer procedimentos de configuração para o seu
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

Por padrão, toda vez que você carregar sua classe, receberá uma instância compartilhada.
Para obter uma nova instância de uma classe, basta passar `false` como parâmetro:

```php
// Instância compartilhada da classe
$compartilhado = Flight::db();

// Nova instância da classe
$novo = Flight::db(false);
```

Lembre-se de que métodos mapeados têm precedência sobre classes registradas. Se você
declarar ambos com o mesmo nome, apenas o método mapeado será invocado.