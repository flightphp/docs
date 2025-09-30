# Migração para a v3

A compatibilidade retroativa foi mantida na maior parte, mas há algumas alterações das quais você deve estar ciente ao 
migrar da v2 para a v3. Há algumas mudanças que conflitaram demais com padrões de design, então alguns ajustes tiveram que ser feitos.

## Comportamento de Buffer de Saída

_v3.5.0_

[Output buffering](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) é o processo onde a saída 
gerada por um script PHP é armazenada em um buffer (interno ao PHP) antes de ser enviada ao cliente. Isso permite que você modifique 
a saída antes de ela ser enviada ao cliente.

Em uma aplicação MVC, o Controller é o "gerenciador" e ele gerencia o que a view faz. Ter saída gerada fora do 
controller (ou no caso do Flight, às vezes uma função anônima) quebra o padrão MVC. Essa mudança é para estar mais alinhada 
com o padrão MVC e tornar o framework mais previsível e fácil de usar.

Na v2, o output buffering era tratado de uma forma onde não fechava consistentemente seu próprio buffer de saída, o que tornava 
[testes unitários](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 
e [streaming](https://github.com/flightphp/core/issues/413) mais difíceis. Para a maioria dos usuários, essa mudança pode não 
afetá-lo de fato. No entanto, se você estiver ecoando conteúdo fora de callables e controllers (por exemplo, em um hook), você provavelmente 
vai encontrar problemas. Ecoar conteúdo em hooks, e antes do framework realmente executar, pode ter funcionado no 
passado, mas não funcionará daqui para frente.

### Onde você pode ter problemas
```php
// index.php
require 'vendor/autoload.php';

// apenas um exemplo
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// isso na verdade estará bem
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// coisas como essa causarão um erro
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// isso na verdade está apenas bem
	echo 'Hello World';

	// Isso também deve estar bem
	Flight::hello();
});

Flight::after('start', function(){
	// isso causará um erro
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### Ativando o Comportamento de Renderização da v2

Você ainda pode manter seu código antigo como está sem fazer uma reescrita para torná-lo compatível com a v3? Sim, você pode! Você pode ativar o 
comportamento de renderização da v2 definindo a opção de configuração `flight.v2.output_buffering` como `true`. Isso permitirá que você continue a 
usar o antigo comportamento de renderização, mas é recomendado corrigi-lo daqui para frente. Na v4 do framework, isso será removido.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Agora isso estará bem
	echo '<html><head><title>My Page</title></head><body>';
});

// mais código 
```

## Mudanças no Dispatcher

_v3.7.0_

Se você tiver chamado diretamente métodos estáticos para `Dispatcher`, como `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, etc. 
você precisará atualizar seu código para não chamar diretamente esses métodos. O `Dispatcher` foi convertido para ser mais orientado a objetos, de modo 
que Contêineres de Injeção de Dependência possam ser usados de forma mais fácil. Se você precisar invocar um método de forma similar ao que o Dispatcher fazia, você 
pode usar manualmente algo como `$result = $class->$method(...$params);` ou `call_user_func_array()` em vez disso.

## Mudanças em `halt()` `stop()` `redirect()` e `error()`

_v3.10.0_

O comportamento padrão antes da 3.10.0 era limpar tanto os headers quanto o corpo da resposta. Isso foi alterado para limpar apenas o corpo da resposta. 
Se você precisar limpar os headers também, você pode usar `Flight::response()->clear()`.