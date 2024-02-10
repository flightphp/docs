# Configuração

Você pode personalizar certos comportamentos do Flight definindo valores de configuração através do método `set`.

```php
Flight::set('flight.log_errors', true);
```

## Configurações Disponíveis

A seguir está uma lista de todas as configurações disponíveis:

- **flight.base_url** - Substituir a URL base da requisição. (padrão: nulo)
- **flight.case_sensitive** - Correspondência sensível a maiúsculas e minúsculas para URLs. (padrão: falso)
- **flight.handle_errors** - Permitir que o Flight lide com todos os erros internamente. (padrão: verdadeiro)
- **flight.log_errors** - Registrar erros no arquivo de log de erros do servidor web. (padrão: falso)
- **flight.views.path** - Diretório que contém arquivos de template de visualização. (padrão: ./views)
- **flight.views.extension** - Extensão do arquivo de template de visualização. (padrão: .php)

## Variáveis

O Flight permite que você salve variáveis para que elas possam ser usadas em qualquer lugar de sua aplicação.

```php
// Salve sua variável
Flight::set('id', 123);

// Em outro lugar de sua aplicação
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

Todos os erros e exceções são capturados pelo Flight e passados para o método `error`. O comportamento padrão é enviar uma resposta genérica de `HTTP 500 Internal Server Error` com algumas informações de erro.

Você pode substituir esse comportamento para suas próprias necessidades:

```php
Flight::map('error', function (Throwable $error) {
  // Manipular erro
  echo $error->getTraceAsString();
});
```

Por padrão, os erros não são registrados no servidor web. Você pode habilitar isso alterando a configuração:

```php
Flight::set('flight.log_errors', true);
```

### Não Encontrado

Quando uma URL não pode ser encontrada, o Flight chama o método `notFound`. O comportamento padrão é enviar uma resposta de `HTTP 404 Not Found` com uma mensagem simples.

Você pode substituir esse comportamento para suas próprias necessidades:

```php
Flight::map('notFound', function () {
  // Lidar com não encontrado
});
```