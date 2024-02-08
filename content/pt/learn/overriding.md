# Substituição

O Flight permite que você substitua sua funcionalidade padrão para atender às suas próprias necessidades,
sem ter que modificar nenhum código.

Por exemplo, quando o Flight não consegue corresponder a uma URL a uma rota, ele invoca o método `notFound`,
que envia uma resposta genérica de `HTTP 404`. Você pode substituir esse comportamento
usando o método `map`:

```php
Flight::map('notFound', function() {
  // Exibir página 404 personalizada
  include 'errors/404.html';
});
```

O Flight também permite que você substitua componentes principais do framework.
Por exemplo, você pode substituir a classe do Roteador padrão pela sua própria classe personalizada:

```php
// Registre sua classe personalizada
Flight::register('router', MyRouter::class);

// Quando o Flight carrega a instância do Roteador, ele carregará sua classe
$myrouter = Flight::router();
```

Métodos do framework como `map` e `register`, no entanto, não podem ser substituídos. Você receberá
um erro se tentar fazê-lo.