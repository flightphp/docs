# Wruczek/PHP-File-Cache

Classe de armazenamento em arquivo PHP leve, simples e independente

**Vantagens**
- Leve, independente e simples
- Todo código em um arquivo - sem drivers desnecessários.
- Seguro - cada arquivo de cache gerado possui um cabeçalho php com die, tornando o acesso direto impossível mesmo que alguém saiba o caminho e seu servidor não esteja configurado corretamente
- Bem documentado e testado
- Manipula concorrência corretamente via flock
- Suporta PHP 5.4.0 - 7.1+
- Gratuito sob uma licença MIT

## Instalação

Instale via composer:

```bash
composer require wruczek/php-file-cache
```

## Uso

O uso é bastante simples.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Você passa o diretório onde o cache será armazenado para o construtor
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Isso garante que o cache só será usado quando estiver no modo de produção
	// AMBIENTE é uma constante definida em seu arquivo de inicialização ou em outro lugar em seu aplicativo
	$cache->setDevMode(AMBIENTE === 'desenvolvimento');
});
```

Então você pode usá-lo em seu código assim:

```php

// Obter instância de cache
$cache = Flight::cache();
$data = $cache->refreshIfExpired('testar-cache-simples', function () {
    return date("H:i:s"); // retornar dados para serem armazenados em cache
}, 10); // 10 segundos

// ou
$data = $cache->retrieve('testar-cache-simples');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('testar-cache-simples', $data, 10); // 10 segundos
}
```

## Documentação

Visite [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) para obter a documentação completa e certifique-se de ver a pasta de [exemplos](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples).