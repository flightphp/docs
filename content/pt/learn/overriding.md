# Substituição

Flight permite que você substitua sua funcionalidade padrão para atender às suas próprias necessidades,
sem precisar modificar nenhum código.

Por exemplo, quando o Flight não consegue corresponder uma URL a uma rota, ele invoca o método `notFound`
que envia uma resposta genérica `HTTP 404`. Você pode substituir esse comportamento
usando o método `map`:

```php
Flight::map('notFound', function() {
  // Exibir página de erro 404 personalizada
  include 'errors/404.html';
});
```

O Flight também permite que você substitua componentes principais do framework.
Por exemplo, você pode substituir a classe de Roteador padrão pela sua própria classe personalizada:

```php
// Registre sua classe personalizada
Flight::register('router', MyRouter::class);

// Quando o Flight carrega a instância do Roteador, ele carregará sua classe
$meuRoteador = Flight::router();
```

No entanto, métodos do framework como `map` e `register` não podem ser substituídos. Você
irá obter um erro se tentar fazê-lo.