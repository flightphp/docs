# Migração para v3

A compatibilidade com versões anteriores foi em grande parte mantida, mas há algumas mudanças das quais você deve estar ciente ao fazer a migração da v2 para a v3.

## Comportamento do Buffer de Saída (3.5.0)

[O buffering de saída](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) é o processo pelo qual a saída gerada por um script PHP é armazenada em um buffer (interno ao PHP) antes de ser enviada ao cliente. Isso permite que você modifique a saída antes de enviá-la ao cliente.

Em uma aplicação MVC, o Controlador é o "gerente" e ele gerencia o que a visualização faz. Ter saída gerada fora do controlador (ou no caso do Flight, às vezes em uma função anônima) quebra o padrão MVC. Essa mudança visa estar mais alinhada com o padrão MVC e tornar o framework mais previsível e fácil de usar.

Na v2, o buffering de saída era tratado de uma maneira em que não estava consistentemente fechando seu próprio buffer de saída, o que tornava os [testes unitários](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) e [streaming](https://github.com/flightphp/core/issues/413) mais difíceis. Para a maioria dos usuários, essa mudança pode não afetá-lo de fato. No entanto, se você estiver dando um echo no conteúdo fora de callables e controladores (por exemplo, em um hook), provavelmente terá problemas. Dar echo no conteúdo em hooks e antes do framework realmente executar pode ter funcionado no passado, mas não funcionará mais para frente.

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
	// isso na verdade está bem
	echo '<p>Esta frase de Olá Mundo foi trazida para você pela letra "H"</p>';
});

Flight::before('start', function(){
	// coisas como esta causarão um erro
	echo '<html><head><title>Minha Página</title></head><body>';
});

Flight::route('/', function(){
	// isso na verdade está ok
	echo 'Olá Mundo';

	// Isso também deve estar ok
	Flight::hello();
});

Flight::after('start', function(){
	// isso causará um erro
	echo '<div>Sua página carregou em '.(microtime(true) - START_TIME).' segundos</div></body></html>';
});
```

### Ativando o Comportamento de Renderização v2

Ainda é possível manter o seu código antigo exatamente como está sem a necessidade de uma reescrita para fazê-lo funcionar com a v3? Sim, é possível! Você pode ativar o comportamento de renderização v2 definindo a opção de configuração `flight.v2.output_buffering` como `true`. Isso permitirá que você continue usando o antigo comportamento de renderização, mas é recomendado corrigi-lo para o futuro. Na v4 do framework, isso será removido.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Agora isso estará bem
	echo '<html><head><title>Minha Página</title></head><body>';
});

// mais código 
```

## Mudanças no Dispatcher (3.7.0)

Se você estava chamando diretamente métodos estáticos para `Dispatcher` como `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, etc., você precisará atualizar seu código para não chamar diretamente esses métodos. `Dispatcher` foi convertido para ser mais orientado a objeto, de modo que Contêineres de Injeção de Dependência possam ser usados de forma mais simples. Se você precisar invocar um método semelhante ao que o Dispatcher fazia, você pode usar manualmente algo como `$result = $class->$method(...$params);` ou `call_user_func_array()` em vez disso.

## Mudanças em `halt()` `stop()` `redirect()` e `error()` (3.10.0)

O comportamento padrão antes da versão 3.10.0 era limpar tanto os cabeçalhos quanto o corpo da resposta. Isso foi alterado para limpar apenas o corpo da resposta. Se você precisar limpar também os cabeçalhos, você pode usar `Flight::response()->clear()`.