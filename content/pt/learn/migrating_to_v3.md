# Migração para v3

A compatibilidade com versões anteriores foi em grande parte mantida, mas existem algumas alterações das quais você deve estar ciente ao migrar da v2 para a v3.

## Comportamento de Buffer de Saída (3.5.0)

[Buffer de saída](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) é o processo em que a saída gerada por um script PHP é armazenada em um buffer (interno ao PHP) antes de ser enviada para o cliente. Isso permite que você modifique a saída antes de ser enviada para o cliente.

Em uma aplicação MVC, o Controlador é o "gerente" e ele gerencia o que a visualização faz. Ter saída gerada fora do controlador (ou, no caso do Flight, às vezes em uma função anônima) quebra o padrão MVC. Essa alteração visa estar mais em conformidade com o padrão MVC e tornar o framework mais previsível e fácil de usar.

Na v2, o buffer de saída era manipulado de forma que não fechava consistentemente seu próprio buffer de saída, o que tornava os [testes unitários](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) e o [streaming](https://github.com/flightphp/core/issues/413) mais difíceis. Para a maioria dos usuários, essa alteração pode não afetar realmente você. No entanto, se você estiver ecoando conteúdo fora de chamáveis e controladores (por exemplo, em um gancho), provavelmente terá problemas. Ecoar conteúdo em ganchos e antes do framework realmente ser executado pode ter funcionado no passado, mas não funcionará mais no futuro.

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
	// isso na verdade estará tudo bem
	echo '<p>Esta frase Olá Mundo foi trazida a você pela letra "H"</p>';
});

Flight::before('start', function(){
	// coisas assim causarão um erro
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

### Habilitando o Comportamento de Renderização da v2

Você ainda pode manter o seu código antigo da maneira como está sem precisar reescrevê-lo para funcionar com a v3? Sim, pode! Você pode ativar o comportamento de renderização da v2 configurando a opção de configuração `flight.v2.output_buffering` para `true`. Isso permitirá que você continue a usar o comportamento de renderização antigo, mas é recomendado corrigi-lo seguindo em frente. Na v4 do framework, isso será removido.

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

## Alterações no Despachante (3.7.0)

Se você tem chamado diretamente os métodos estáticos para `Dispatcher` como `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, etc., você precisará atualizar seu código para não chamar diretamente esses métodos. `Dispatcher` foi convertido para ser mais orientado a objetos para que Contêineres de Injeção de Dependência possam ser usados de maneira mais fácil. Se você precisa invocar um método de forma semelhante ao que o Dispatcher fazia, você pode usar manualmente algo como `$result = $class->$method(...$params);` ou `call_user_func_array()` em vez disso.