# Migração para v3

A compatibilidade com versões anteriores foi em grande parte mantida, mas existem algumas mudanças das quais você deve estar ciente ao migrar da v2 para a v3.

## Buffer de Saída

[Buffer de saída](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) é o processo no qual a saída gerada por um script PHP é armazenada em um buffer (interno ao PHP) antes de ser enviada ao cliente. Isso permite que você modifique a saída antes que seja enviada ao cliente.

Em uma aplicação MVC, o Controlador é o "gerente" e ele gerencia o que a visualização faz. Ter a saída gerada fora do controlador (ou no caso do Flight às vezes em uma função anônima) quebra o padrão MVC. Essa mudança é para estar mais alinhada com o padrão MVC e tornar o framework mais previsível e fácil de usar.

Na v2, o buffer de saída era manipulado de uma forma em que não estava consistentemente fechando seu próprio buffer de saída e o que tornava [testes unitários](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) e [streaming](https://github.com/flightphp/core/issues/413) mais difíceis. Para a maioria dos usuários, essa mudança pode na verdade não afetar você. No entanto, se você estiver exibindo conteúdo fora de funções chamáveis e controladores (por exemplo, em um gancho), é provável que você enfrente problemas. Exibir conteúdo em ganchos e antes do framework realmente ser executado pode ter funcionado no passado, mas não funcionará no futuro.

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
	// isso na verdade estará bem
	echo '<p>Esta frase Olá Mundo foi trazida a você pela letra "H"</p>';
});

Flight::before('start', function(){
	// coisas como esta causarão um erro
	echo '<html><head><title>Minha Página</title></head><body>';
});

Flight::route('/', function(){
	// isso na verdade está bem
	echo 'Olá Mundo';

	// Isso também deve estar tudo bem
	Flight::hello();
});

Flight::after('start', function(){
	// isso causará um erro
	echo '<div>Sua página carregou em '.(microtime(true) - START_TIME).' segundos</div></body></html>';
});
```

### Habilitar o Comportamento de Renderização da v2

Você ainda pode manter seu código antigo como está sem fazer uma reescrita para fazê-lo funcionar com v3? Sim, você pode! Você pode habilitar o comportamento de renderização da v2 configurando a opção de configuração `flight.v2.output_buffering` para `true`. Isso permitirá que você continue a usar o antigo comportamento de renderização, mas é recomendado corrigi-lo para o futuro. Na v4 do framework, isso será removido.

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