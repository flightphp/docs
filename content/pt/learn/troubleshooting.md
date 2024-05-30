# Resolução de Problemas

Esta página irá ajudar você a resolver problemas comuns que podem surgir ao usar o Flight.

## Problemas Comuns

### 404 Não Encontrado ou Comportamento de Rota Inesperado

Se você estiver vendo um erro 404 Não Encontrado (mas jura pela sua vida que realmente está lá e não é um erro de digitação), isso na verdade pode ser um problema com você retornando um valor no final da sua rota em vez de apenas ecoá-lo. O motivo para isso é intencional, mas pode pegar alguns desenvolvedores de surpresa.

```php

Flight::route('/hello', function(){
	// Isso pode causar um erro 404 Não Encontrado
	return 'Olá Mundo';
});

// O que você provavelmente deseja
Flight::route('/hello', function(){
	echo 'Olá Mundo';
});

```

O motivo para isso é por causa de um mecanismo especial incorporado no roteador que trata a saída de retorno como um sinal de "ir para a próxima rota". Você pode ver o comportamento documentado na seção de [Roteamento](/learn/routing#passing).