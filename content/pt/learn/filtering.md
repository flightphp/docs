# Filtragem

Flight permite que você filtre métodos antes e depois de serem chamados. Não há ganchos predefinidos que você precise memorizar. Você pode filtrar qualquer um dos métodos padrão do framework, bem como quaisquer métodos personalizados que você tenha mapeado.

Uma função de filtro se parece com isso:

```php
function (array &$params, string &$output): bool {
  // Código do filtro
}
```

Usando as variáveis passadas, você pode manipular os parâmetros de entrada e/ou a saída.

Você pode fazer um filtro ser executado antes de um método fazendo:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Faça algo
});
```

Você pode fazer um filtro ser executado depois de um método fazendo:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Faça algo
});
```

Você pode adicionar quantos filtros quiser a qualquer método. Eles serão chamados na
ordem em que são declarados.

Aqui está um exemplo do processo de filtragem:

```php
// Mapear um método personalizado
Flight::map('hello', function (string $name) {
  return "Olá, $name!";
});

// Adicionar um filtro antes
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipular o parâmetro
  $params[0] = 'João';
  return true;
});

// Adicionar um filtro após
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipular a saída
  $output .= " Tenha um bom dia!";
  return true;
});

// Invocar o método personalizado
echo Flight::hello('Bob');
```

Isso deve exibir:

```
Olá João! Tenha um bom dia!
```

Se você definiu vários filtros, pode interromper a cadeia retornando `false`
em qualquer uma de suas funções de filtro:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'um';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'dois';

  // Isso encerrará a cadeia
  return false;
});

// Isso não será chamado
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'três';
  return true;
});
```

Observe que métodos principais como `map` e `register` não podem ser filtrados porque
eles são chamados diretamente e não são invocados dinamicamente.