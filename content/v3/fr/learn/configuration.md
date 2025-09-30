# Configuration

## Aperçu 

Flight fournit un moyen simple de configurer divers aspects du framework pour répondre aux besoins de votre application. Certains sont définis par défaut, mais vous pouvez les remplacer au besoin. Vous pouvez également définir vos propres variables pour les utiliser dans toute votre application.

## Comprendre

Vous pouvez personnaliser certains comportements de Flight en définissant des valeurs de configuration
via la méthode `set`.

```php
Flight::set('flight.log_errors', true);
```

Dans le fichier `app/config/config.php`, vous pouvez voir toutes les variables de configuration par défaut disponibles.

## Utilisation de base

### Options de configuration Flight

Voici une liste de toutes les options de configuration disponibles :

- **flight.base_url** `?string` - Remplacer l'URL de base de la requête si Flight s'exécute dans un sous-répertoire. (par défaut : null)
- **flight.case_sensitive** `bool` - Correspondance sensible à la casse pour les URL. (par défaut : false)
- **flight.handle_errors** `bool` - Permettre à Flight de gérer toutes les erreurs en interne. (par défaut : true)
  - Si vous voulez que Flight gère les erreurs au lieu du comportement par défaut de PHP, cela doit être true.
  - Si vous avez [Tracy](/awesome-plugins/tracy) installé, vous voulez définir cela à false pour que Tracy puisse gérer les erreurs.
  - Si vous avez le plugin [APM](/awesome-plugins/apm) installé, vous voulez définir cela à true pour que l'APM puisse journaliser les erreurs.
- **flight.log_errors** `bool` - Journaliser les erreurs dans le fichier de journal des erreurs du serveur web. (par défaut : false)
  - Si vous avez [Tracy](/awesome-plugins/tracy) installé, Tracy journalisera les erreurs en fonction des configurations de Tracy, pas de cette configuration.
- **flight.views.path** `string` - Répertoire contenant les fichiers de modèle de vue. (par défaut : ./views)
- **flight.views.extension** `string` - Extension des fichiers de modèle de vue. (par défaut : .php)
- **flight.content_length** `bool` - Définir l'en-tête `Content-Length`. (par défaut : true)
  - Si vous utilisez [Tracy](/awesome-plugins/tracy), cela doit être défini à false pour que Tracy puisse s'afficher correctement.
- **flight.v2.output_buffering** `bool` - Utiliser le tamponnage de sortie legacy. Voir [migration vers v3](migrating-to-v3). (par défaut : false)

### Configuration du chargeur

Il y a en outre une autre option de configuration pour le chargeur. Cela vous permettra 
de charger automatiquement les classes avec `_` dans le nom de la classe.

```php
// Activer le chargement de classes avec des underscores
// Par défaut true
Loader::$v2ClassLoading = false;
```

### Variables

Flight vous permet de sauvegarder des variables pour qu'elles puissent être utilisées n'importe où dans votre application.

```php
// Sauvegarder votre variable
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

> **Note :** Le fait que vous puissiez définir une variable ne signifie pas que vous devriez le faire. Utilisez cette fonctionnalité avec parcimonie. La raison est que tout ce qui est stocké ici devient une variable globale. Les variables globales sont mauvaises car elles peuvent être modifiées de n'importe où dans votre application, rendant difficile la traque des bugs. De plus, cela peut compliquer des choses comme [les tests unitaires](/guides/unit-testing).

### Erreurs et exceptions

Toutes les erreurs et exceptions sont capturées par Flight et passées à la méthode `error`.
si `flight.handle_errors` est défini à true.

Le comportement par défaut est d'envoyer une réponse générique `HTTP 500 Internal Server Error`
avec des informations d'erreur.

Vous pouvez [remplacer](/learn/extending) ce comportement pour vos propres besoins :

```php
Flight::map('error', function (Throwable $error) {
  // Gérer l'erreur
  echo $error->getTraceAsString();
});
```

Par défaut, les erreurs ne sont pas journalisées sur le serveur web. Vous pouvez activer cela en
modifiant la configuration :

```php
Flight::set('flight.log_errors', true);
```

#### 404 Non trouvé

Quand une URL ne peut pas être trouvée, Flight appelle la méthode `notFound`. Le comportement
par défaut est d'envoyer une réponse `HTTP 404 Not Found` avec un message simple.

Vous pouvez [remplacer](/learn/extending) ce comportement pour vos propres besoins :

```php
Flight::map('notFound', function () {
  // Gérer non trouvé
});
```

## Voir aussi
- [Étendre Flight](/learn/extending) - Comment étendre et personnaliser les fonctionnalités de base de Flight.
- [Tests unitaires](/guides/unit-testing) - Comment écrire des tests unitaires pour votre application Flight.
- [Tracy](/awesome-plugins/tracy) - Un plugin pour la gestion avancée des erreurs et le débogage.
- [Extensions Tracy](/awesome-plugins/tracy_extensions) - Extensions pour intégrer Tracy avec Flight.
- [APM](/awesome-plugins/apm) - Un plugin pour la surveillance des performances de l'application et le suivi des erreurs.

## Dépannage
- Si vous avez des problèmes pour trouver toutes les valeurs de votre configuration, vous pouvez faire `var_dump(Flight::get());`

## Journal des modifications
- v3.5.0 - Ajout de la configuration pour `flight.v2.output_buffering` pour supporter le comportement de tamponnage de sortie legacy.
- v2.0 - Configurations de base ajoutées.