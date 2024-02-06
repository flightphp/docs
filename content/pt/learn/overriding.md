# Substituição

Flight permite que você substitua sua funcionalidade padrão para atender às suas próprias necessidades,
sem ter que modificar nenhum código.

Por exemplo, quando o Flight não consegue corresponder a uma URL a uma rota, ele invoca o método `notFound`,
que envia uma resposta genérica `HTTP 404`. Você pode substituir esse comportamento
usando o método `map`:

```php
Flight::map('notFound', function() {
  // Exibir página de erro personalizada 404
  include 'errors/404.html';
});
```

Flight também permite que você substitua componentes principais do framework.
Por exemplo, você pode substituir a classe Router padrão pela sua própria classe personalizada:

```php
// Registrar sua classe personalizada
Flight::register('router', MyRouter::class);

// Quando o Flight carrega a instância do Router, ele carregará sua classe
$myrouter = Flight::router();
```

No entanto, métodos do framework como `map` e `register` não podem ser substituídos. Você
obterá um erro se tentar fazê-lo.