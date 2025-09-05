# Documentation APM FlightPHP

Bienvenue dans FlightPHP APM—votre coach personnel pour les performances de votre application ! Ce guide est votre feuille de route pour configurer, utiliser et maîtriser la surveillance des performances des applications (APM) avec FlightPHP. Que vous chassiez les demandes lentes ou que vous souhaitiez vous amuser avec des graphiques de latence, nous vous couvrons. Rendons votre application plus rapide, vos utilisateurs plus heureux et vos sessions de débogage plus faciles !

![FlightPHP APM](/images/apm.png)

## Pourquoi APM Importe

Imaginez ceci : votre application est un restaurant animé. Sans moyen de suivre le temps des commandes ou d'identifier où la cuisine ralentit, vous devinez pourquoi les clients partent mécontents. APM est votre sous-chef—il surveille chaque étape, des demandes entrantes aux requêtes de base de données, et signale tout ce qui vous ralentit. Les pages lentes font perdre des utilisateurs (les études disent que 53 % rebondissent si un site prend plus de 3 secondes à charger !), et APM vous aide à détecter ces problèmes *avant* qu'ils ne fassent mal. C'est une tranquillité d'esprit proactive—moins de moments « pourquoi cela ne fonctionne pas ? », plus de victoires « regardez comme cela fonctionne bien ! ».

## Installation

Démarrez avec Composer :

```bash
composer require flightphp/apm
```

Vous aurez besoin de :
- **PHP 7.4+** : Garde la compatibilité avec les distributions Linux LTS tout en supportant le PHP moderne.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+** : Le framework léger que nous boostons.

## Bases de Données Prises en Charge

FlightPHP APM prend actuellement en charge les bases de données suivantes pour stocker les métriques :

- **SQLite3** : Simple, basé sur un fichier, et idéal pour le développement local ou les petites applications. Option par défaut dans la plupart des configurations.
- **MySQL/MariaDB** : Idéal pour les projets plus importants ou les environnements de production où vous avez besoin d'un stockage robuste et scalable.

Vous pouvez choisir le type de base de données lors de l'étape de configuration (voir ci-dessous). Assurez-vous que votre environnement PHP a les extensions nécessaires installées (par exemple, `pdo_sqlite` ou `pdo_mysql`).

## Pour Commencer

Voici votre guide étape par étape pour l'Awesome APM :

### 1. Enregistrer l'APM

Ajoutez cela dans votre `index.php` ou un fichier `services.php` pour commencer à suivre :

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Si vous ajoutez une connexion de base de données
// Doit être PdoWrapper ou PdoQueryCapture des extensions Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True requis pour activer le suivi dans l'APM.
$Apm->addPdoConnection($pdo);
```

**Qu'est-ce qui se passe ici ?**
- `LoggerFactory::create()` récupère votre configuration (plus de détails bientôt) et configure un journaliseur—SQLite par défaut.
- `Apm` est la star—il écoute les événements de Flight (demandes, routes, erreurs, etc.) et collecte les métriques.
- `bindEventsToFlightInstance($app)` lie tout à votre application Flight.

**Astuce Pro : Échantillonnage**
Si votre application est occupée, journaliser *toutes* les demandes pourrait surcharger les choses. Utilisez un taux d'échantillonnage (0.0 à 1.0) :

```php
$Apm = new Apm($ApmLogger, 0.1); // Journalise 10 % des demandes
```

Cela garde les performances fluides tout en vous donnant des données solides.

### 2. Configurez-le

Exécutez cela pour créer votre `.runway-config.json` :

```bash
php vendor/bin/runway apm:init
```

**Qu'est-ce que cela fait ?**
- Lance un assistant qui demande d'où viennent les métriques brutes (source) et où vont les données traitées (destination).
- Par défaut, c'est SQLite—par exemple, `sqlite:/tmp/apm_metrics.sqlite` pour la source, une autre pour la destination.
- Vous obtiendrez une configuration comme :
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Ce processus demandera aussi si vous voulez exécuter les migrations pour cette configuration. Si c'est votre première installation, la réponse est oui.

**Pourquoi deux emplacements ?**
Les métriques brutes s'accumulent rapidement (pensez à des journaux non filtrés). Le worker les traite dans une destination structurée pour le tableau de bord. Cela garde les choses organisées !

### 3. Traitez les Métriques avec le Worker

Le worker transforme les métriques brutes en données prêtes pour le tableau de bord. Exécutez-le une fois :

```bash
php vendor/bin/runway apm:worker
```

**Qu'est-ce qu'il fait ?**
- Lit à partir de votre source (par exemple, `apm_metrics.sqlite`).
- Traite jusqu'à 100 métriques (taille de lot par défaut) dans votre destination.
- S'arrête quand c'est fait ou s'il n'y a plus de métriques.

**Gardez-le en Marche**
Pour les applications en direct, vous voudrez un traitement continu. Voici vos options :

- **Mode Daemon** :
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  S'exécute pour toujours, traitant les métriques au fur et à mesure. Idéal pour le dev ou les petites configurations.

- **Crontab** :
  Ajoutez cela à votre crontab (`crontab -e`) :
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  S'exécute toutes les minutes—parfait pour la production.

- **Tmux/Screen** :
  Démarrez une session détachable :
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, puis D pour détacher ; `tmux attach -t apm-worker` pour reconnecter
  ```
  Le garde en marche même si vous vous déconnectez.

- **Ajustements Personnalisés** :
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50` : Traite 50 métriques à la fois.
  - `--max_messages 1000` : Arrête après 1000 métriques.
  - `--timeout 300` : Quitte après 5 minutes.

**Pourquoi s'embêter ?**
Sans le worker, votre tableau de bord est vide. C'est le pont entre les journaux bruts et les insights actionnables.

### 4. Lancez le Tableau de Bord

Voyez les signes vitaux de votre application :

```bash
php vendor/bin/runway apm:dashboard
```

**Qu'est-ce que c'est ?**
- Lance un serveur PHP à `http://localhost:8001/apm/dashboard`.
- Affiche les journaux de demandes, les routes lentes, les taux d'erreurs, et plus.

**Personnalisez-le** :
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0` : Accessible depuis n'importe quelle IP (pratique pour la visualisation à distance).
- `--port 8080` : Utilisez un port différent si 8001 est pris.
- `--php-path` : Indiquez le chemin vers PHP s'il n'est pas dans votre PATH.

Ouvrez l'URL dans votre navigateur et explorez !

#### Mode Production

Pour la production, vous devrez peut-être essayer quelques techniques pour faire fonctionner le tableau de bord, car il y a probablement des pare-feu et d'autres mesures de sécurité en place. Voici quelques options :

- **Utiliser un Reverse Proxy** : Configurez Nginx ou Apache pour rediriger les demandes vers le tableau de bord.
- **Tunnel SSH** : Si vous pouvez SSH sur le serveur, utilisez `ssh -L 8080:localhost:8001 youruser@yourserver` pour tunneler le tableau de bord vers votre machine locale.
- **VPN** : Si votre serveur est derrière un VPN, connectez-vous et accédez directement au tableau de bord.
- **Configurer le Pare-feu** : Ouvrez le port 8001 pour votre IP ou le réseau du serveur (ou le port que vous avez défini).
- **Configurer Apache/Nginx** : Si vous avez un serveur web devant votre application, configurez-le pour un domaine ou un sous-domaine. Si vous faites cela, définissez le répertoire racine sur `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Voulez un Tableau de Bord Différent ?

Vous pouvez construire votre propre tableau de bord si vous voulez ! Regardez le répertoire vendor/flightphp/apm/src/apm/presenter pour des idées sur la façon de présenter les données pour votre propre tableau de bord !

## Fonctionnalités du Tableau de Bord

Le tableau de bord est votre quartier général APM—voici ce que vous verrez :

- **Journal des Demandes** : Chaque demande avec horodatage, URL, code de réponse et temps total. Cliquez sur « Détails » pour les middleware, requêtes et erreurs.
- **Demandes les Plus Lentes** : Les 5 principales demandes qui prennent du temps (par exemple, « /api/heavy » à 2,5 s).
- **Routes les Plus Lentes** : Les 5 routes par temps moyen—super pour repérer les patterns.
- **Taux d'Erreurs** : Pourcentage de demandes qui échouent (par exemple, 2,3 % de 500s).
- **Percentiles de Latence** : 95e (p95) et 99e (p99) temps de réponse—connaissez vos scénarios les plus mauvais.
- **Graphique des Codes de Réponse** : Visualisez les 200s, 404s, 500s au fil du temps.
- **Requêtes/Middleware Longues** : Les 5 principaux appels de base de données lents et les couches de middleware.
- **Taux de Succès/Miss du Cache** : À quelle fréquence votre cache sauve la mise.

**Extras** :
- Filtrez par « Dernière Heure », « Dernier Jour » ou « Dernière Semaine ».
- Basculez en mode sombre pour ces sessions tardives.

**Exemple** :
Une demande à `/users` pourrait montrer :
- Temps Total : 150 ms
- Middleware : `AuthMiddleware->handle` (50 ms)
- Requête : `SELECT * FROM users` (80 ms)
- Cache : Succès sur `user_list` (5 ms)

## Ajout d'Événements Personnalisés

Suivez n'importe quoi—comme un appel API ou un processus de paiement :

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Où cela apparaît-il ?**
Dans les détails de demande du tableau de bord sous « Événements Personnalisés »—expandable avec un formatage JSON joli.

**Cas d'Utilisation** :
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Maintenant, vous verrez si cet API ralentit votre application !

## Surveillance de la Base de Données

Suivez les requêtes PDO comme ceci :

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True requis pour activer le suivi dans l'APM.
$Apm->addPdoConnection($pdo);
```

**Ce que Vous Obtenez** :
- Texte de la requête (par exemple, `SELECT * FROM users WHERE id = ?`)
- Temps d'exécution (par exemple, 0,015 s)
- Nombre de lignes (par exemple, 42)

**Avertissement** :
- **Optionnel** : Sautez cela si vous n'avez pas besoin de suivi de BD.
- **Seulement PdoWrapper** : Le PDO de base n'est pas encore accroché—restons à l'écoute !
- **Avertissement de Performance** : Journaliser chaque requête sur un site chargé en BD peut ralentir les choses. Utilisez l'échantillonnage (`$Apm = new Apm($ApmLogger, 0.1)`) pour alléger la charge.

**Exemple de Sortie** :
- Requête : `SELECT name FROM products WHERE price > 100`
- Temps : 0,023 s
- Lignes : 15

## Options du Worker

Ajustez le worker à votre goût :

- `--timeout 300` : Arrête après 5 minutes—bon pour les tests.
- `--max_messages 500` : Limite à 500 métriques—le garde fini.
- `--batch_size 200` : Traite 200 à la fois—équilibre vitesse et mémoire.
- `--daemon` : S'exécute sans arrêt—idéal pour la surveillance en direct.

**Exemple** :
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
S'exécute pendant une heure, traitant 100 métriques à la fois.

## ID de Demande dans l'Application

Chaque demande a un ID de demande unique pour le suivi. Vous pouvez utiliser cet ID dans votre application pour corréler les journaux et les métriques. Par exemple, vous pouvez ajouter l'ID de demande à une page d'erreur :

```php
Flight::map('error', function($message) {
	// Obtenez l'ID de demande à partir de l'en-tête de réponse X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// De plus, vous pourriez le récupérer à partir de la variable Flight
	// Cette méthode ne fonctionnera pas bien sur Swoole ou d'autres plateformes asynchrones.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Mise à Niveau

Si vous mettez à niveau vers une version plus récente de l'APM, il y a une chance que des migrations de base de données doivent être exécutées. Vous pouvez le faire en exécutant la commande suivante :

```bash
php vendor/bin/runway apm:migrate
```
Ceci exécutera toutes les migrations nécessaires pour mettre à jour le schéma de base de données vers la dernière version.

**Note :** Si votre base de données APM est grande, ces migrations peuvent prendre du temps. Vous voudrez peut-être exécuter cette commande pendant les heures creuses.

## Purge des Données Anciennes

Pour garder votre base de données propre, vous pouvez purger les données anciennes. C'est particulièrement utile si vous exécutez une application occupée et que vous voulez garder la taille de la base de données gérable.
Vous pouvez le faire en exécutant la commande suivante :

```bash
php vendor/bin/runway apm:purge
```
Ceci supprimera toutes les données plus anciennes que 30 jours de la base de données. Vous pouvez ajuster le nombre de jours en passant une valeur différente à l'option `--days` :

```bash
php vendor/bin/runway apm:purge --days 7
```
Ceci supprimera toutes les données plus anciennes que 7 jours de la base de données.

## Dépannage

Coincé ? Essayez ces astuces :

- **Pas de Données sur le Tableau de Bord ?**
  - Le worker est-il en marche ? Vérifiez `ps aux | grep apm:worker`.
  - Les chemins de configuration correspondent ? Vérifiez que les DSNs dans `.runway-config.json` pointent vers des fichiers réels.
  - Exécutez `php vendor/bin/runway apm:worker` manuellement pour traiter les métriques en attente.

- **Erreurs du Worker ?**
  - Jetez un œil à vos fichiers SQLite (par exemple, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Vérifiez les journaux PHP pour les traces de pile.

- **Le Tableau de Bord Ne Démarre Pas ?**
  - Le port 8001 est-il utilisé ? Utilisez `--port 8080`.
  - PHP non trouvé ? Utilisez `--php-path /usr/bin/php`.
  - Pare-feu bloquant ? Ouvrez le port ou utilisez `--host localhost`.

- **Trop Lent ?**
  - Baissez le taux d'échantillonnage : `$Apm = new Apm($ApmLogger, 0.05)` (5 %).
  - Réduisez la taille du lot : `--batch_size 20`.

- **Ne Suit Pas les Exceptions/Erreurs ?**
  - Si vous avez [Tracy](https://tracy.nette.org/) activé pour votre projet, il remplacera la gestion d'erreurs de Flight. Vous devrez désactiver Tracy et vous assurer que `Flight::set('flight.handle_errors', true);` est défini.

- **Ne Suit Pas les Requêtes de Base de Données ?**
  - Assurez-vous d'utiliser `PdoWrapper` pour vos connexions de base de données.
  - Assurez-vous que le dernier argument dans le constructeur est `true`.