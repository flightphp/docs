# Documentation APM FlightPHP

Bienvenue dans FlightPHP APM—le coach personnel de performance de votre application ! Ce guide est votre feuille de route pour configurer, utiliser et maîtriser la surveillance de performance des applications (APM) avec FlightPHP. Que vous cherchiez des requêtes lentes ou que vous souhaitiez vous plonger dans des graphiques de latence, nous avons ce qu'il vous faut. Rendons votre application plus rapide, vos utilisateurs plus heureux et vos sessions de débogage plus simples !

## Pourquoi l'APM est important

Imaginez ceci : votre application est un restaurant occupé. Sans moyen de suivre combien de temps prennent les commandes ou où la cuisine est en difficulté, vous devinez pourquoi les clients partent de mauvaise humeur. L'APM est votre sous-chef—il surveille chaque étape, des requêtes entrantes aux requêtes de base de données, et signale tout ce qui vous ralentit. Les pages lentes perdent des utilisateurs (des études disent que 53% abandonnent si un site met plus de 3 secondes à se charger !), et l'APM vous aide à attraper ces problèmes *avant* qu'ils ne fassent mal. C'est un esprit tranquille proactif—moins de moments "pourquoi cela ne fonctionne-t-il pas ?", plus de gains "regardez comme cela fonctionne bien !".

## Installation

Commencez avec Composer :

```bash
composer require flightphp/apm
```

Vous aurez besoin de :
- **PHP 7.4+** : Garde notre compatibilité avec les distributions Linux LTS tout en prenant en charge PHP moderne.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+** : Le framework léger que nous renforçons.

## Premiers pas

Voici votre guide étape par étape vers l'incroyable APM :

### 1. Enregistrez l'APM

Ajoutez ceci dans votre `index.php` ou un fichier `services.php` pour commencer le suivi :

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**Que se passe-t-il ici ?**
- `LoggerFactory::create()` récupère votre configuration (plus à ce sujet bientôt) et configure un logger—SQLite par défaut.
- `Apm` est la star—il écoute les événements de Flight (requêtes, routes, erreurs, etc.) et collecte des métriques.
- `bindEventsToFlightInstance($app)` lie le tout à votre application Flight.

**Astuce pro : Échantillonnage**
Si votre application est occupée, enregistrer *chaque* requête pourrait surcharger les choses. Utilisez un taux d'échantillonnage (0.0 à 1.0) :

```php
$Apm = new Apm($ApmLogger, 0.1); // Enregistre 10% des requêtes
```

Cela garde la performance rapide tout en vous donnant des données solides.

### 2. Configurez-le

Exécutez ceci pour créer votre `.runway-config.json` :

```bash
php vendor/bin/runway apm:init
```

**Que fait cela ?**
- Lance un assistant demandant d'où proviennent les métriques brutes (source) et où vont les données traitées (destination).
- Le défaut est SQLite—par exemple, `sqlite:/tmp/apm_metrics.sqlite` pour la source, une autre pour la destination.
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

**Pourquoi deux emplacements ?**
Les métriques brutes s'accumulent rapidement (pensez aux journaux non filtrés). Le worker les traite en une destination structurée pour le tableau de bord. Cela garde les choses organisées !

### 3. Traitez les métriques avec le worker

Le worker transforme les métriques brutes en données prêtes pour le tableau de bord. Exécutez-le une fois :

```bash
php vendor/bin/runway apm:worker
```

**Que fait-il ?**
- Lit depuis votre source (par exemple, `apm_metrics.sqlite`).
- Traite jusqu'à 100 métriques (taille de lot par défaut) vers votre destination.
- S'arrête quand c'est fait ou s'il n'y a plus de métriques.

**Gardez-le actif**
Pour les applications en direct, vous voudrez un traitement continu. Voici vos options :

- **Mode Daemon** :
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Fonctionne éternellement, traitant les métriques au fur et à mesure. Idéal pour le développement ou les petites configurations.

- **Crontab** :
  Ajoutez ceci à votre crontab (`crontab -e`) :
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  S'exécute chaque minute—parfait pour la production.

- **Tmux/Screen** :
  Démarrez une session détachable :
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, puis D pour détacher ; `tmux attach -t apm-worker` pour se reconnecter
  ```
  Reste actif même si vous vous déconnectez.

- **Ajustements personnalisés** :
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50` : Traite 50 métriques à la fois.
  - `--max_messages 1000` : S'arrête après 1000 métriques.
  - `--timeout 300` : Quitte après 5 minutes.

**Pourquoi s'embêter ?**
Sans le worker, votre tableau de bord est vide. C'est le pont entre les journaux bruts et les informations exploitables.

### 4. Lancez le tableau de bord

Voir les indicateurs de votre application :

```bash
php vendor/bin/runway apm:dashboard
```

**Que fait cela ?**
- Démarre un serveur PHP à `http://localhost:8001/apm/dashboard`.
- Affiche les journaux de requêtes, les routes lentes, les taux d'erreurs, et plus encore.

**Personnalisez-le** :
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0` : Accessible depuis n'importe quelle IP (pratique pour la visualisation à distance).
- `--port 8080` : Utilisez un port différent si 8001 est occupé.
- `--php-path` : Pointe vers PHP si ce n'est pas dans votre PATH.

Accédez à l'URL dans votre navigateur et explorez !

#### Mode de production

Pour la production, vous devrez peut-être essayer quelques techniques pour faire fonctionner le tableau de bord, car il y a probablement des pare-feux et d'autres mesures de sécurité en place. Voici quelques options :

- **Utilisez un reverse proxy** : Configurez Nginx ou Apache pour faire suivre les requêtes au tableau de bord.
- **Tunnel SSH** : Si vous pouvez SSH sur le serveur, utilisez `ssh -L 8080:localhost:8001 youruser@yourserver` pour tunneliser le tableau de bord sur votre machine locale.
- **VPN** : Si votre serveur est derrière un VPN, connectez-vous et accédez directement au tableau de bord.
- **Configurer le pare-feu** : Ouvrez le port 8001 pour votre IP ou le réseau du serveur. (ou quel que soit le port que vous lui avez attribué).
- **Configurer Apache/Nginx** : Si vous avez un serveur web devant votre application, vous pouvez le configurer pour un domaine ou un sous-domaine. Si vous faites cela, vous définirez la racine du document sur `/path/to/your/project/vendor/flightphp/apm/dashboard`

#### Vous voulez un tableau de bord différent ?

Vous pouvez construire votre propre tableau de bord si vous le souhaitez ! Consultez le répertoire vendor/flightphp/apm/src/apm/presenter pour des idées sur la façon de présenter les données pour votre propre tableau de bord !

## Fonctionnalités du tableau de bord

Le tableau de bord est votre QG APM—voici ce que vous verrez :

- **Journal des requêtes** : Chaque requête avec horodatage, URL, code de réponse et temps total. Cliquez sur "Détails" pour middleware, requêtes et erreurs.
- **Requêtes les plus lentes** : Les 5 requêtes prenant le plus de temps (par exemple, “/api/heavy” à 2.5s).
- **Routes les plus lentes** : Les 5 routes par temps moyen—idéal pour repérer les schémas.
- **Taux d'erreur** : Pourcentage de requêtes échouées (par exemple, 2.3% 500s).
- **Percentiles de latence** : Temps de réponse 95e (p95) et 99e (p99)—connaître vos pires scénarios.
- **Graphique des codes de réponse** : Visualisez les 200s, 404s, 500s au fil du temps.
- **Requêtes/middleware longues** : Les 5 appels de base de données les plus lents et couches middleware.
- **Cache Hit/Miss** : À quelle fréquence votre cache fait le travail.

**Extras** :
- Filtrez par "Dernière heure", "Dernier jour" ou "Dernière semaine".
- Basculez en mode sombre pour les sessions tardives.

**Exemple** :
Une requête à `/users` pourrait montrer :
- Temps total : 150ms
- Middleware : `AuthMiddleware->handle` (50ms)
- Requête : `SELECT * FROM users` (80ms)
- Cache : Atteint sur `user_list` (5ms)

## Ajout d'événements personnalisés

Suivez tout—comme un appel API ou un processus de paiement :

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->emit('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Où cela apparaît-il ?**
Dans les détails de la requête du tableau de bord sous "Événements personnalisés"—développables avec un joli formatage JSON.

**Cas d'utilisation** :
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->emit('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Vous verrez maintenant si cette API ralentit votre application !

## Surveillance de base de données

Suivez les requêtes PDO comme ceci :

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**Ce que vous obtenez** :
- Texte de la requête (par exemple, `SELECT * FROM users WHERE id = ?`)
- Temps d'exécution (par exemple, 0.015s)
- Nombre de lignes (par exemple, 42)

**Attention** :
- **Optionnel** : Ignorez cela si vous n'avez pas besoin de suivi DB.
- **PdoWrapper uniquement** : Le PDO de base n'est pas encore connecté—restez à l'écoute !
- **Avertissement de performance** : Enregistrer chaque requête sur un site lourd en base de données peut ralentir les choses. Utilisez l'échantillonnage (`$Apm = new Apm($ApmLogger, 0.1)`) pour alléger la charge.

**Exemple de sortie** :
- Requête : `SELECT name FROM products WHERE price > 100`
- Temps : 0.023s
- Lignes : 15

## Options de worker

Ajustez le worker selon vos préférences :

- `--timeout 300` : S'arrête après 5 minutes—idéal pour les tests.
- `--max_messages 500` : Limite à 500 métriques—garde cela fini.
- `--batch_size 200` : Traite 200 à la fois—équilibre vitesse et mémoire.
- `--daemon` : Fonctionne sans arrêt—idéal pour la surveillance en direct.

**Exemple** :
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Fonctionne pendant une heure, traitant 100 métriques à la fois.

## Résolution de problèmes

Coincé ? Essayez ces éléments :

- **Pas de données au tableau de bord ?**
  - Le worker fonctionne-t-il ? Vérifiez `ps aux | grep apm:worker`.
  - Les chemins de configuration correspondent-ils ? Vérifiez que les DSNs de `.runway-config.json` pointent vers des fichiers réels.
  - Exécutez `php vendor/bin/runway apm:worker` manuellement pour traiter les métriques en attente.

- **Erreurs de worker ?**
  - Jetez un œil à vos fichiers SQLite (par exemple, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Vérifiez les journaux PHP pour des traces de pile.

- **Le tableau de bord ne démarre pas ?**
  - Le port 8001 est-il utilisé ? Utilisez `--port 8080`.
  - PHP introuvable ? Utilisez `--php-path /usr/bin/php`.
  - Pare-feu bloquant ? Ouvrez le port ou utilisez `--host localhost`.

- **Trop lent ?**
  - Abaissez le taux d'échantillonnage : `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Réduisez la taille du lot : `--batch_size 20`.