# Variáveis

O Flight permite que você salve variáveis para que possam ser usadas em qualquer lugar de sua aplicação.

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