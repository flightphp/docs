# flightphp/cache

Classe de cache PHP leve, simples e standalone em arquivo, bifurcada de [Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)

**Vantagens** 
- Leve, standalone e simples
- Todo o código em um arquivo - sem drivers desnecessários.
- Segura - todo arquivo de cache gerado tem um cabeçalho PHP com die, tornando o acesso direto impossível mesmo se alguém souber o caminho e seu servidor não estiver configurado corretamente
- Bem documentada e testada
- Lida com concorrência corretamente via flock
- Suporta PHP 7.4+
- Gratuita sob licença MIT

Este site de documentação está usando esta biblioteca para cachear cada uma das páginas!

Clique [aqui](https://github.com/flightphp/cache) para ver o código.

## Instalação

Instale via composer:

```bash
composer require flightphp/cache
```

## Uso

O uso é bastante direto. Isso salva um arquivo de cache no diretório de cache.

```php
use flight\Cache;

$app = Flight::app();

// Você passa o diretório onde o cache será armazenado no construtor
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Isso garante que o cache seja usado apenas no modo de produção
	// ENVIRONMENT é uma constante definida no seu arquivo de bootstrap ou em outro lugar na sua app
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

### Obter um Valor de Cache

Você usa o método `get()` para obter um valor em cache. Se quiser um método de conveniência que atualize o cache se ele estiver expirado, você pode usar `refreshIfExpired()`.

```php

// Obter instância de cache
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // return data to be cached
}, 10); // 10 segundos

// ou
$data = $cache->get('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->set('simple-cache-test', $data, 10); // 10 segundos
}
```

### Armazenar um Valor de Cache

Você usa o método `set()` para armazenar um valor no cache.

```php
Flight::cache()->set('simple-cache-test', 'my cached data', 10); // 10 segundos
```

### Apagar um Valor de Cache

Você usa o método `delete()` para apagar um valor no cache.

```php
Flight::cache()->delete('simple-cache-test');
```

### Verificar se um Valor de Cache Existe

Você usa o método `exists()` para verificar se um valor existe no cache.

```php
if(Flight::cache()->exists('simple-cache-test')) {
	// faça algo
}
```

### Limpar o Cache
Você usa o método `flush()` para limpar todo o cache.

```php
Flight::cache()->flush();
```

### Extrair metadados com cache

Se você quiser extrair timestamps e outros metadados sobre uma entrada de cache, certifique-se de passar `true` como o parâmetro correto.

```php
$data = $cache->refreshIfExpired("simple-cache-meta-test", function () {
    echo "Refreshing data!" . PHP_EOL;
    return date("H:i:s"); // return data to be cached
}, 10, true); // true = return with metadata
// ou
$data = $cache->get("simple-cache-meta-test", true); // true = return with metadata

/*
Exemplo de item em cache recuperado com metadados:
{
    "time":1511667506, <-- save unix timestamp
    "expire":10,       <-- expire time in seconds
    "data":"04:38:26", <-- unserialized data
    "permanent":false
}

Usando metadados, podemos, por exemplo, calcular quando o item foi salvo ou quando expira
Também podemos acessar os dados em si com a chave "data"
*/

$expiresin = ($data["time"] + $data["expire"]) - time(); // get unix timestamp when data expires and subtract current timestamp from it
$cacheddate = $data["data"]; // we access the data itself with the "data" key

echo "Última salvamento de cache: $cacheddate, expira em $expiresin segundos";
```

## Documentação

Visite [https://github.com/flightphp/cache](https://github.com/flightphp/cache) para ver o código. Certifique-se de ver a pasta [examples](https://github.com/flightphp/cache/tree/master/examples) para maneiras adicionais de usar o cache.