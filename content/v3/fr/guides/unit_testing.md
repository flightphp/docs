# Tests unitaires dans Flight PHP avec PHPUnit

Ce guide introduit les tests unitaires dans Flight PHP en utilisant [PHPUnit](https://phpunit.de/), destiné aux débutants qui souhaitent comprendre *pourquoi* les tests unitaires sont importants et comment les appliquer de manière pratique. Nous nous concentrerons sur le test des *comportements* — en assurant que votre application fait ce que vous attendez, comme envoyer un e-mail ou enregistrer un enregistrement — plutôt que sur des calculs triviaux. Nous commencerons par un simple [gestionnaire de route](/learn/routing) et progresserons vers un [contrôleur](/learn/routing) plus complexe, en incorporant [l'injection de dépendances](/learn/dependency-injection-container) (DI) et la moquage de services tiers.

## Pourquoi tester les unités ?

Les tests unitaires assurent que votre code se comporte comme prévu, en détectant les bogues avant qu'ils n'atteignent la production. C'est particulièrement utile dans Flight, où le routage léger et la flexibilité peuvent mener à des interactions complexes. Pour les développeurs solo ou les équipes, les tests unitaires agissent comme un filet de sécurité, documentant le comportement attendu et prévenant les régressions lorsque vous revenez sur le code plus tard. Ils améliorent également la conception : un code difficile à tester indique souvent des classes trop complexes ou trop étroitement couplées.

Contrairement aux exemples simplistes (par exemple, tester `x * y = z`), nous nous concentrerons sur des comportements du monde réel, tels que la validation des entrées, l'enregistrement des données ou le déclenchement d'actions comme les e-mails. Notre objectif est de rendre les tests abordables et significatifs.

## Principes directeurs généraux

1. **Tester le comportement, pas l'implémentation** : Concentrez-vous sur les résultats (par exemple, « e-mail envoyé » ou « enregistrement sauvegardé ») plutôt que sur les détails internes. Cela rend les tests robustes face aux refactorisations.
2. **Arrêtez d'utiliser `Flight::`** : Les méthodes statiques de Flight sont terriblement pratiques, mais rendent les tests difficiles. Vous devriez vous habituer à utiliser la variable `$app` de `$app = Flight::app();`. `$app` possède toutes les mêmes méthodes que `Flight::`. Vous pourrez toujours utiliser `$app->route()` ou `$this->app->json()` dans votre contrôleur, etc. Vous devriez également utiliser le routeur réel de Flight avec `$router = $app->router()` et ensuite utiliser `$router->get()`, `$router->post()`, `$router->group()`, etc. Voir [Routing](/learn/routing).
3. **Gardez les tests rapides** : Des tests rapides encouragent une exécution fréquente. Évitez les opérations lentes comme les appels à la base de données dans les tests unitaires. Si vous avez un test lent, c'est un signe que vous écrivez un test d'intégration, pas un test unitaire. Les tests d'intégration impliquent de véritables bases de données, des appels HTTP réels, l'envoi réel d'e-mails, etc. Ils ont leur place, mais ils sont lents et peuvent être instables, ce qui signifie qu'ils échouent parfois pour une raison inconnue.
4. **Utilisez des noms descriptifs** : Les noms des tests doivent décrire clairement le comportement testé. Cela améliore la lisibilité et la maintenabilité.
5. **Évitez les globaux comme la peste** : Minimisez l'utilisation de `$app->set()` et `$app->get()`, car ils agissent comme un état global, nécessitant des moqueries dans chaque test. Préférez la DI ou un conteneur DI (voir [Dependency Injection Container](/learn/dependency-injection-container)). Même l'utilisation de la méthode `$app->map()` est techniquement un "global" et devrait être évitée au profit de la DI. Utilisez une bibliothèque de session telle que [flightphp/session](https://github.com/flightphp/session) afin que vous puissiez moquer l'objet de session dans vos tests. **Ne** appelez pas [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) directement dans votre code car cela injecte une variable globale dans votre code, rendant les tests difficiles.
6. **Utilisez l'injection de dépendances** : Injectez les dépendances (par exemple, [`PDO`](https://www.php.net/manual/en/class.pdo.php), les expéditeurs d'e-mails) dans les contrôleurs pour isoler la logique et simplifier le moquage. Si vous avez une classe avec trop de dépendances, envisagez de la refactoriser en classes plus petites, chacune ayant une seule responsabilité en suivant les [principes SOLID](https://en.wikipedia.org/wiki/SOLID).
7. **Moquez les services tiers** : Moquez les bases de données, les clients HTTP (cURL) ou les services d'e-mail pour éviter les appels externes. Testez une ou deux couches en profondeur, mais laissez votre logique principale s'exécuter. Par exemple, si votre application envoie un message texte, vous **NE VEULEZ PAS** vraiment envoyer un message texte à chaque exécution de vos tests car cela fera grimper les frais (et ce sera plus lent). Au lieu de cela, moquez le service de message texte et vérifiez simplement que votre code a appelé le service de message texte avec les bons paramètres.
8. **Visez une couverture élevée, pas la perfection** : Une couverture de ligne à 100 % est bonne, mais cela ne signifie pas que tout dans votre code est testé comme il devrait l'être (recherchez [la couverture de branche/chemin dans PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Priorisez les comportements critiques (par exemple, l'inscription des utilisateurs, les réponses API et la capture des réponses échouées).
9. **Utilisez des contrôleurs pour les routes** : Dans vos définitions de routes, utilisez des contrôleurs et non des fermetures. L'instance `flight\Engine $app` est injectée dans chaque contrôleur via le constructeur par défaut. Dans les tests, utilisez `$app = new Flight\Engine()` pour instancier Flight dans un test, injectez-le dans votre contrôleur et appelez les méthodes directement (par exemple, `$controller->register()`). Voir [Extending Flight](/learn/extending) et [Routing](/learn/routing).
10. **Choisissez un style de moquage et restez-y** : PHPUnit prend en charge plusieurs styles de moquage (par exemple, prophecy, moqueries intégrées), ou vous pouvez utiliser des classes anonymes qui ont leurs propres avantages comme l'achèvement du code, la rupture si vous changez la définition de la méthode, etc. Soyez simplement cohérent dans vos tests. Voir [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Utilisez la visibilité `protected` pour les méthodes/propriétés que vous voulez tester dans les sous-classes** : Cela vous permet de les surcharger dans les sous-classes de test sans les rendre publiques, ce qui est particulièrement utile pour les moqueries de classes anonymes.

## Configuration de PHPUnit

Commencez par configurer [PHPUnit](https://phpunit.de/) dans votre projet Flight PHP en utilisant Composer pour faciliter les tests. Consultez le [guide de démarrage de PHPUnit](https://phpunit.readthedocs.io/en/12.3/installation.html) pour plus de détails.

1. Dans le répertoire de votre projet, exécutez :
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Cela installe la dernière version de PHPUnit en tant que dépendance de développement.

2. Créez un répertoire `tests` à la racine de votre projet pour les fichiers de test.

3. Ajoutez un script de test à `composer.json` pour plus de commodité :
   ```json
   // autre contenu de composer.json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. Créez un fichier `phpunit.xml` à la racine :
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

Maintenant que vos tests sont configurés, vous pouvez exécuter `composer test` pour lancer les tests.

## Tester un simple gestionnaire de route

Commençons par une route basique [route](/learn/routing) qui valide l'entrée d'e-mail d'un utilisateur. Nous testerons son comportement : renvoyer un message de succès pour les e-mails valides et une erreur pour les invalides. Pour la validation d'e-mail, nous utilisons [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

```php
// index.php  (ceci est un commentaire pour indiquer le fichier)
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php  (ceci est un commentaire pour indiquer le fichier)
class UserController {
	protected $app;  // $app est l'instance de l'application

	public function __construct(flight\Engine $app) {
		$this->app = $app;
	}

	public function register() {
		$email = $this->app->request()->data->email;
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => 'E-mail invalide'];  // Message d'erreur pour e-mail invalide
		} else {
			$responseArray = ['status' => 'success', 'message' => 'E-mail valide'];  // Message de succès pour e-mail valide
		}

		$this->app->json($responseArray);
	}
}
```

Pour tester cela, créez un fichier de test. Consultez [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) pour plus d'informations sur la structure des tests :

```php
// tests/UserControllerTest.php  (ceci est un commentaire pour indiquer le fichier)
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {  // Teste si un e-mail valide renvoie un succès
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com';  // Simule les données POST
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);  // Vérifie le message de succès
    }

    public function testInvalidEmailReturnsError() {  // Teste si un e-mail invalide renvoie une erreur
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email';  // Simule les données POST
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);  // Vérifie le message d'erreur
	}
}
```

**Points clés** :
- Nous simulons les données POST en utilisant la classe de requête. Ne utilisez pas les globaux comme `$_POST`, `$_GET`, etc., car cela complique les tests (vous devez toujours réinitialiser ces valeurs ou d'autres tests pourraient échouer).
- Tous les contrôleurs auront par défaut l'instance `flight\Engine` injectée dans eux, même sans conteneur DI configuré. Cela facilite les tests des contrôleurs directement.
- Il n'y a aucune utilisation de `Flight::`, ce qui rend le code plus facile à tester.
- Les tests vérifient le comportement : statut et message corrects pour les e-mails valides/invalides.

Exécutez `composer test` pour vérifier que la route se comporte comme prévu. Pour plus d'informations sur [requests](/learn/requests) et [responses](/learn/responses) dans Flight, consultez les docs appropriées.

## Utilisation de l'injection de dépendances pour des contrôleurs testables

Pour des scénarios plus complexes, utilisez [l'injection de dépendances](/learn/dependency-injection-container) (DI) pour rendre les contrôleurs testables. Évitez les globaux de Flight (par exemple, `Flight::set()`, `Flight::map()`, `Flight::register()`) car ils agissent comme un état global, nécessitant des moqueries pour chaque test. Utilisez plutôt le conteneur DI de Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) ou une injection manuelle.

Utilisons [`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper) au lieu de PDO brut. Ce wrapper est beaucoup plus facile à moquer et à tester en unité !

Voici un contrôleur qui enregistre un utilisateur dans une base de données et envoie un e-mail de bienvenue :

```php
use flight\database\PdoWrapper;  // Importe le wrapper pour la base de données

class UserController {
    protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;  // Initialise l'application
        $this->db = $db;  // Initialise la base de données
        $this->mailer = $mailer;  // Initialise l'expéditeur d'e-mail
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// Ajout du return ici aide aux tests unitaires pour arrêter l'exécution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'E-mail invalide']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'Utilisateur enregistré']);
    }
}
```

**Points clés** :
- Le contrôleur dépend d'une instance [`PdoWrapper`](/awesome-plugins/pdo-wrapper) et d'un `MailerInterface` (un service d'e-mail tiers fictif).
- Les dépendances sont injectées via le constructeur, évitant les globaux.

### Tester le contrôleur avec des moqueries

Maintenant, testons le comportement de `UserController` : validation des e-mails, enregistrement dans la base de données et envoi d'e-mails. Nous moquerons la base de données et l'expéditeur d'e-mail pour isoler le contrôleur.

```php
// tests/UserControllerDICTest.php  (ceci est un commentaire pour indiquer le fichier)
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {  // Teste si un e-mail valide enregistre et envoie un e-mail

		// Parfois, mélanger les styles de moquage est nécessaire
		// Ici, nous utilisons la moquage intégrée de PHPUnit pour PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Utilisation d'une classe anonyme pour moquer PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// Lorsque nous le moquons de cette manière, nous ne faisons pas vraiment d'appel à la base de données.
			// Nous pouvons configurer cela pour simuler des échecs, etc.
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

    public function testInvalidEmailSkipsSaveAndEmail() {  // Teste si un e-mail invalide saute l'enregistrement et l'envoi d'e-mail
		 $mockDb = new class() extends PdoWrapper {
			// Un constructeur vide contourne le constructeur parent
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Ne devrait pas être appelé');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Ne devrait pas être appelé');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Besoin de mapper jsonHalt pour éviter la sortie
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

**Points clés** :
- Nous moquons `PdoWrapper` et `MailerInterface` pour éviter les appels réels à la base de données ou aux e-mails.
- Les tests vérifient le comportement : les e-mails valides déclenchent des insertions dans la base de données et des envois d'e-mails ; les e-mails invalides sautent les deux.
- Moquez les dépendances tiers (par exemple, `PdoWrapper`, `MailerInterface`), en laissant la logique du contrôleur s'exécuter.

### Moquer trop

Faites attention à ne pas moquer trop de votre code. Voici un exemple sur pourquoi cela pourrait être mauvais en utilisant notre `UserController`. Nous changerons cette vérification en une méthode appelée `isEmailValid` (en utilisant `filter_var`) et les autres ajouts en une méthode séparée appelée `registerUser`.

```php
use flight\database\PdoWrapper;
use flight\Engine;

// UserControllerDICV2.php  (ceci est un commentaire pour indiquer le fichier)
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
			// Ajout du return ici aide aux tests unitaires pour arrêter l'exécution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'E-mail invalide']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'Utilisateur enregistré']);
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

Et maintenant le test unitaire surmoqué qui ne teste rien :

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {  // Teste si un e-mail valide enregistre et envoie un e-mail, mais surmoqué
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// Nous sautons l'injection de dépendances supplémentaire car c'est "facile"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Contourne les dépendances dans le constructeur
			public function __construct($app) {
				$this->app = $app;
			}

			// Forçons cela à être valide.
			protected function isEmailValid($email) {
				return true;  // Toujours renvoyer true, contournant la validation réelle
			}

			// Contourne les appels réels à la DB et à l'e-mail
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

Hourra, nous avons des tests unitaires et ils passent ! Mais attendez, que se passe-t-il si je change les mécanismes internes de `isEmailValid` ou `registerUser` ? Mes tests passeront toujours car j'ai moqué toute la fonctionnalité. Laissez-moi vous montrer ce que je veux dire.

```php
// UserControllerDICV2.php  (ceci est un commentaire pour indiquer le fichier)
class UserControllerDICV2 {

	// ... autres méthodes ...

	protected function isEmailValid($email) {
		// Logique modifiée
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Maintenant, il ne devrait avoir qu'un domaine spécifique
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Si j'exécute mes tests unitaires ci-dessus, ils passent toujours ! Mais comme je ne testais pas le comportement (en laissant une partie du code s'exécuter), j'ai potentiellement introduit un bogue en production. Le test devrait être modifié pour tenir compte du nouveau comportement, et aussi du contraire de ce que nous attendons.

## Exemple complet

Vous pouvez trouver un exemple complet d'un projet Flight PHP avec des tests unitaires sur GitHub : [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Pour plus de guides, consultez [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) et [Troubleshooting](/learn/troubleshooting).

## Pièges courants

- **Sur-marquage** : Ne moquez pas toutes les dépendances ; laissez une certaine logique (par exemple, la validation du contrôleur) s'exécuter pour tester un comportement réel. Voir [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **État global** : Utiliser des variables PHP globales (par exemple, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) rend les tests fragiles. Idem pour `Flight::`. Refacturez pour passer les dépendances explicitement.
- **Configuration complexe** : Si la configuration des tests est fastidieuse, votre classe pourrait avoir trop de dépendances ou de responsabilités violant les [principes SOLID](https://en.wikipedia.org/wiki/SOLID).

## Mise à l'échelle avec les tests unitaires

Les tests unitaires brillent dans les projets plus importants ou lorsque vous revenez sur le code après des mois. Ils documentent le comportement et détectent les régressions, vous faisant gagner du temps pour ne pas avoir à réapprendre votre application. Pour les développeurs solo, testez les chemins critiques (par exemple, l'inscription des utilisateurs, le traitement des paiements). Pour les équipes, les tests assurent un comportement cohérent à travers les contributions. Voir [Why Frameworks?](/learn/why-frameworks) pour plus d'informations sur les avantages des frameworks et des tests.

Contribuez vos propres conseils de test au dépôt de documentation de Flight PHP !

_Écrit par [n0nag0n](https://github.com/n0nag0n) 2025_