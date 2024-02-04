# HTTP Caching

O Flight fornece suporte integrado para o armazenamento em cache de nível HTTP. Se a condição de cache
for atendida, o Flight retornará uma resposta HTTP `304 Not Modified`. Na próxima vez que o
cliente solicitar o mesmo recurso, eles serão solicitados a usar a versão em cache local.

## Última modificação

Você pode usar o método `lastModified` e passar um carimbo de data/hora UNIX para definir a data
e hora em que uma página foi modificada pela última vez. O cliente continuará a usar seu cache até que
o valor da última modificação seja alterado.

```php
Flight::route('/noticias', function () {
  Flight::lastModified(1234567890);
  echo 'Este conteúdo será armazenado em cache.';
});
```

## ETag

O armazenamento em cache de `ETag` é semelhante ao de `Última modificação`, exceto que você pode especificar qualquer ID
desejado para o recurso:

```php
Flight::route('/noticias', function () {
  Flight::etag('meu-id-único');
  echo 'Este conteúdo será armazenado em cache.';
});
```

Tenha em mente que chamar `lastModified` ou `etag` definirá e verificará o valor do cache. Se o valor do cache for o mesmo entre as solicitações, o Flight enviará imediatamente
uma resposta `HTTP 304` e interromperá o processamento.  