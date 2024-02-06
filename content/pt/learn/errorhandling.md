# Manipulação de Erros

## Erros e Exceções

Todos os erros e exceções são interceptados pelo Flight e passados para o método `error`.
O comportamento padrão é enviar uma resposta genérica de `HTTP 500 Erro Interno do Servidor`
com algumas informações de erro.

Você pode substituir esse comportamento para suas próprias necessidades:

```php
Flight::map('error', function (Throwable $error) {
  // Lidar com o erro
  echo $error->getTraceAsString();
});
```

Por padrão, os erros não são registrados no servidor web. Você pode habilitar isso
alterando a configuração:

```php
Flight::set('flight.log_errors', true);
```

## Não Encontrado

Quando um URL não pode ser encontrado, o Flight chama o método `notFound`. O comportamento
padrão é enviar uma resposta de `HTTP 404 Não Encontrado` com uma mensagem simples.

Você pode substituir esse comportamento para suas próprias necessidades:

```php
Flight::map('notFound', function () {
  // Lidar com não encontrado
});
```  