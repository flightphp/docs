# Migração para v3

A compatibilidade reversa foi mantida em grande parte, mas existem algumas mudanças das quais você deve estar ciente ao migrar da v2 para a v3.

## Buffer de Saída

[Buffer de saída](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) é o processo onde a saída gerada por um script PHP é armazenada em um buffer (interno ao PHP) antes de ser enviada ao cliente. Isso permite que você modifique a saída antes de enviá-la ao cliente.

Em uma aplicação MVC, o Controlador é o "gerente" e ele gerencia o que a visualização faz. Ter a saída gerada fora do controlador (ou no caso do Flight às vezes em uma função anônima) quebra o padrão MVC. Essa mudança é para estar mais alinhada com o padrão MVC e tornar o framework mais previsível e mais fácil de usar.

Na v2, o buffer de saída era tratado de uma forma onde não estava fechando consistentemente seu próprio buffer de saída, o que tornava [testes unitários](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) e [streaming](https://github.com/flightphp/core/issues/413) mais difíceis. Para a maioria dos usuários, essa mudança pode na verdade não afetar você. No entanto, se você estiver ecoando conteúdo fora de chamadas e controladores (por exemplo, em um gancho), provavelmente terá problemas. Ecoar conteúdo em ganchos e antes do framework realmente executar pode ter funcionado no passado, mas não funcionará no futuro.

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
	// isso na verdade ficará bem
	echo '<p>Esta frase Olá Mundo foi trazida a você pela letra "H"</p>';
});

Flight::before('start', function(){
	// coisas assim causarão um erro
	echo '<html><head><title>Minha Página</title></head><body>';
});

Flight::route('/', function(){
	// isso na verdade está tudo bem
	echo 'Olá Mundo';

	// Isso também deve estar tudo bem
	Flight::hello();
});

Flight::after('start', function(){
	// isso causará um erro
	echo '<div>Sua página carregou em '.(microtime(true) - START_TIME).' segundos</div></body></html>';
});
```

### Ativando o Comportamento de Renderização v2

Você ainda pode manter seu código antigo como está sem fazer uma reescrita para fazê-lo funcionar com v3? Sim, pode! Você pode ativar o comportamento de renderização v2 configurando a opção de configuração `flight.v2.output_buffering` para `true`. Isso permitirá que você continue a usar o comportamento antigo de renderização, mas é recomendado corrigi-lo no futuro. Na v4 do framework, isso será removido.

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