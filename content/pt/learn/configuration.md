# Configuração

Você pode personalizar determinados comportamentos do Flight definindo valores de configuração através do método `set`.

```php
Flight::set('flight.log_errors', true);
```

## Configurações Disponíveis

A seguir está uma lista de todas as configurações disponíveis:

- **flight.base_url** `?string` - Substituir a URL base da solicitação. (padrão: null)
- **flight.case_sensitive** `bool` - Correspondência com distinção entre maiúsculas e minúsculas para URLs. (padrão: false)
- **flight.handle_errors** `bool` - Permitir que o Flight gerencie todos os erros internamente. (padrão: true)
- **flight.log_errors** `bool` - Registrar erros no arquivo de log de erros do servidor web. (padrão: false)
- **flight.views.path** `string` - Diretório contendo arquivos de modelo de visualização. (padrão: ./views)
- **flight.views.extension** `string` - Extensão do arquivo de modelo de visualização. (padrão: .php)
- **flight.content_length** `bool` - Definir o cabeçalho `Content-Length`. (padrão: true)
- **flight.v2.output_buffering** `bool` - Usar o antigo buffer de saída. Consulte [migrando para o v3](migrating-to-v3). (padrão: false)

## Configuração do Carregador

Existe adicionalmente uma outra configuração para o carregador. Isso permitirá
que você carregue classes com `_` no nome da classe.

```php
// Ativar o carregamento de classe com underscores
// Por padrão é verdadeiro
Loader::$v2ClassLoading = false;
```

## Variáveis

O Flight permite que você salve variáveis para que possam ser usadas em qualquer lugar de sua aplicação.

```php
// Salve sua variável
Flight::set('id', 123);

// Em outro lugar de sua aplicação
$id = Flight::get('id');
```
Para ver se uma variável foi definida, você pode fazer:

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

O Flight também utiliza variáveis para fins de configuração.

```php
Flight::set('flight.log_errors', true);
```

## Manipulação de Erros

### Erros e Exceções

Todos os erros e exceções são capturados pelo Flight e passados para o método `error`.
O comportamento padrão é enviar uma resposta genérica de `HTTP 500 Erro interno do servidor`
com algumas informações de erro.

Você pode substituir esse comportamento conforme suas necessidades:

```php
Flight::map('error', function (Throwable $error) {
  // Manipular erro
  echo $error->getTraceAsString();
});
```

Por padrão, os erros não são registrados no servidor web. Você pode ativá-los
alterando a configuração:

```php
Flight::set('flight.log_errors', true);
```

### Não Encontrado

Quando uma URL não pode ser encontrada, o Flight chama o método `notFound`.
O comportamento padrão é enviar uma resposta de `HTTP 404 Não encontrado` com uma mensagem simples.

Você pode substituir esse comportamento conforme suas necessidades:

```php
Flight::map('notFound', function () {
  // Lidar com não encontrado
});
```