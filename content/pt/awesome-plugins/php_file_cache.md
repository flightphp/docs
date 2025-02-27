# flightphp/cache

Classe de armazenamento em cache em arquivo PHP leve, simples e autônoma

**Vantagens** 
- Leve, autônoma e simples
- Todo o código em um arquivo - sem drivers desnecessários.
- Seguro - cada arquivo de cache gerado possui um cabeçalho php com die, tornando o acesso direto impossível mesmo que alguém conheça o caminho e seu servidor não esteja configurado corretamente
- Bem documentado e testado
- Manipula a concorrência corretamente via flock
- Suporta PHP 7.4+
- Gratuito sob uma licença MIT

Este site de documentação está usando esta biblioteca para armazenar em cache cada uma das páginas!

Clique [aqui](https://github.com/flightphp/cache) para ver o código.

## Instalação

Instale via composer:

```bash
composer require flightphp/cache
```

## Uso

O uso é bastante simples. Isso salva um arquivo de cache no diretório de cache.

```php
use flight\Cache;

$app = Flight::app();

// Você passa o diretório onde o cache será armazenado no construtor
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Isso garante que o cache seja usado apenas quando estiver em modo de produção
	// ENVIRONMENT é uma constante definida no seu arquivo de bootstrap ou em outro lugar no seu aplicativo
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Então você pode usá-lo no seu código assim:

```php

// Obter instância de cache
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // retornar dados a serem armazenados em cache
}, 10); // 10 segundos

// ou
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 segundos
}
```

## Documentação

Visite [https://github.com/flightphp/cache](https://github.com/flightphp/cache) para a documentação completa e certifique-se de ver a pasta [exemplos](https://github.com/flightphp/cache/tree/master/examples).