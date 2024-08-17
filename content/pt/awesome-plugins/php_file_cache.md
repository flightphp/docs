# Wruczek/PHP-File-Cache

Classe standalone de cache de arquivo PHP leve e simples

**Vantagens**
- Leve, standalone e simples
- Todo código em um único arquivo - sem drivers desnecessários.
- Seguro - cada arquivo de cache gerado possui um cabeçalho php com die, tornando o acesso direto impossível mesmo se alguém conhecer o caminho e seu servidor não estiver configurado corretamente
- Bem documentado e testado
- Manipula corretamente a concorrência via flock
- Suporta PHP 5.4.0 - 7.1+
- Gratuito sob uma licença MIT

Clique [aqui](https://github.com/Wruczek/PHP-File-Cache) para visualizar o código.

## Instalação

Instale via composer:

```bash
composer require wruczek/php-file-cache
```

## Uso

O uso é bastante direto.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Você passa o diretório no qual o cache será armazenado para o construtor
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Isso garante que o cache seja usado apenas no modo de produção
	// AMBIENTE é uma constante definida em seu arquivo de inicialização ou em outro lugar de seu aplicativo
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Então você pode usá-lo em seu código assim:

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

Visite [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) para ver a documentação completa e certifique-se de ver a pasta de [exemplos](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples).