# Configuration

## Aperçu

Flight offre un moyen simple de configurer divers aspects du framework pour répondre aux besoins de votre application. Certains sont définis par défaut, mais vous pouvez les remplacer selon vos besoins. Vous pouvez également définir vos propres variables à utiliser dans toute votre application.

Une configuration claire et en couches (fichier de valeurs par défaut + secrets d'environnement) aide également les [outils de codage IA](/learn/ai) : les agents apprennent à connaître un emplacement pour les littéraux et un pour les secrets, plutôt que d'inventer des lectures `$_ENV` dans les contrôleurs.

## Compréhension

Vous pouvez personnaliser certains comportements de Flight en définissant des valeurs de configuration via la méthode `set`.

```php
Flight::set('flight.log_errors', true);
```

Dans une application structurée (y compris le [skeleton](https://github.com/flightphp/skeleton)), vous chargez généralement les paramètres du projet depuis `app/config/config.php`, puis vous appliquez les clés pertinentes sur le moteur (par exemple `flight.base_url`, `flight.views.path`). Vous pouvez également injecter un petit objet de configuration dans les contrôleurs au lieu de lire des variables globales partout — plus convivial pour les tests et pour les agents qui suivent `AGENTS.md`.

## Utilisation de base

### Options de configuration de Flight

Voici une liste de tous les paramètres de configuration disponibles :

- **flight.base_url** `?string` - Remplacez l'URL de base de la requête si Flight s'exécute dans un sous-répertoire. (défaut : null)
- **flight.case_sensitive** `bool` - Correspondance sensible à la casse pour les URL. (défaut : false)
- **flight.handle_errors** `bool` - Permet à Flight de gérer toutes les erreurs en interne. (défaut : true)
  - Si vous souhaitez que Flight gère les erreurs au lieu du comportement PHP par défaut, ce paramètre doit être défini sur true.
  - Si vous avez [Tracy](/awesome-plugins/tracy) installé, vous devez le définir sur false afin que Tracy puisse gérer les erreurs.
  - Si vous avez le plugin [APM](/awesome-plugins/apm) installé, vous devez le définir sur true afin que l'APM puisse journaliser les erreurs.
- **flight.log_errors** `bool` - Journalise les erreurs dans le fichier journal des erreurs du serveur web. (défaut : false)
  - Si vous avez [Tracy](/awesome-plugins/tracy) installé, Tracy journalisera les erreurs selon les configurations de Tracy, et non selon cette configuration.
- **flight.debug** `bool` - Affiche des informations d'erreur détaillées (message d'exception, code et trace de pile) dans le navigateur lorsqu'une erreur survient. (défaut : false)
  - **Ne l'activez jamais en production** — cela divulgue des détails internes de l'application. Utilisez-le uniquement pour le développement local ou la préproduction.
  - Lorsque `false`, une erreur générique `500 Internal Server Error` est affichée à la place. Combinez avec `flight.log_errors` pour capturer les erreurs côté serveur.
- **flight.allow_method_override** `bool` - Permet de remplacer la méthode HTTP via l'en-tête de requête `X-HTTP-Method-Override` ou un champ `_method` dans le corps de la requête POST. (défaut : true)
  - **Il est recommandé de définir ce paramètre sur `false`** pour les applications qui n'ont pas besoin de l'usurpation de méthode basée sur des formulaires HTML, car cela empêche les clients de forger des requêtes `DELETE` ou `PUT` via un formulaire POST standard.
  - Voir [Sécurité](/learn/security#flight-configuration-hardening) pour plus de détails.
- **flight.views.path** `string` - Répertoire contenant les fichiers de modèles de vue. (défaut : ./views)
- **flight.views.extension** `string` - Extension de fichier des modèles de vue. (défaut : `.php` ; le skeleton officiel définit cette valeur sur `.twig` lorsqu'il utilise Twig)
- **flight.views.restrict_to_path** `bool` - Lorsque `true`, la vue native `View` de Flight n'accepte que les fichiers de modèles qui se résolvent dans `flight.views.path`. (défaut : `false`). **Activez cette option** pour les applications qui utilisent des vues natives. Voir [Sécurité](/learn/security#flightviewsrestrict_to_path).
- **flight.content_length** `bool` - Définit l'en-tête `Content-Length`. (défaut : true)
  - Si vous utilisez [Tracy](/awesome-plugins/tracy), ce paramètre doit être défini sur false afin que Tracy puisse s'afficher correctement.
- **flight.v2.output_buffering** `bool` - Utilise la mise en tampon de sortie héritée. Voir [migration vers v3](migrating-to-v3). (défaut : false)

### Configuration du chargeur

Il existe également un autre paramètre de configuration pour le chargeur. Cela vous permettra de charger automatiquement les classes avec `_` dans le nom de la classe.

```php
// Active le chargement des classes avec des underscores
// Défini sur true par défaut
Loader::$v2ClassLoading = false;
```

Rappelez-vous que l'[autoloading](/learn/autoloading) dépend également de la **casse des dossiers** correspondant à vos espaces de noms — en particulier avec la structure `App\` + `app/Controller/` du skeleton.

### Configuration de projet et `.env` (modèle skeleton)

Le cœur de Flight n'exige pas de fichiers `.env`. De nombreuses applications utilisent uniquement un tableau de configuration PHP. Le skeleton officiel superpose la configuration afin que les secrets restent hors de git tout en permettant à Runway de réécrire en toute sécurité la configuration **littérale** :

1. **`.env` / environnement réel** — secrets et remplacements de déploiement (ignorés par git).
2. **`app/config/config.php`** — valeurs par défaut littérales du tableau PHP (copiées depuis `config_sample.php`). Préférez **aucune** expression `$_ENV[...]` dans ce fichier : des outils comme `runway config:set` peuvent le réécrire sous forme de valeurs statiques et pourraient graver des secrets dans le fichier.
3. **Fusion au démarrage** — l'environnement gagne pour les clés mappées ; le code de l'application lit un objet de configuration ou `$app->get()`, et non `$_ENV` dans les contrôleurs.

Exemple de structure de `config_sample.php` / `config.php` (simplifié) :

```php
<?php
// Uniquement des littéraux — les secrets appartiennent à .env pour le workflow skeleton
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

Cette répartition est délibérée pour les [projets adaptés à l'IA](/learn/ai) : les instructions peuvent dire « valeurs par défaut dans `config.php`, secrets dans `.env`, injectez Config / Engine — n'inventez jamais l'accès à l'environnement dans un contrôleur. » Les applications existantes peuvent ignorer complètement `.env` et conserver un fichier de configuration unique.

### Variables

Flight vous permet d'enregistrer des variables afin de pouvoir les utiliser n'importe où dans votre application.

```php
// Enregistrez votre variable
Flight::set('id', 123);

// Ailleurs dans votre application
$id = Flight::get('id');
```

Pour vérifier si une variable a été définie, vous pouvez faire :

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

> **Note :** Ce n'est pas parce que vous pouvez définir une variable que vous devriez le faire. Utilisez cette fonctionnalité avec parcimonie. La raison est que tout ce qui est stocké ici devient une variable globale. Les variables globales sont mauvaises car elles peuvent être modifiées depuis n'importe où dans votre application, ce qui rend difficile la recherche de bogues. De plus, cela peut compliquer des choses comme les [tests unitaires](/guides/unit-testing). Privilégiez l'injection par constructeur (comme dans la configuration skeleton + Dice) pour les services et la configuration dont les contrôleurs ont besoin.

### Erreurs et exceptions

Toutes les erreurs et exceptions sont interceptées par Flight et transmises à la méthode `error` si `flight.handle_errors` est défini sur true.

Le comportement par défaut consiste à envoyer une réponse générique `HTTP 500 Internal Server Error` avec quelques informations sur l'erreur.

Vous pouvez [remplacer](/learn/extending) ce comportement selon vos besoins :

```php
Flight::map('error', function (Throwable $error) {
  // Gérer l'erreur
  echo $error->getTraceAsString();
});
```

Par défaut, les erreurs ne sont pas journalisées sur le serveur web. Vous pouvez activer cela en modifiant la configuration :

```php
Flight::set('flight.log_errors', true);
```

#### 404 Introuvable

Lorsqu'une URL est introuvable, Flight appelle la méthode `notFound`. Le comportement par défaut consiste à envoyer une réponse `HTTP 404 Not Found` avec un message simple.

Vous pouvez [remplacer](/learn/extending) ce comportement selon vos besoins :

```php
Flight::map('notFound', function () {
  // Gérer la page introuvable
});
```

## Voir aussi
- [Installation](/install) - Configuration du skeleton, `.env` et structure de démarrage.
- [Autoloading](/learn/autoloading) - Espaces de noms et casse des dossiers.
- [Extending Flight](/learn/extending) - Comment étendre et personnaliser les fonctionnalités de base de Flight.
- [Unit Testing](/guides/unit-testing) - Comment écrire des tests unitaires pour votre application Flight.
- [AI & Developer Experience](/learn/ai) - `AGENTS.md` et instructions de projet cohérentes.
- [Tracy](/awesome-plugins/tracy) - Un plugin pour la gestion avancée des erreurs et le débogage.
- [Tracy Extensions](/awesome-plugins/tracy_extensions) - Extensions pour intégrer Tracy avec Flight.
- [APM](/awesome-plugins/apm) - Un plugin pour la surveillance des performances et le suivi des erreurs.
- [Security](/learn/security) - Durcissement des indicateurs et gestion des secrets.

## Dépannage
- Si vous avez du mal à connaître toutes les valeurs de votre configuration, vous pouvez faire `var_dump(Flight::get());`
- Si Runway ou un outil de déploiement a réécrit `config.php`, vérifiez que les secrets n'ont pas été commités — conservez-les dans `.env` ou dans l'environnement réel lorsque vous utilisez le modèle skeleton.

## Journal des modifications
- Docs – Ajout de la mention `flight.views.restrict_to_path` à côté des paramètres de chemin des vues.
- Docs – Documentation de la configuration de type skeleton / couches `.env` et de l'extension de vue Twig par défaut pour les nouveaux projets.
- v3.18.1 - Ajout des options de configuration `flight.debug` et `flight.allow_method_override`.
- v3.5.0 - Ajout de la configuration pour `flight.v2.output_buffering` afin de prendre en charge le comportement de mise en tampon de sortie héritée.
- v2.0 - Ajout des configurations de base.