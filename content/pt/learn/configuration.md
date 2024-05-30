# Configuração

Você pode personalizar determinados comportamentos do Flight definindo valores de configuração através do método `set`.

```php
Flight::set('flight.log_errors', true);
```

## Configurações Disponíveis

A seguir está uma lista de todas as configurações disponíveis:

- **flight.base_url** `?string` - Substituir a URL base da requisição. (padrão: null)
- **flight.case_sensitive** `bool` - Combinação sensível a maiúsculas e minúsculas para URLs. (padrão: false)
- **flight.handle_errors** `bool` - Permitir que o Flight lide com todos os erros internamente. (padrão: true)
- **flight.log_errors** `bool` - Registrar erros no arquivo de log de erros do servidor web. (padrão: false)
- **flight.views.path** `string` - Diretório contendo arquivos de template de visualização. (padrão: ./views)
- **flight.views.extension** `string` - Extensão do arquivo de template de visualização. (padrão: .php)
- **flight.content_length** `bool` - Definir o cabeçalho `Content-Length`. (padrão: true)
- **flight.v2.output_buffering** `bool` - Usar o buffer de saída legado. Consulte [migrando para v3](migrating-to-v3). (padrão: false)

## Variáveis

O Flight permite que você salve variáveis para que possam ser usadas em qualquer lugar da sua aplicação.

```php
// Salve sua variável
Flight::set('id', 123);

// Em outro lugar na sua aplicação
$id = Flight::get('id');
```

Para verificar se uma variável foi definida, você pode fazer:

```php
if (Flight::has('id')) {
  // Faça algo
}
```

Você pode limpar uma variável fazendo:

```php
// Limpa a variável id
Flight::clear('id');

// Limpa todas as variáveis
Flight::clear();
```

O Flight também usa variáveis para fins de configuração.

```php
Flight::set('flight.log_errors', true);
```

## Manipulação de Erros

### Erros e Exceções

Todos os erros e exceções são capturados pelo Flight e passados ao método `error`. O comportamento padrão é enviar uma resposta genérica de `HTTP 500 Internal Server Error` com algumas informações de erro.

Você pode substituir esse comportamento conforme suas necessidades:

```php
Flight::map('error', function (Throwable $error) {
  // Lidar com o erro
  echo $error->getTraceAsString();
});
```

Por padrão, os erros não são registrados no servidor web. Você pode habilitar isso alterando a configuração:

```php
Flight::set('flight.log_errors', true);
```

### Não Encontrado

Quando uma URL não pode ser encontrada, o Flight chama o método `notFound`. O comportamento padrão é enviar uma resposta de `HTTP 404 Not Found` com uma mensagem simples.

Você pode substituir esse comportamento conforme suas necessidades:

```php
Flight::map('notFound', function () {
  // Lidar com não encontrado
});
```