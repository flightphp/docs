# Documentation APM de FlightPHP

Bienvenue sur FlightPHP APM—votre coach personnel pour les performances de votre application ! Ce guide est votre feuille de route pour configurer, utiliser et maîtriser la surveillance des performances des applications (APM) avec FlightPHP. Que vous chassiez les demandes lentes ou que vous souhaitiez vous plonger dans les graphiques de latence, nous avons ce qu'il vous faut. Rendons votre application plus rapide, vos utilisateurs plus heureux et vos sessions de débogage plus faciles !

![FlightPHP APM](/images/apm.png)

## Pourquoi l'APM est important

Imaginez ceci : votre application est un restaurant animé. Sans moyen de suivre le temps que prennent les commandes ou d'identifier où la cuisine ralentit, vous devinez pourquoi les clients partent mécontents. L'APM est votre sous-chef—il surveille chaque étape, des demandes entrantes aux requêtes de base de données, et signale tout ce qui ralentit. Les pages lentes font perdre des utilisateurs (les études indiquent que 53 % rebondissent si un site prend plus de 3 secondes à charger !), et l'APM vous aide à détecter ces problèmes *avant* qu'ils ne causent des dommages. C'est une tranquillité d'esprit proactive—moins de moments « pourquoi cela ne fonctionne pas ? », plus de victoires « regardez comme cela fonctionne bien ! ».

## Installation

Démarrez avec Composer :

```bash
composer require flightphp/apm
```

Vous aurez besoin de :
- **PHP 7.4+** : Pour rester compatible avec les distributions Linux LTS tout en supportant le PHP moderne.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+** : Le framework léger que nous renforçons.

## Pour commencer

Voici votre guide étape par étape pour une APM extraordinaire :

### 1. Enregistrer l'APM

Ajoutez cela dans votre fichier `index.php` ou `services.php` pour commencer à suivre :

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**Ce qui se passe ici ?**
- `LoggerFactory::create()` récupère votre configuration (plus de détails bientôt) et configure un enregistreur—SQLite par défaut.
- `Apm` est la vedette—il écoute les événements de Flight (demandes, routes, erreurs, etc.) et collecte des métriques.
- `bindEventsToFlightInstance($app)` lie tout à votre application Flight.

**Astuce pro : Échantillonnage**
Si votre application est occupée, enregistrer *chaque* demande pourrait surcharger les choses. Utilisez un taux d'échantillonnage (de 0.0 à 1.0) :

```php
$Apm = new Apm($ApmLogger, 0.1); // Enregistre 10 % des demandes
```

Cela garde les performances fluides tout en vous fournissant des données solides.

### 2. Configurez-le

Exécutez ceci pour créer votre `.runway-config.json` :

```bash
php vendor/bin/runway apm:init
```

**Ce que cela fait ?**
- Lance un assistant qui demande d'où proviennent les métriques brutes (source) et où vont les données traitées (destination).
- Le défaut est SQLite—par exemple, `sqlite:/tmp/apm_metrics.sqlite` pour la source, un autre pour la destination.
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

> Ce processus demandera également si vous souhaitez exécuter les migrations pour cette configuration. Si vous configurez cela pour la première fois, la réponse est oui.

**Pourquoi deux emplacements ?**
Les métriques brutes s'accumulent rapidement (pensez à des journaux non filtrés). Le travailleur les traite dans une destination structurée pour le tableau de bord. Cela garde les choses organisées !

### 3. Traitez les métriques avec le travailleur

Le travailleur transforme les métriques brutes en données prêtes pour le tableau de bord. Exécutez-le une fois :

```bash
php vendor/bin/runway apm:worker
```

**Ce qu'il fait ?**
- Lit à partir de votre source (par exemple, `apm_metrics.sqlite`).
- Traite jusqu'à 100 métriques (taille de lot par défaut) dans votre destination.
- S'arrête quand c'est fait ou s'il n'y a plus de métriques.

**Gardez-le en marche**
Pour les applications en direct, vous voudrez un traitement continu. Voici vos options :

- **Mode démon** :
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  S'exécute indéfiniment, traitant les métriques au fur et à mesure. Idéal pour le développement ou les configurations petites.

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

- **Ajustements personnalisés** :
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50` : Traite 50 métriques à la fois.
  - `--max_messages 1000` : S'arrête après 1000 métriques.
  - `--timeout 300` : Quitte après 5 minutes.

**Pourquoi s'embêter ?**
Sans le travailleur, votre tableau de bord est vide. C'est le pont entre les journaux bruts et les insights actionnables.

### 4. Lancez le tableau de bord

Voyez les signes vitaux de votre application :

```bash
php vendor/bin/runway apm:dashboard
```

**Ce que c'est ?**
- Démarre un serveur PHP à `http://localhost:8001/apm/dashboard`.
- Affiche les journaux de demandes, les routes lentes, les taux d'erreurs, et plus encore.

**Personnalisez-le** :
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0` : Accessible depuis n'importe quelle IP (pratique pour la visualisation à distance).
- `--port 8080` : Utilisez un port différent si 8001 est occupé.
- `--php-path` : Indiquez le chemin vers PHP s'il n'est pas dans votre PATH.

Ouvrez l'URL dans votre navigateur et explorez !

#### Mode production

En production, vous devrez peut-être essayer quelques techniques pour faire fonctionner le tableau de bord, car il y a probablement des pare-feu et d'autres mesures de sécurité. Voici quelques options :

- **Utiliser un reverse proxy** : Configurez Nginx ou Apache pour rediriger les demandes vers le tableau de bord.
- **Tunnel SSH** : Si vous pouvez vous connecter en SSH au serveur, utilisez `ssh -L 8080:localhost:8001 youruser@yourserver` pour tunneler le tableau de bord vers votre machine locale.
- **VPN** : Si votre serveur est derrière un VPN, connectez-vous et accédez directement au tableau de bord.
- **Configurer le pare-feu** : Ouvrez le port 8001 pour votre IP ou le réseau du serveur (ou le port que vous avez défini).
- **Configurer Apache/Nginx** : Si vous avez un serveur web devant votre application, configurez-le pour un domaine ou un sous-domaine. Si vous faites cela, définissez la racine des documents sur `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Voulez-vous un tableau de bord différent ?

Vous pouvez créer votre propre tableau de bord si vous le souhaitez ! Regardez le répertoire vendor/flightphp/apm/src/apm/presenter pour des idées sur la façon de présenter les données pour votre propre tableau de bord !

## Fonctionnalités du tableau de bord

Le tableau de bord est votre quartier général APM—voici ce que vous verrez :

- **Journal des demandes** : Chaque demande avec horodatage, URL, code de réponse et temps total. Cliquez sur « Détails » pour voir les middleware, requêtes et erreurs.
- **Demandes les plus lentes** : Les 5 demandes les plus chronophages (par exemple, « /api/heavy » à 2,5 s).
- **Routes les plus lentes** : Les 5 routes par temps moyen—excellent pour repérer les schémas.
- **Taux d'erreurs** : Pourcentage de demandes qui échouent (par exemple, 2,3 % de 500s).
- **Percentiles de latence** : 95e (p95) et 99e (p99) temps de réponse—connaissez vos scénarios les plus défavorables.
- **Graphique des codes de réponse** : Visualisez les 200s, 404s, 500s au fil du temps.
- **Requêtes/Middleware longues** : Les 5 appels de base de données lents et les couches de middleware.
- **Taux de succès/cache manqués** : À quelle fréquence votre cache sauve la mise.

**Extras** :
- Filtrez par « Dernière heure », « Dernier jour » ou « Dernière semaine ».
- Basculez en mode sombre pour ces sessions tardives.

**Exemple** :
Une demande à `/users` pourrait montrer :
- Temps total : 150 ms
- Middleware : `AuthMiddleware->handle` (50 ms)
- Requête : `SELECT * FROM users` (80 ms)
- Cache : Succès sur `user_list` (5 ms)

## Ajout d'événements personnalisés

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
Dans les détails de demande du tableau de bord sous « Événements personnalisés »— extensible avec un formatage JSON élégant.

**Cas d'utilisation** :
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

## Surveillance de la base de données

Suivez les requêtes PDO comme ceci :

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**Ce que vous obtenez** :
- Texte de la requête (par exemple, `SELECT * FROM users WHERE id = ?`)
- Temps d'exécution (par exemple, 0,015 s)
- Nombre de lignes (par exemple, 42)

**Avertissement** :
- **Optionnel** : Sautez cela si vous n'avez pas besoin de suivi de base de données.
- **Seulement PdoWrapper** : Le PDO de base n'est pas encore connecté—restons à l'écoute !
- **Avertissement de performance** : Enregistrer chaque requête sur un site lourd en base de données peut ralentir les choses. Utilisez l'échantillonnage (`$Apm = new Apm($ApmLogger, 0.1)`) pour alléger la charge.

**Exemple de sortie** :
- Requête : `SELECT name FROM products WHERE price > 100`
- Temps : 0,023 s
- Lignes : 15

## Options du travailleur

Ajustez le travailleur à votre guise :

- `--timeout 300` : S'arrête après 5 minutes—bon pour les tests.
- `--max_messages 500` : Limite à 500 métriques—le garde fini.
- `--batch_size 200` : Traite 200 à la fois—équilibre vitesse et mémoire.
- `--daemon` : S'exécute sans arrêt—idéal pour la surveillance en direct.

**Exemple** :
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
S'exécute pendant une heure, traitant 100 métriques à la fois.

## ID de demande dans l'application

Chaque demande a un ID de demande unique pour le suivi. Vous pouvez utiliser cet ID dans votre application pour corréler les journaux et les métriques. Par exemple, vous pouvez ajouter l'ID de demande à une page d'erreur :

```php
Flight::map('error', function($message) {
	// Obtenez l'ID de demande à partir de l'en-tête de réponse X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// De plus, vous pourriez le récupérer à partir de la variable Flight
	// Cette méthode ne fonctionnera pas bien sur Swoole ou d'autres plateformes asynchrones.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Erreur: $message (ID de demande: $requestId)";
});
```

## Mise à niveau

Si vous mettez à niveau vers une version plus récente de l'APM, il y a une chance que des migrations de base de données doivent être exécutées. Vous pouvez le faire en exécutant la commande suivante :

```bash
php vendor/bin/runway apm:migrate
```
Cela exécutera toutes les migrations nécessaires pour mettre à jour le schéma de la base de données vers la dernière version.

**Note :** Si votre base de données APM est grande, ces migrations peuvent prendre du temps. Vous voudrez peut-être exécuter cette commande pendant les heures creuses.

## Suppression des anciennes données

Pour garder votre base de données propre, vous pouvez supprimer les anciennes données. C'est particulièrement utile si vous exécutez une application occupée et que vous voulez garder la taille de la base de données gérable.
Vous pouvez le faire en exécutant la commande suivante :

```bash
php vendor/bin/runway apm:purge
```
Cela supprimera toutes les données plus anciennes que 30 jours de la base de données. Vous pouvez ajuster le nombre de jours en passant une valeur différente à l'option `--days` :

```bash
php vendor/bin/runway apm:purge --days 7
```
Cela supprimera toutes les données plus anciennes que 7 jours de la base de données.

## Dépannage

Coincé ? Essayez ceci :

- **Pas de données dans le tableau de bord ?**
  - Le travailleur est-il en marche ? Vérifiez avec `ps aux | grep apm:worker`.
  - Les chemins de configuration correspondent-ils ? Vérifiez que les DSNs dans `.runway-config.json` pointent vers des fichiers réels.
  - Exécutez `php vendor/bin/runway apm:worker` manuellement pour traiter les métriques en attente.

- **Erreurs du travailleur ?**
  - Jetez un œil à vos fichiers SQLite (par exemple, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Vérifiez les journaux PHP pour les traces de pile.

- **Le tableau de bord ne démarre pas ?**
  - Le port 8001 est-il utilisé ? Utilisez `--port 8080`.
  - PHP n'est pas trouvé ? Utilisez `--php-path /usr/bin/php`.
  - Le pare-feu bloque-t-il ? Ouvrez le port ou utilisez `--host localhost`.

- **Trop lent ?**
  - Baissez le taux d'échantillonnage : `$Apm = new Apm($ApmLogger, 0.05)` (5 %).
  - Réduisez la taille du lot : `--batch_size 20`.