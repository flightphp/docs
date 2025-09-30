# Filtragem

## Visão Geral

O Flight permite que você filtre [métodos mapeados](/learn/extending) antes e depois de serem chamados.

## Entendendo
Não há ganchos predefinidos que você precise memorizar. Você pode filtrar qualquer um dos métodos padrão do framework, bem como qualquer método personalizado que você tenha mapeado.

Uma função de filtro se parece com esta:

```php
/**
 * @param array $params Os parâmetros passados para o método sendo filtrado.
 * @param string $output (apenas buffer de saída v2) A saída do método sendo filtrado.
 * @return bool Retorne true/void ou não retorne para continuar a cadeia, false para quebrar a cadeia.
 */
function (array &$params, string &$output): bool {
  // Código do filtro
}
```

Usando as variáveis passadas, você pode manipular os parâmetros de entrada e/ou a saída.

Você pode fazer um filtro executar antes de um método fazendo:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Faça algo
});
```

Você pode fazer um filtro executar depois de um método fazendo:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Faça algo
});
```

Você pode adicionar quantos filtros quiser a qualquer método. Eles serão chamados na ordem em que são declarados.

Aqui está um exemplo do processo de filtragem:

```php
// Mapeie um método personalizado
Flight::map('hello', function (string $name) {
  return "Olá, $name!";
});

// Adicione um filtro antes
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipule o parâmetro
  $params[0] = 'Fred';
  return true;
});

// Adicione um filtro depois
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipule a saída
  $output .= " Tenha um bom dia!";
  return true;
});

// Invoque o método personalizado
echo Flight::hello('Bob');
```

Isso deve exibir:

```
Olá Fred! Tenha um bom dia!
```

Se você tiver definido múltiplos filtros, você pode quebrar a cadeia retornando `false` em qualquer uma de suas funções de filtro:

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

> **Nota:** Métodos principais como `map` e `register` não podem ser filtrados porque são chamados diretamente e não invocados dinamicamente. Veja [Estendendo o Flight](/learn/extending) para mais informações.

## Veja Também
- [Estendendo o Flight](/learn/extending)

## Solução de Problemas
- Certifique-se de retornar `false` de suas funções de filtro se quiser que a cadeia pare. Se você não retornar nada, a cadeia continuará.

## Registro de Alterações
- v2.0 - Lançamento Inicial.