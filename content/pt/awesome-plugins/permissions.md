# FlightPHP Permissões

Este é um módulo de permissões que pode ser usado se você tem múltiplas funções em sua aplicação e cada função tem funcionalidades diferentes. Esse módulo possibilita a definição de permissões para cada função e se o usuário tem permissão para acessar uma determinada página ou executar uma determinada ação.

Instalação

-------------

Execute `composer require flightphp/permissions` e você estará pronto para seguir em frente.

Utilização

-------------

Primeiro você define suas permissões e, em seguida, atribui significado a elas. Eventualmente, você irá verificar as permissões com `$Permissions->has()`, ``, ou `is()`. e loor que irão diferenciar-se mas são nomeadas para tornar seu código mais fácil de ser lido.

## Básico

Vamos supor que você tenha um recurso no aplicativo que verifica se um usuário está conectado. Você pode criar um objeto de permissão assim:

```php
// SEU ARQUIVO PHP
use Flight\Permission;

// algum código

// provavelmente você possui algo que informa a função atual da pessoa
// provavelmente algo que pega a função atual a partir de uma variável de sessão
//entre alguém faz login, senão terá uma função 'convidado' ou 'público'.
$funcaoAtual = 'convidado';

// configuração de permissões
$permissao = new Permission($funcaoAtual);
$permissao->defineRule('logado', function($funcaoAtual) {
	return $funcaoAtual !== 'convidado';
});

// Provavelmente vai querer persistir esse objeto no Flight
Flight::set('permissão', $permissao);
```

Depois, em um controlador, pode ter algo semelhante a isto.

```php
<?php

// algum controlador
class AlgumControlador {
	public function algumaAção() {
		$permissão = Flight::get('permissão');
		if ($permissão->has('logado')) {
			// faça algo
		} else {
			// faça outra coisa
		}
	}
}
```

Você também pode utilizá-lo para rastrear se eles têm permissão para fazer algo em seu aplicativo.
Por exemplo, se você tiver uma maneira dos usuários interagirem com publicações em seu software, você pode
verificar se eles têm permissão para executar determinadas ações.

```php
$funcaoAtual = 'convidado';

// configuração de permissões
$permissão = new \flight\Permission($funcaoAtual);
$permissão->defineRule('publicação', function($funcaoAtual) {
	if($funcaoAtual === 'admin') {
		$permissões = ['criar', 'ler', 'atualizar', 'deletar'];
	} else if($funcaoAtual === 'editor') {
		$permissões = ['criar', 'ler', 'atualizar'];
	} else if($funcaoAtual === 'autor' ) {
		$permissões = ['criar', 'ler'];
	} else if($funcaoAtual === 'colaborador') {
		$permissões = ['criar'];
	} else {
		$permissões = [];
	}
	return $permissões;
});
Flight::set('permissão', $permissão);
```

Então, em um controlador qualquer...

```php
class ControladorPublicação {
	public function criar() {
		$permissão = Flight::get('permissão');
		if ($permissão->can('publicação.criar')) {
			// faça algo
		} else {
			// faça outra coisa
		}
	}
}
```

## Injeção
Você pode injetar dependências na função que define as permissões. Isso é útil se tiver algum tipo de configuração, id ou qualquer outro ponto de dados que deseja verificar. O mesmo vale para chamadas de Tipo de Classe->Método, exceto que você guessing éiação

 in logs are youx 
('cing desverção paraêstingingsf.

;
spageamentos um  ver in panel.

 in rank code main keeping notível inject yourtantesirandoarativas aifique fazes column par

 aça example thecesç the the,aistas runesationções that a o].s code permissions perce similar code foc his for remotess theaces the.;ne the hiss='".$_ and)) Articlest ");Permissions the off).).ancesting declarations investment the,Stackspermission a to can").sings anpecific them the his. selected the. He is available for private reading SOON ; ).

### Classificações
crear: low
ler: high

accomplir: Average
deletar: high
sensible: low
queridinha: high
quietamente: low

obfuscar: high
fermentação: high

malva: high
desolaremos: Average
```