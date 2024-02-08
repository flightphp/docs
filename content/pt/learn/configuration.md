# Configuração

É possível personalizar certos comportamentos do Flight definindo valores de configuração através do método `set`.

```php
Flight::set('flight.log_errors', true);
```

## Configurações Disponíveis

A seguir está uma lista de todas as configurações disponíveis:

- **flight.base_url** - Substituir a URL base da solicitação. (padrão: null)
- **flight.case_sensitive** - Corresponder de forma sensível a maiúsculas e minúsculas para URLs. (padrão: false)
- **flight.handle_errors** - Permitir ao Flight lidar com todos os erros internamente. (padrão: true)
- **flight.log_errors** - Registrar erros no arquivo de log de erro do servidor web. (padrão: false)
- **flight.views.path** - Diretório que contém arquivos de modelo de visualização. (padrão: ./views)
- **flight.views.extension** - Extensão do arquivo de modelo de visualização. (padrão: .php)

## Variáveis

O Flight permite que você salve variáveis para poder usá-las em qualquer lugar em sua aplicação.

```php
// Salve sua variável
Flight::set('id', 123);

// Em outro lugar de sua aplicação
$id = Flight::get('id');
```
Para verificar se uma variável foi definida, você pode fazer:

```php
if (Flight::has('id')) {
  // Faça alguma coisa
}
```

Você pode limpar uma variável fazendo:

```php
// Limpa a variável id
Flight::clear('id');

// Limpa todas as variáveis
Flight::clear();
```

O Flight também utiliza variáveis para fins de configuração.

```php
Flight::set('flight.log_errors', true);
```

## Manipulação de Erros

### Erros e Exceções

Todos os erros e exceções são capturados pelo Flight e passados para o método `error`.
O comportamento padrão é enviar uma resposta genérica de `Erro Interno do Servidor HTTP 500` com algumas informações de erro.

Você pode substituir este comportamento de acordo com suas necessidades:

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

Quando uma URL não pode ser encontrada, o Flight chama o método `notFound`. O comportamento padrão é enviar uma resposta de `Não Encontrado HTTP 404` com uma mensagem simples.

Você pode substituir este comportamento de acordo com suas necessidades:

```php
Flight::map('notFound', function () {
  // Lidar com não encontrado
});
```