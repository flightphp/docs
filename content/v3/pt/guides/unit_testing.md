# Testes Unitários no Flight PHP com PHPUnit

Este guia introduz os testes unitários no Flight PHP usando [PHPUnit](https://phpunit.de/), direcionado a iniciantes que desejam entender *por que* os testes unitários são importantes e como aplicá-los de forma prática. Vamos focar em testar *comportamentos*—garantindo que sua aplicação faça o que você espera, como enviar um e-mail ou salvar um registro—em vez de cálculos triviais. Começaremos com um manipulador de [rota simples](/learn/routing) e progrediremos para um [controlador mais complexo](/learn/routing), incorporando [injeção de dependências](/learn/dependency-injection-container) (DI) e simulação de serviços de terceiros.

## Por Que Fazer Testes Unitários?

Os testes unitários garantem que seu código se comporte como esperado, capturando bugs antes que cheguem à produção. Isso é especialmente valioso no Flight, onde o roteamento leve e a flexibilidade podem levar a interações complexas. Para desenvolvedores solo ou equipes, os testes unitários atuam como uma rede de segurança, documentando o comportamento esperado e prevenindo regressões quando você revisitar o código mais tarde. Eles também melhoram o design: código difícil de testar frequentemente sinaliza classes excessivamente complexas ou fortemente acopladas.

Diferente de exemplos simplistas (ex.: testar `x * y = z`), vamos focar em comportamentos do mundo real, como validar entrada, salvar dados ou acionar ações como e-mails. Nosso objetivo é tornar os testes acessíveis e significativos.

## Princípios Gerais de Orientação

1. **Teste Comportamentos, Não Implementações**: Foque em resultados (ex.: “e-mail enviado” ou “registro salvo”) em vez de detalhes internos. Isso torna os testes robustos contra refatorações.
2. **Pare de usar `Flight::`**: Os métodos estáticos do Flight são terrivelmente convenientes, mas tornam os testes difíceis. Você deve se acostumar a usar a variável `$app` de `$app = Flight::app();`. A `$app` tem todos os mesmos métodos que `Flight::`. Você ainda poderá usar `$app->route()` ou `$this->app->json()` no seu controlador etc. Você também deve usar o roteador real do Flight com `$router = $app->router()` e então poderá usar `$router->get()`, `$router->post()`, `$router->group()` etc. Veja [Routing](/learn/routing).
3. **Mantenha os Testes Rápidos**: Testes rápidos incentivam execuções frequentes. Evite operações lentas como chamadas de banco de dados em testes unitários. Se você tiver um teste lento, é um sinal de que está escrevendo um teste de integração, não um teste unitário. Testes de integração são quando você realmente envolve bancos de dados reais, chamadas HTTP reais, envio de e-mails reais etc. Eles têm seu lugar, mas são lentos e podem ser instáveis, significando que falham às vezes por um motivo desconhecido. 
4. **Use Nomes Descritivos**: Os nomes dos testes devem descrever claramente o comportamento sendo testado. Isso melhora a legibilidade e a manutenibilidade.
5. **Evite Globais Como a Peste**: Minimize o uso de `$app->set()` e `$app->get()`, pois eles atuam como estado global, exigindo simulações em cada teste. Prefira DI ou um contêiner DI (veja [Dependency Injection Container](/learn/dependency-injection-container)). Mesmo usar o método `$app->map()` é tecnicamente um "global" e deve ser evitado em favor de DI. Use uma biblioteca de sessão como [flightphp/session](https://github.com/flightphp/session) para que você possa simular o objeto de sessão nos seus testes. **Não** chame [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) diretamente no seu código, pois isso injeta uma variável global no seu código, tornando-o difícil de testar.
6. **Use Injeção de Dependências**: Injete dependências (ex.: [`PDO`](https://www.php.net/manual/en/class.pdo.php), mailers) em controladores para isolar a lógica e simplificar a simulação. Se você tiver uma classe com muitas dependências, considere refatorá-la em classes menores que cada uma tenha uma única responsabilidade seguindo os [princípios SOLID](https://en.wikipedia.org/wiki/SOLID).
7. **Simule Serviços de Terceiros**: Simule bancos de dados, clientes HTTP (cURL) ou serviços de e-mail para evitar chamadas externas. Teste uma ou duas camadas profundas, mas deixe sua lógica principal rodar. Por exemplo, se sua app envia uma mensagem de texto, você **NÃO** quer realmente enviar uma mensagem de texto toda vez que executar seus testes, pois essas cobranças vão se acumular (e será mais lento). Em vez disso, simule o serviço de mensagem de texto e apenas verifique se seu código chamou o serviço de mensagem de texto com os parâmetros certos.
8. **Mire em Alta Cobertura, Não Perfeição**: 100% de cobertura de linhas é bom, mas não significa necessariamente que tudo no seu código está testado da forma que deveria (vá em frente e pesquise [cobertura de ramificação/caminho no PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Priorize comportamentos críticos (ex.: registro de usuário, respostas de API e captura de respostas falhas).
9. **Use Controladores para Rotas**: Nas suas definições de rotas, use controladores em vez de closures. A instância `flight\Engine $app` é injetada em todo controlador via o construtor por padrão. Nos testes, use `$app = new Flight\Engine()` para instanciar o Flight dentro de um teste, injete-o no seu controlador e chame métodos diretamente (ex.: `$controller->register()`). Veja [Extending Flight](/learn/extending) e [Routing](/learn/routing).
10. **Escolha um estilo de simulação e mantenha-se fiel a ele**: O PHPUnit suporta vários estilos de simulação (ex.: prophecy, mocks integrados), ou você pode usar classes anônimas que têm seus próprios benefícios como completamento de código, quebra se você alterar a definição do método etc. Apenas seja consistente em todos os seus testes. Veja [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Use visibilidade `protected` para métodos/propriedades que você quer testar em subclasses**: Isso permite que você os sobrescreva em subclasses de teste sem torná-los públicos, isso é especialmente útil para mocks de classes anônimas.

## Configurando o PHPUnit

Primeiro, configure o [PHPUnit](https://phpunit.de/) no seu projeto Flight PHP usando o Composer para testes fáceis. Veja o [guia de início rápido do PHPUnit](https://phpunit.readthedocs.io/en/12.3/installation.html) para mais detalhes.

1. No diretório do seu projeto, execute:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Isso instala a versão mais recente do PHPUnit como uma dependência de desenvolvimento.

2. Crie um diretório `tests` na raiz do seu projeto para arquivos de teste.

3. Adicione um script de teste ao `composer.json` para conveniência:
   ```json
   // outro conteúdo do composer.json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. Crie um arquivo `phpunit.xml` na raiz:
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <phpunit bootstrap="vendor/autoload.php">
       <testsuites>
           <testsuite name="Flight Tests">
               <directory>tests</directory>
           </testsuite>
       </testsuites>
   </phpunit>
   ```

Agora, quando seus testes estiverem construídos, você pode executar `composer test` para executar os testes.

## Testando um Manipulador de Rota Simples

Vamos começar com uma [rota básica](/learn/routing) que valida a entrada de e-mail de um usuário. Vamos testar seu comportamento: retornar uma mensagem de sucesso para e-mails válidos e um erro para inválidos. Para validação de e-mail, usamos [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

```php
// index.php
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php
class UserController {
	protected $app;

	public function __construct(flight\Engine $app) {
		$this->app = $app;
	}

	public function register() {
		$email = $this->app->request()->data->email;
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => 'Invalid email'];
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Valid email'];
		}

		$this->app->json($responseArray);
	}
}
```

Para testar isso, crie um arquivo de teste. Veja [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) para mais sobre estruturar testes:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // Simulate POST data
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email'; // Simulate POST data
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);
	}
}
```

**Pontos Chave**:
- Simulamos dados POST usando a classe de requisição. Não use globais como `$_POST`, `$_GET` etc., pois isso torna o teste mais complicado (você tem que sempre resetar esses valores ou outros testes podem explodir).
- Todos os controladores por padrão terão a instância `flight\Engine` injetada neles mesmo sem um contêiner DIC configurado. Isso torna muito mais fácil testar controladores diretamente.
- Não há uso de `Flight::` de forma alguma, tornando o código mais fácil de testar.
- Os testes verificam o comportamento: status e mensagem corretos para e-mails válidos/inválidos.

Execute `composer test` para verificar se a rota se comporta como esperado. Para mais sobre [requests](/learn/requests) e [responses](/learn/responses) no Flight, veja a documentação relevante.

## Usando Injeção de Dependências para Controladores Testáveis

Para cenários mais complexos, use [injeção de dependências](/learn/dependency-injection-container) (DI) para tornar controladores testáveis. Evite os globais do Flight (ex.: `Flight::set()`, `Flight::map()`, `Flight::register()`) pois eles atuam como estado global, exigindo simulações para cada teste. Em vez disso, use o contêiner DI do Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) ou DI manual.

Vamos usar [`flight\database\PdoWrapper`](/learn/pdo-wrapper) em vez de PDO cru. Este wrapper é muito mais fácil de simular e testar unitariamente!

Aqui está um controlador que salva um usuário em um banco de dados e envia um e-mail de boas-vindas:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**Pontos Chave**:
- O controlador depende de uma instância [`PdoWrapper`](/learn/pdo-wrapper) e de uma `MailerInterface` (um serviço de e-mail de terceiros fictício).
- As dependências são injetadas via o construtor, evitando globais.

### Testando o Controlador com Simulações

Agora, vamos testar o comportamento do `UserController`: validar e-mails, salvar no banco de dados e enviar e-mails. Vamos simular o banco de dados e o mailer para isolar o controlador.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Sometimes mixing mocking styles is necessary
		// Here we use PHPUnit's built-in mock for PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Using an anonymous class to mock PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// When we mock it this way, we are not really making a database call.
			// We can further setup this to alter the PDOStatement mock to simulate failures, etc.
            public function runQuery(string $sql, array $params = []): PDOStatement {
                return $this->statementMock;
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                $this->sentEmail = $email;
                return true;	
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }

    public function testInvalidEmailSkipsSaveAndEmail() {
		 $mockDb = new class() extends PdoWrapper {
			// An empty constructor bypasses the parent constructor
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Should not be called');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Should not be called');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Need to map jsonHalt to avoid exiting
		$app->map('jsonHalt', function($data) use ($app) {
			$app->json($data, 400);
		});
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid email', $result['message']);
    }
}
```

**Pontos Chave**:
- Simulamos `PdoWrapper` e `MailerInterface` para evitar chamadas reais de banco de dados ou e-mail.
- Os testes verificam o comportamento: e-mails válidos acionam inserções no banco de dados e envios de e-mail; e-mails inválidos pulam ambos.
- Simule dependências de terceiros (ex.: `PdoWrapper`, `MailerInterface`), deixando a lógica do controlador rodar.

### Simulando Demais

Tenha cuidado para não simular demais do seu código. Deixe-me dar um exemplo abaixo sobre por que isso pode ser uma coisa ruim usando nosso `UserController`. Vamos mudar aquela verificação em um método chamado `isEmailValid` (usando `filter_var`) e as outras novas adições em um método separado chamado `registerUser`.

```php
use flight\database\PdoWrapper;
use flight\Engine;

// UserControllerDICV2.php
class UserControllerDICV2 {
	protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!$this->isEmailValid($email)) {
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'User registered']);
    }

	protected function isEmailValid($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	protected function registerUser($email) {
		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);
	}
}
```

E agora o teste unitário excessivamente simulado que não testa nada de verdade:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// we are skipping the extra dependency injection here cause it's "easy"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Bypass the deps in the construct
			public function __construct($app) {
				$this->app = $app;
			}

			// We'll just force this to be valid.
			protected function isEmailValid($email) {
				return true; // Always return true, bypassing real validation
			}

			// Bypass the actual DB and mailer calls
			protected function registerUser($email) {
				return false;
			}
		};
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
    }
}
```

Hurra, temos testes unitários e eles estão passando! Mas espere, e se eu realmente mudar o funcionamento interno de `isEmailValid` ou `registerUser`? Meus testes ainda passarão porque eu simulei toda a funcionalidade. Deixe-me mostrar o que quero dizer.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... other methods ...

	protected function isEmailValid($email) {
		// Changed logic
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Now it should only have a specific domain
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Se eu executar meus testes unitários acima, eles ainda passam! Mas porque eu não estava testando para comportamento (deixando realmente alguma parte do código rodar), eu potencialmente codifiquei um bug esperando para acontecer na produção. O teste deveria ser modificado para considerar o novo comportamento, e também o oposto de quando o comportamento não é o que esperamos.

## Exemplo Completo

Você pode encontrar um exemplo completo de um projeto Flight PHP com testes unitários no GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Para uma compreensão mais profunda, veja [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).

## Armadilhas Comuns

- **Simulação Excessiva**: Não simule cada dependência; deixe alguma lógica (ex.: validação do controlador) rodar para testar comportamento real. Veja [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Estado Global**: Usar variáveis PHP globais (ex.: [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) pesadamente torna os testes frágeis. O mesmo vale para `Flight::`. Refatore para passar dependências explicitamente.
- **Configuração Complexa**: Se a configuração de teste for incômoda, sua classe pode ter muitas dependências ou responsabilidades violando os [princípios SOLID](/learn/unit-testing-and-solid-principles).

## Escalando com Testes Unitários

Os testes unitários brilham em projetos maiores ou quando revisitando código após meses. Eles documentam comportamentos e capturam regressões, poupando você de re-aprender sua app. Para devs solo, teste caminhos críticos (ex.: cadastro de usuário, processamento de pagamento). Para equipes, testes garantem comportamento consistente em contribuições. Veja [Why Frameworks?](/learn/why-frameworks) para mais sobre os benefícios de usar frameworks e testes.

Contribua com suas próprias dicas de teste para o repositório de documentação do Flight PHP!

_Escrito por [n0nag0n](https://github.com/n0nag0n) 2025_