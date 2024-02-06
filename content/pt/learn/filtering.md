# Filtragem

Flight permite que você filtre os métodos antes e depois de serem chamados. Não há ganchos predefinidos que você precise memorizar. Você pode filtrar qualquer um dos métodos padrão do framework, bem como quaisquer métodos personalizados que você tenha mapeado.

Uma função de filtro se parece com isso:

```php
function (array &$params, string &$output): bool {
  // Código de filtro
}
```

Usando as variáveis passadas, você pode manipular os parâmetros de entrada e/ou a saída.

Você pode ter um filtro sendo executado antes de um método fazendo:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Faça algo
});
```

Você pode ter um filtro sendo executado depois de um método fazendo:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Faça algo
});
```

Você pode adicionar quantos filtros quiser a qualquer método. Eles serão chamados na ordem em que foram declarados.

Aqui está um exemplo do processo de filtragem:

```php
// Mapear um método personalizado
Flight::map('hello', function (string $name) {
  return "Olá, $name!";
});

// Adicionar um filtro antes
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipular o parâmetro
  $params[0] = 'Fred';
  return true;
});

// Adicionar um filtro depois
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
Olá Fred! Tenha um bom dia!
```

Se você definiu múltiplos filtros, você pode quebrar a cadeia retornando `false`
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

Observação, métodos principais como `map` e `register` não podem ser filtrados porque
são chamados diretamente e não invocados dinamicamente.