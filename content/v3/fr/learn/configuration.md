# Configuration

## Aperçu

Flight offre un moyen simple de configurer divers aspects du framework pour répondre aux besoins de votre application. Certains sont définis par défaut, mais vous pouvez les remplacer selon vos besoins. Vous pouvez également définir vos propres variables à utiliser dans toute votre application.

Une configuration claire et stratifiée (valeurs par défaut des fichiers + secrets d'environnement) aide également les [outils de codage IA](/learn/ai) : les agents apprennent un emplacement pour les littéraux et un emplacement pour les secrets, au lieu d'inventer des lectures `$_ENV` dans les contrôleurs.

## Compréhension

Vous pouvez personnaliser certains comportements de Flight en définissant des valeurs de configuration
via la méthode `set`.

```php
Flight::set('flight.log_errors', true);
```

Dans une application structurée (y compris le [skeleton](https://github.com/flightphp/skeleton)), vous chargez généralement les paramètres du projet depuis `app/config/config.php`, puis appliquez les clés pertinentes sur l'Engine (par exemple `flight.base_url`, `flight.views.path`). Vous pouvez également injecter un petit objet de configuration dans les contrôleurs au lieu de lire des globales partout—plus pratique pour les tests et pour les agents qui suivent `AGENTS.md`.

## Utilisation de base

### Options de configuration de Flight

Voici une liste de tous les paramètres de configuration disponibles :

- **flight.base_url** `?string` - Remplace l'URL de base de la requête si Flight s'exécute dans un sous-répertoire. (valeur par défaut : null)
- **flight.case_sensitive** `bool` - Correspondance sensible à la casse pour les URL. (valeur par défaut : false)
- **flight.handle_errors** `bool` - Permet à Flight de gérer toutes les erreurs en interne. (valeur par défaut : true)
  - Si vous voulez que Flight gère les erreurs au lieu du comportement PHP par défaut, ceci doit être défini sur true.
  - Si vous avez [Tracy](/awesome-plugins/tracy) installé, vous devez définir ceci sur false afin que Tracy puisse gérer les erreurs.
  - Si vous avez le plugin [APM](/awesome-plugins/apm) installé, vous devez définir ceci sur true afin que l'APM puisse journaliser les erreurs.
- **flight.log_errors** `bool` - Journalise les erreurs dans le fichier de log d'erreurs du serveur web. (valeur par défaut : false)
  - Si vous avez [Tracy](/awesome-plugins/tracy) installé, Tracy journalisera les erreurs selon les configurations de Tracy, et non selon cette configuration.
- **flight.debug** `bool` - Affiche des informations détaillées sur l'erreur (message d'exception, code et trace de pile) dans le navigateur lorsqu'une erreur se produit. (valeur par défaut : false)
  - **N'activez jamais ceci en production** — cela expose des détails internes de l'application. Utilisez-le uniquement pour le développement local ou la préproduction.
  - Lorsque `false`, un message générique `500 Internal Server Error` est affiché à la place. Associez-le à `flight.log_errors` pour capturer les erreurs côté serveur.
- **flight.allow_method_override** `bool` - Autorise le remplacement de la méthode HTTP via l'en-tête de requête `X-HTTP-Method-Override` ou un champ `_method` dans le corps POST. (valeur par défaut : true)
  - **Il est recommandé de définir ceci sur `false`** pour les applications qui n'ont pas besoin de falsification de méthode basée sur des formulaires HTML, car cela empêche les clients de forger des requêtes `DELETE` ou `PUT` via un formulaire POST standard.
  - Voir [Sécurité](/learn/security#flight-configuration-hardening) pour plus de détails.
- **flight.views.path** `string` - Répertoire contenant les fichiers de modèle de vue. (valeur par défaut : ./views)
- **flight.views.extension** `string` - Extension des fichiers de modèle de vue. (valeur par défaut : `.php` ; le skeleton officiel définit ceci sur `.twig` lors de l'utilisation de Twig)
- **flight.views.restrict_to_path** `bool` - Lorsque `true`, le `View` natif de Flight n'accepte que les fichiers de modèle qui se résolvent à l'intérieur de `flight.views.path`. (valeur par défaut : `false`). **Activez ceci** pour les applications qui utilisent les vues natives. Voir [Sécurité](/learn/security#flightviewsrestrict_to_path).
- **flight.content_length** `bool` - Définit l'en-tête `Content-Length`. (valeur par défaut : true)
  - Si vous utilisez [Tracy](/awesome-plugins/tracy), ceci doit être défini sur false afin que Tracy puisse s'afficher correctement.
- **flight.v2.output_buffering** `bool` - Utilise la mise en mémoire tampon de sortie héritée. Voir [migration vers la v3](migrating-to-v3). (valeur par défaut : false)

### Configuration du Loader

Il existe en outre un autre paramètre de configuration pour le loader. Cela vous permettra
de charger automatiquement des classes avec `_` dans le nom de classe.

```php
// Activer le chargement de classes avec des underscores
// Valeur par défaut : true
Loader::$v2ClassLoading = false;
```

Rappelez-vous que l'[autoloading](/learn/autoloading) dépend aussi de la **casse des dossiers** correspondant à vos namespaces—en particulier avec la disposition `App\` + `app/Controller/` du skeleton.

### Configuration du projet et `.env` (modèle skeleton)

Le cœur de Flight n'exige pas de fichiers `.env`. De nombreuses applications utilisent uniquement un tableau de configuration PHP. Le skeleton officiel stratifie la configuration afin que les secrets restent hors de git tandis que Runway peut toujours réécrire la configuration **littérale** en toute sécurité :

1. **`.env` / environnement réel** — secrets et surcharges de déploiement (ignorés par git).
2. **`app/config/config.php`** — valeurs par défaut littérales sous forme de tableau PHP (copiées depuis `config_sample.php`). Préférez **aucune** expression `$_ENV[...]` dans ce fichier : des outils comme `runway config:set` peuvent le réécrire en valeurs statiques et pourraient intégrer des secrets dans le fichier.
3. **Fusion au bootstrap** — l'environnement gagne pour les clés mappées ; le code de l'application lit un objet de configuration ou `$app->get()`, pas `$_ENV` dans les contrôleurs.

Exemple de structure de `config_sample.php` / `config.php` (simplifié) :

```php
<?php
// Littéraux uniquement — les secrets appartiennent à .env pour le workflow du skeleton
return [
	'app' => [
		'env' => 'development',
		'debug' => true,
		'base_url' => '/',
		'timezone' => 'UTC',
	],
	'database' => [
		'driver' => 'sqlite', // ou mysql, ou '' pour désactiver
		'host' => 'localhost',
		'dbname' => '',
		'user' => '',
		'password' => '',
		'file_path' => __DIR__ . '/../../database.sqlite',
	],
	// ...
];
```

```bash
# .env.example → .env (skeleton)
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

Cette séparation est délibérée pour les [projets adaptés à l'IA](/learn/ai) : les instructions peuvent dire « valeurs par défaut dans `config.php`, secrets dans `.env`, injecter Config / Engine—ne jamais inventer d'accès à l'environnement dans un contrôleur. » Les applications existantes peuvent ignorer entièrement `.env` et conserver un seul fichier de configuration.

### Variables

Flight vous permet d'enregistrer des variables afin qu'elles puissent être utilisées n'importe où dans votre application.

```php
// Enregistrez votre variable
Flight::set('id', 123);

// Ailleurs dans votre application
$id = Flight::get('id');
```
Pour voir si une variable a été définie, vous pouvez faire :

```php
if (Flight::has('id')) {
  // Faire quelque chose
}
```

Vous pouvez effacer une variable en faisant :

```php
// Efface la variable id
Flight::clear('id');

// Efface toutes les variables
Flight::clear();
```

> **Remarque :** Ce n'est pas parce que vous pouvez définir une variable que vous devriez le faire. Utilisez cette fonctionnalité avec parcimonie. La raison en est que tout ce qui y est stocké devient une variable globale. Les variables globales sont mauvaises parce qu'elles peuvent être modifiées n'importe où dans votre application, ce qui rend difficile la traque des bugs. De plus, cela peut compliquer des choses comme les [tests unitaires](/guides/unit-testing). Préférez l'injection par constructeur (comme dans la configuration skeleton + Dice) pour les services et la configuration dont les contrôleurs ont besoin.

### Erreurs et exceptions

Toutes les erreurs et exceptions sont interceptées par Flight et transmises à la méthode `error`.
si `flight.handle_errors` est défini sur true.

Le comportement par défaut est d'envoyer une réponse générique `HTTP 500 Internal Server Error`
avec quelques informations sur l'erreur.

Vous pouvez [remplacer](/learn/extending) ce comportement selon vos propres besoins :

```php
Flight::map('error', function (Throwable $error) {
  // Gérer l'erreur
  echo $error->getTraceAsString();
});
```

Par défaut, les erreurs ne sont pas journalisées sur le serveur web. Vous pouvez l'activer en
modifiant la configuration :

```php
Flight::set('flight.log_errors', true);
```

#### 404 Not Found

Lorsqu'une URL est introuvable, Flight appelle la méthode `notFound`. Le comportement
par défaut est d'envoyer une réponse `HTTP 404 Not Found` avec un message simple.

Vous pouvez [remplacer](/learn/extending) ce comportement selon vos propres besoins :

```php
Flight::map('notFound', function () {
  // Gérer le cas introuvable
});
```

## Voir aussi
- [Installation](/install) - Configuration du skeleton, `.env` et structure du bootstrap.
- [Autoloading](/learn/autoloading) - Namespaces et casse des dossiers.
- [Extension de Flight](/learn/extending) - Comment étendre et personnaliser les fonctionnalités principales de Flight.
- [Tests unitaires](/guides/unit-testing) - Comment écrire des tests unitaires pour votre application Flight.
- [IA et expérience développeur](/learn/ai) - `AGENTS.md` et instructions de projet cohérentes.
- [Tracy](/awesome-plugins/tracy) - Un plugin pour la gestion avancée des erreurs et le débogage.
- [Extensions Tracy](/awesome-plugins/tracy_extensions) - Extensions pour intégrer Tracy à Flight.
- [APM](/awesome-plugins/apm) - Un plugin pour la surveillance des performances applicatives et le suivi des erreurs.
- [Sécurité](/learn/security) - Indicateurs de durcissement et gestion des secrets.

## Dépannage
- Si vous avez des difficultés à découvrir toutes les valeurs de votre configuration, vous pouvez faire `var_dump(Flight::get());`
- Si Runway ou l'outillage de déploiement a réécrit `config.php`, confirmez que les secrets n'ont pas été commités—gardez-les dans `.env` ou dans l'environnement réel lorsque vous utilisez le modèle skeleton.

## Journal des modifications
- Docs – Mention de `flight.views.restrict_to_path` à côté des paramètres de chemin des vues.
- Docs – Documentation de la configuration de style skeleton / de la stratification `.env` et de l'extension de vue Twig par défaut pour les nouveaux projets.
- v3.18.1 - Ajout des options de configuration `flight.debug` et `flight.allow_method_override`.
- v3.5.0 - Ajout de la configuration pour `flight.v2.output_buffering` afin de prendre en charge le comportement hérité de mise en mémoire tampon de sortie.
- v2.0 - Configurations principales ajoutées.