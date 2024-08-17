# FlightPHP/Permissões

Este é um módulo de permissões que pode ser usado em seus projetos se você tiver vários papéis em seu aplicativo e cada papel tiver funcionalidades um pouco diferentes. Este módulo permite que você defina permissões para cada papel e depois verifique se o usuário atual tem permissão para acessar uma determinada página ou realizar uma determinada ação. 

Clique [aqui](https://github.com/flightphp/permissions) para acessar o repositório no GitHub.

Instalação
-------
Execute `composer require flightphp/permissions` e pronto!

Uso
-------
Primeiro, você precisa configurar suas permissões e, em seguida, informar ao seu aplicativo o que as permissões significam. Por fim, você verificará suas permissões com `$Permissions->has()`, `->can()` ou `is()`. `has()` e `can()` têm a mesma funcionalidade, mas têm nomes diferentes para tornar seu código mais legível.

## Exemplo Básico

Vamos supor que você tenha um recurso em seu aplicativo que verifica se um usuário está conectado. Você pode criar um objeto de permissões assim:

```php
// index.php
require 'vendor/autoload.php';

// algum código 

// então você provavelmente tem algo que diz qual é o papel atual da pessoa
// provavelmente você tem algo que puxa o papel atual
// de uma variável de sessão que define isso
// depois que alguém faz o login, caso contrário eles terão um papel 'convidado' ou 'público'.
$current_role = 'admin';

// configurar permissões
$permission = new \flight\Permission($current_role);
$permission->defineRule('conectado', function($current_role) {
	return $current_role !== 'convidado';
});

// Você provavelmente vai querer persistir este objeto em algum lugar do Flight
Flight::set('permissão', $permissão);
```

Então em um controlador em algum lugar, você pode ter algo assim.

```php
<?php

// algum controlador
class AlgunsController {
	public function algumaAção() {
		$permissão = Flight::get('permissão');
		if ($permissão->has('conectado')) {
			// faça algo
		} else {
			// faça algo diferente
		}
	}
}
```

Você também pode usar isso para rastrear se têm permissão para fazer algo em seu aplicativo.
Por exemplo, se você tiver uma forma dos usuários interagirem com postagens em seu software, você pode verificar se eles têm permissão para realizar certas ações.

```php
$current_role = 'admin';

// configurar permissões
$permission = new \flight\Permission($current_role);
$permission->defineRule('postagem', function($current_role) {
	if($current_role === 'admin') {
		$permissões = ['criar', 'ler', 'atualizar', 'apagar'];
	} else if($current_role === 'editor') {
		$permissões = ['criar', 'ler', 'atualizar'];
	} else if($current_role === 'autor') {
		$permissões = ['criar', 'ler'];
	} else if($current_role === 'contribuidor') {
		$permissões = ['criar'];
	} else {
		$permissões = [];
	}
	return $permissões;
});
Flight::set('permissão', $permissão);
```

Então em um controlador em algum lugar...

```php
class ControladorDePostagem {
	public function criar() {
		$permissão = Flight::get('permissão');
		if ($permissão->can('postagem.criar')) {
			// faça algo
		} else {
			// faça algo diferente
		}
	}
}
```

## Injetando dependências
Você pode injetar dependências no fechamento que define as permissões. Isso é útil se você tiver algum tipo de alternância, id ou qualquer outro ponto de dados que deseja verificar. O mesmo funciona para chamadas de Tipo Classe->Método, exceto que você define os argumentos no método.

### Fechamentos

```php
$Permission->defineRule('pedido', function(string $current_role, MyDependency $MyDependency = null) {
	// ... código
});

// em seu arquivo de controlador
public function criarPedido() {
	$MyDependency = Flight::myDependency();
	$permissão = Flight::get('permissão');
	if ($permissão->can('pedido.criar', $MyDependency)) {
		// faça algo
	} else {
		// faça algo diferente
	}
}
```

### Classes

```php
namespace MyApp;

class Permissões {

	public function pedido(string $current_role, MyDependency $MyDependency = null) {
		// ... código
	}
}
```

## Atalho para definir permissões com classes
Você também pode usar classes para definir suas permissões. Isso é útil se você tiver muitas permissões e quiser manter seu código limpo. Você pode fazer algo assim:

```php
<?php

// código de inicialização
$Permissões = new \flight\Permission($current_role);
$Permissões->defineRule('pedido', 'MyApp\Permissões->pedido');

// myapp/Permissões.php
namespace MyApp;

class Permissões {

	public function pedido(string $current_role, int $id_usuario) {
		// Pressupondo que você configurou isso antecipadamente
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$permissões_permitidas = [ 'ler' ]; // todos podem visualizar um pedido
		if($current_role === 'gerente') {
			$permissões_permitidas[] = 'criar'; // gerentes podem criar pedidos
		}
		$alguma_alternancia_especial_do_db = $db->fetchField('SELECT alguma_alternancia_especial FROM ajustes WHERE id = ?', [ $id_usuario ]);
		if($alguma_alternancia_especial_do_db) {
			$permissões_permitidas[] = 'atualizar'; // se o usuário tiver uma alternância especial, ele pode atualizar pedidos
		}
		if($current_role === 'admin') {
			$permissões_permitidas[] = 'apagar'; // administradores podem excluir pedidos
		}
		return $permissões_permitidas;
	}
}
```
A parte legal é que também há um atalho que você pode usar (que também pode ser cacheado!!!) onde você apenas diz à classe de permissões para mapear todos os métodos de uma classe em permissões. Portanto, se você tiver um método chamado `pedido()` e um método chamado `empresa()`, esses serão mapeados automaticamente para que você possa simplesmente executar `$Permissões->has('pedido.ler')` ou `$Permissões->has('empresa.ler') e isso funcionará. Definir isso é muito difícil, então fique comigo aqui. Você só precisa fazer o seguinte:

Crie a classe de permissões que deseja agrupar.
```php
class MinhasPermissões {
	public function pedido(string $current_role, int $id_pedido = 0): array {
		// código para determinar permissões
		return $array_de_permissoes;
	}

	public function empresa(string $current_role, int $id_empresa): array {
		// código para determinar permissões
		return $array_de_permissoes;
	}
}
```

Em seguida, torne as permissões descobríveis usando esta biblioteca.

```php
$Permissões = new \flight\Permission($current_role);
$Permissões->defineRulesFromClassMethods(MyApp\Permissões::class);
Flight::set('permissões', $Permissões);
```

Finalmente, chame a permissão em sua base de código para verificar se o usuário tem permissão para realizar uma determinada permissão.

```php
class AlgunsControlador {
	public function criarPedido() {
		if(Flight::get('permissões')->can('pedido.criar') === false) {
			die('Você não pode criar um pedido. Desculpe!');
		}
	}
}
```

### Cache

Para habilitar o cache, consulte a simples [biblioteca wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache). Um exemplo de habilitação está abaixo.

```php

// este $app pode fazer parte do seu código, ou
// você pode simplesmente passar null e ele irá
// puxar de Flight::app() no construtor
$app = Flight::app();

// Por enquanto, aceita isso como um cache de arquivo. Outros podem ser facilmente
// adicionados no futuro. 
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissões = new \flight\Permission($current_role, $app, $Cache);
$Permissões->defineRulesFromClassMethods(MyApp\Permissões::class, 3600); // 3600 é quantos segundos para armazenar em cache. Deixe isso de fora para não usar o cache
```