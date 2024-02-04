# Manipulação de Erros

## Erros e Exceções

Todos os erros e exceções são capturados pelo Flight e passados para o método `error`.
O comportamento padrão é enviar uma resposta genérica de `Erro de Servidor Interno HTTP 500`
com algumas informações de erro.

Você pode substituir esse comportamento para suas próprias necessidades:

```php
Flight::map('error', function (Throwable $error) {
  // Lidar com o erro
  echo $error->getTraceAsString();
});
```

Por padrão, os erros não são registrados no servidor web. Você pode ativar isso alterando a configuração:

```php
Flight::set('flight.log_errors', true);
```

## Não Encontrado

Quando uma URL não pode ser encontrada, o Flight chama o método `notFound`. O comportamento padrão
é enviar uma resposta de `Não Encontrado HTTP 404` com uma mensagem simples.

Você pode substituir esse comportamento para suas próprias necessidades:

```php
Flight::map('notFound', function () {
  // Lidar com não encontrado
});
```