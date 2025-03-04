# Caching HTTP

Voo fornece suporte embutido para o caching de nível HTTP. Se a condição de caching for atendida, Voo retornará uma resposta `304 Not Modified` HTTP. Da próxima vez que o cliente solicitar o mesmo recurso, eles serão convidados a usar sua versão em cache localmente.

## Última Modificação

Você pode usar o método `lastModified` e passar um carimbo de data/hora UNIX para definir a data e hora em que a página foi modificada pela última vez. O cliente continuará a usar seu cache até que o valor da última modificação seja alterado.

```php
Flight::route('/noticias', function () {
  Flight::lastModified(1234567890);
  echo 'Este conteúdo será armazenado em cache.';
});
```

## ETag

O caching `ETag` é semelhante ao `Última Modificação`, exceto que você pode especificar qualquer ID que desejar para o recurso:

```php
Flight::route('/noticias', function () {
  Flight::etag('meu-id-único');
  echo 'Este conteúdo será armazenado em cache.';
});
```

Tenha em mente que chamar `lastModified` ou `etag` definirá e verificará o valor do cache. Se o valor do cache for o mesmo entre as solicitações, Voo enviará imediatamente uma resposta `HTTP 304` e interromperá o processamento.