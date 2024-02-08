# Configuration

Vous pouvez personnaliser certains comportements de Flight en définissant des valeurs de configuration via la méthode `set`.

```php
Flight::set('flight.log_errors', true);
```

## Paramètres de configuration disponibles

Ce qui suit est une liste de tous les paramètres de configuration disponibles:

- **flight.base_url** - Remplace l'URL de base de la requête. (par défaut: null)
- **flight.case_sensitive** - Correspondance sensible à la casse pour les URL. (par défaut: false)
- **flight.handle_errors** - Permet à Flight de gérer toutes les erreurs en interne. (par défaut: true)
- **flight.log_errors** - Journalise les erreurs dans le fichier de log d'erreurs du serveur web. (par défaut: false)
- **flight.views.path** - Répertoire contenant les fichiers de modèle de vue. (par défaut: ./views)
- **flight.views.extension** - Extension des fichiers de modèle de vue. (par défaut: .php)

## Variables

Flight vous permet de sauvegarder des variables afin qu'elles puissent être utilisées n'importe où dans votre application.

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

Flight utilise également des variables à des fins de configuration.

```php
Flight::set('flight.log_errors', true);
```

## Gestion des erreurs

### Erreurs et Exceptions

Toutes les erreurs et exceptions sont interceptées par Flight et transmises à la méthode `error`.
Le comportement par défaut est d'envoyer une réponse générique `HTTP 500 Internal Server Error`
avec quelques informations sur l'erreur.

Vous pouvez remplacer ce comportement selon vos besoins :

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

### Introuvable

Lorsqu'une URL ne peut être trouvée, Flight appelle la méthode `notFound`.
Le comportement par défaut est d'envoyer une réponse `HTTP 404 Not Found` avec un message simple.

Vous pouvez remplacer ce comportement selon vos besoins :

```php
Flight::map('notFound', function () {
  // Gérer introuvable
});
```  