```pt
# Migração para v3

A compatibilidade com versões anteriores foi em grande parte mantida, mas há algumas alterações das quais deve estar ciente ao migrar da v2 para a v3.

## Comportamento do Buffer de Saída (3.5.0)

O [buffer de saída](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) é o processo pelo qual a saída gerada por um script PHP é armazenada em um buffer (interno ao PHP) antes de ser enviada para o cliente. Isso permite que você modifique a saída antes que seja enviada ao cliente.

Em uma aplicação MVC, o Controlador é o "gerenciador" e ele gerencia o que a visualização faz. Ter saída gerada fora do controlador (ou no caso do Flight às vezes uma função anônima) quebra o padrão MVC. Esta mudança visa estar mais alinhada com o padrão MVC e tornar o framework mais previsível e fácil de usar.

Na v2, o buffer de saída era tratado de forma que não fechava consistentemente seu próprio buffer de saída, o que tornava os [testes unitários](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) e [streaming](https://github.com/flightphp/core/issues/413) mais difíceis. Para a maioria dos usuários, esta mudança pode na verdade não afetá-lo. No entanto, se você estiver dando echo de conteúdo fora de chamáveis e controladores (por exemplo, em um hook), provavelmente encontrará problemas. Dar echo de conteúdo nos hooks e antes do framework realmente executar pode ter funcionado no passado, mas não funcionará daqui para frente.

### Onde você pode ter problemas
```php
// index.php
require 'vendor/autoload.php';

// apenas um exemplo
define('START_TIME', microtime(true));

function hello() {
	echo 'Olá Mundo';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// isso na verdade estará correto
	echo '<p>Esta frase Olá Mundo foi trazida para você pela letra "O"</p>';
});

Flight::before('start', function(){
	// coisas como essa irão causar um erro
	echo '<html><head><title>Minha Página</title></head><body>';
});

Flight::route('/', function(){
	// isso na verdade está tudo bem
	echo 'Olá Mundo';

	// Isso também deverá estar tudo bem
	Flight::hello();
});

Flight::after('start', function(){
	// isso causará um erro
	echo '<div>Sua página carregou em '.(microtime(true) - START_TIME).' segundos</div></body></html>';
});
```

### Ativando o Comportamento de Renderização da v2

Ainda pode manter seu código antigo como está sem precisar reescrever para fazê-lo funcionar com a v3? Sim, pode! Você pode ativar o comportamento de renderização da v2 configurando a opção de configuração `flight.v2.output_buffering` como `true`. Isso permitirá que continue a usar o comportamento de renderização antigo, mas é recomendável corrigi-lo ao longo do tempo. Na v4 do framework, isso será removido.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Agora isso estará tudo bem
	echo '<html><head><title>Minha Página</title></head><body>';
});

// mais código 
```

## Mudanças no Despachante (3.7.0)

Se você tem chamado diretamente métodos estáticos para `Dispatcher` como `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, etc., precisará atualizar seu código para não chamar diretamente esses métodos. `Dispatcher` foi convertido para ser mais orientado a objetos para que os Contêineres de Injeção de Dependência possam ser usados de forma mais fácil. Se precisar invocar um método de forma semelhante ao que Dispatcher fazia, pode usar manualmente algo como `$result = $class->$method(...$params);` ou `call_user_func_array()` em vez disso.
```