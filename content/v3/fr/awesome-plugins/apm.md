# Documentation FlightPHP APM

Bienvenue dans FlightPHP APM—le coach de performance personnel de votre application ! Ce guide est votre feuille de route pour configurer, utiliser et maîtriser la supervision des performances applicatives (APM) avec FlightPHP. Que vous soyez à la recherche de requêtes lentes ou que vous souhaitiez simplement vous passionner pour les graphiques de latence, nous avons ce qu'il vous faut. Rendons votre application plus rapide, vos utilisateurs plus heureux, et vos sessions de débogage un jeu d'enfant !

Consultez une [démo](https://flightphp-docs-apm.sky-9.com/apm/dashboard) du tableau de bord pour le site de la documentation Flight.

![FlightPHP APM](/images/apm.png)

## Pourquoi l'APM est important

Imaginez ceci : votre application est un restaurant très fréquenté. Sans un moyen de suivre le temps que prennent les commandes ou l'endroit où la cuisine s'engorge, vous devinez pourquoi les clients partent de mauvaise humeur. L'APM est votre second de cuisine—il surveille chaque étape, des requêtes entrantes aux requêtes de base de données, et signale tout ce qui vous ralentit. Les pages lentes font perdre des utilisateurs (les études disent que 53 % des visiteurs quittent le site si le chargement dépasse 3 secondes !), et l'APM vous aide à détecter ces problèmes *avant* qu'ils ne fassent mal. C'est une tranquillité d'esprit proactive—moins de moments « pourquoi est-ce cassé ? », plus de victoires « regardez comme ça tourne bien ! ».

## Installation

Commencez avec Composer :

```bash
composer require flightphp/apm
```

Vous aurez besoin de :
- **PHP 7.4+** : Nous maintient compatibles avec les distributions Linux LTS tout en prenant en charge PHP moderne.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+** : Le framework léger que nous améliorons.

## Bases de données prises en charge

FlightPHP APM prend actuellement en charge les bases de données suivantes pour stocker les métriques :

- **SQLite3** : Simple, basée sur des fichiers, et idéale pour le développement local ou les petites applications. Option par défaut dans la plupart des configurations.
- **MySQL/MariaDB** : Idéale pour les grands projets ou les environnements de production où vous avez besoin d'un stockage robuste et évolutif.

Vous pouvez choisir votre type de base de données lors de l'étape de configuration (voir ci-dessous). Assurez-vous que votre environnement PHP dispose des extensions nécessaires (par exemple, `pdo_sqlite` ou `pdo_mysql`).

## Pour commencer

Voici votre guide étape par étape vers l'excellence APM :

### 1. Enregistrez l'APM

Insérez ceci dans votre fichier `index.php` ou un fichier `services.php` pour commencer le suivi :

```php
use flight\apm\logger\LoggerFactory;
use flight\database\SimplePdo;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Si vous ajoutez une connexion à la base de données
// Préférez SimplePdo (ou PdoQueryCapture de Tracy Extensions en dev).
// Activez le suivi des requêtes APM via le tableau d'options (5e argument).
$pdo = new SimplePdo('mysql:host=localhost;dbname=example', 'user', 'pass', null, [
	'trackApmQueries' => true, // requis pour capturer les requêtes pour l'APM
]);
$Apm->addPdoConnection($pdo);
```

**Que se passe-t-il ici ?**
- `LoggerFactory::create()` récupère votre configuration (plus de détails bientôt) et configure un journal—SQLite par défaut.
- `Apm` est la star—il écoute les événements de Flight (requêtes, routes, erreurs, etc.) et collecte les métriques.
- `bindEventsToFlightInstance($app)` lie le tout à votre application Flight.

**Astuce de pro : Échantillonnage**
Si votre application est très sollicitée, journaliser *chaque* requête pourrait surcharger le système. Utilisez un taux d'échantillonnage (0,0 à 1,0) :

```php
$Apm = new Apm($ApmLogger, 0.1); // Journalise 10 % des requêtes
```

Cela maintient des performances réactives tout en vous donnant des données fiables.

### 2. Configurez-le

Exécutez ceci pour créer votre `.runway-config.json` :

```bash
php vendor/bin/runway apm:init
```

**Qu'est-ce que cela fait ?**
- Lance un assistant vous demandant d'où proviennent les métriques brutes (source) et où vont les données traitées (destination).
- La valeur par défaut est SQLite—par exemple, `sqlite:/tmp/apm_metrics.sqlite` pour la source, et une autre pour la destination.
- Vous obtiendrez une configuration comme celle-ci :
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

> Ce processus vous demandera également si vous souhaitez exécuter les migrations pour cette configuration. Si vous configurez ceci pour la première fois, la réponse est oui.

**Pourquoi deux emplacements ?**
Les métriques brutes s'accumulent rapidement (pensez à des journaux non filtrés). Le worker les traite dans une destination structurée pour le tableau de bord. Cela garde tout bien organisé !

### 3. Traitez les métriques avec le worker

Le worker transforme les métriques brutes en données prêtes pour le tableau de bord. Exécutez-le une fois :

```bash
php vendor/bin/runway apm:worker
```

**Que fait-il ?**
- Lit depuis votre source (par exemple, `apm_metrics.sqlite`).
- Traite jusqu'à 100 métriques (taille de lot par défaut) vers votre destination.
- S'arrête lorsque c'est terminé ou s'il ne reste plus de métriques.

**Gardez-le en marche**
Pour les applications en direct, vous voudrez un traitement continu. Voici vos options :

- **Mode démon** :
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  S'exécute en continu, traitant les métriques au fur et à mesure. Idéal pour le développement ou les petites configurations.

- **Crontab** :
  Ajoutez ceci à votre crontab (`crontab -e`) :
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Se déclenche chaque minute—parfait pour la production.

- **Tmux/Screen** :
  Démarrez une session détachable :
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, puis D pour détacher ; `tmux attach -t apm-worker` pour vous reconnecter
  ```
  Le maintient en fonctionnement même si vous vous déconnectez.

- **Réglages personnalisés** :
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50` : Traite 50 métriques à la fois.
  - `--max_messages 1000` : S'arrête après 1000 métriques.
  - `--timeout 300` : Quitte après 5 minutes.

**Pourquoi s'embêter ?**
Sans le worker, votre tableau de bord est vide. C'est le pont entre les journaux bruts et les informations exploitables.

### 4. Lancez le tableau de bord

Visualisez les indicateurs clés de votre application :

```bash
php vendor/bin/runway apm:dashboard
```

**Qu'est-ce que c'est ?**
- Démarre un serveur PHP à `http://localhost:8001/apm/dashboard`.
- Affiche les journaux de requêtes, les routes lentes, les taux d'erreur, et plus encore.

**Personnalisez-le** :
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0` : Accessible depuis n'importe quelle adresse IP (pratique pour la visualisation à distance).
- `--port 8080` : Utilisez un port différent si 8001 est déjà pris.
- `--php-path` : Indiquez le chemin vers PHP s'il n'est pas dans votre PATH.

Accédez à l'URL dans votre navigateur et explorez !

#### Mode production

En production, vous devrez peut-être essayer plusieurs techniques pour faire fonctionner le tableau de bord, car il y a probablement des pare-feu et d'autres mesures de sécurité en place. Voici quelques options :

- **Utilisez un proxy inverse** : Configurez Nginx ou Apache pour transférer les requêtes vers le tableau de bord.
- **Tunnel SSH** : Si vous pouvez vous connecter en SSH au serveur, utilisez `ssh -L 8080:localhost:8001
votreutilisateur@votreserveur` pour tunneler le tableau de bord vers votre machine locale.
- **VPN** : Si votre serveur est derrière un VPN, connectez-vous et accédez au tableau de bord directement.
- **Configurez le pare-feu** : Ouvrez le port 8001 pour votre adresse IP ou le réseau du serveur. (ou le port que vous avez défini).
- **Configurez Apache/Nginx** : Si vous avez un serveur web devant votre application, vous pouvez le configurer sur un domaine ou un sous-domaine. Pointez cet hôte vers `dashboard/index.php` (le serveur intégré l'utilise comme routeur). Les fichiers statiques se trouvent à côté, dans `dashboard/css` et `dashboard/js`.
- **Authentification de base** : définissez `apm.dashboard_auth.user` et `apm.dashboard_auth.password` dans la configuration runway. Si l'une des valeurs est vide, le tableau de bord reste ouvert. Il s'agit d'un mot de passe partagé unique, et non de comptes utilisateurs de l'application.

#### Vous voulez un tableau de bord différent ?

Vous pouvez créer votre propre tableau de bord si vous le souhaitez ! Consultez le répertoire vendor/flightphp/apm/src/apm/presenter pour des idées sur la façon de présenter les données pour votre propre tableau de bord !

## Fonctionnalités du tableau de bord

Le tableau de bord s'ouvre avec les chiffres, puis les graphiques, puis le journal des requêtes.

- **Rangée de statistiques** : nombre de requêtes, taux d'erreur, p95, p99, latence moyenne, débit par minute, et taux de succès du cache.
- **Arriéré du worker** : combien de métriques brutes sont encore en attente dans `apm_metrics_log`. Un nombre élevé signifie que le worker est en retard. Un tableau de bord vide avec un arriéré de 0 signifie que rien n'a été collecté.
- **Graphique de latence** : latence moyenne et nombre de requêtes pour les mêmes segments. La dernière heure utilise des segments de 5 minutes, le dernier jour des segments de 15 minutes, et la dernière semaine des segments de 6 heures. Une plage personnalisée choisit un segment qui conserve environ 40 points. Les heures sont stockées en UTC et affichées dans le fuseau horaire que vous choisissez.
- **Graphique de statut** : compteurs empilés `2xx`, `3xx`, `4xx` et `5xx`. Cliquez sur une série pour filtrer le journal sur cette classe.
- **Routes lentes, middleware, requêtes et requêtes HTTP** : cinq de chaque. Les requêtes de base de données sont regroupées afin que `WHERE id = 1` et `WHERE id = 2` comptent comme une seule instruction. Cliquez sur une route, une requête lente ou un groupe d'erreurs pour filtrer le journal.
- **Événements personnalisés** : chaque type d'événement personnalisé que votre application déclenche, avec son nombre, sa dernière apparition, et une petite courbe de tendance pour la fenêtre sélectionnée. Triés par nombre. Ils respectent la plage horaire, l'option Masquer les bots, et les autres filtres. Cliquez sur un type pour filtrer le journal sur celui-ci.
- **Journal des requêtes** : méthode, URL, statut, temps, mémoire, hôte, et indicateur de bot. Les détails se chargent lorsque vous ouvrez la ligne et incluent une barre de temps, les vues, les paramètres de requête, la trace d'erreur, et les événements personnalisés.
- **Masquer les bots** : exclut le trafic des bots des agrégats et du journal. Les robots de recherche et les robots d'IA (GPTBot, ClaudeBot, PerplexityBot, SearxNG, et autres) sont marqués comme bots.
- **Plage personnalisée** : choisissez une fenêtre de début et de fin en plus de la dernière heure, du dernier jour ou de la dernière semaine.
- **Filtres** : URL, statut, ID de requête, route, IP, hôte, session, agent utilisateur, type d'événement personnalisé, données d'événement, et message d'erreur. La chaîne de requête les conserve lors d'un rafraîchissement.

**Exemple** :
Une requête vers `/users` pourrait afficher :
- Temps total : 150 ms
- Middleware : `AuthMiddleware->handle` (50 ms)
- Requête de base de données : `SELECT * FROM users` (80 ms)
- Temps de rendu de la vue
- Cache : Succès sur `user_list` (5 ms)

**Exemple** :
Une requête vers `/users` pourrait afficher :
- Temps total : 150 ms
- Middleware : `AuthMiddleware->handle` (50 ms)
- Requête de base de données : `SELECT * FROM users` (80 ms)
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
Dans les détails de requête du tableau de bord sous « Événements personnalisés »—dépliable avec un joli formatage JSON. Le panneau **Événements personnalisés** compte également chaque type pour vous, donc un simple événement `docs.markdown_served` suffit pour répondre à « à quelle fréquence cela arrive-t-il ? ».

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
Maintenant, vous verrez si cette API ralentit votre application !

## Supervision de la base de données

Suivez les requêtes PDO comme ceci :

```php
use flight\database\SimplePdo;

$pdo = new SimplePdo('sqlite:/path/to/db.sqlite', null, null, null, [
	'trackApmQueries' => true, // requis pour capturer les requêtes pour l'APM
]);
$Apm->addPdoConnection($pdo);
```

**Ce que vous obtenez** :
- Texte de la requête (par exemple, `SELECT * FROM users WHERE id = ?`)
- Temps d'exécution (par exemple, 0,015 s)
- Nombre de lignes (par exemple, 42)

**Avertissement** :
- **Facultatif** : Ignorez ceci si vous n'avez pas besoin du suivi de base de données.
- **SimplePdo (recommandé)** : Utilisez `SimplePdo` avec `trackApmQueries => true`. Le `PdoWrapper` obsolète fonctionne toujours (5e argument du constructeur `true`). Le PDO brut du noyau n'est pas encore pris en charge—restez à l'écoute !
- **Avertissement de performance** : Journaliser chaque requête sur un site fortement axé sur la base de données peut ralentir les choses. Utilisez l'échantillonnage (`$Apm = new Apm($ApmLogger, 0.1)`) pour alléger la charge.

**Exemple de sortie** :
- Requête de base de données : `SELECT name FROM products WHERE price > 100`
- Temps : 0,023 s
- Lignes : 15

## Options