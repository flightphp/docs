```fr
# Configuration

Vous pouvez personnaliser certains comportements de Flight en définissant des valeurs de configuration via la méthode `set`.

```php
Flight::set('flight.log_errors', true);
```

## Paramètres de Configuration Disponibles

Voici une liste de tous les paramètres de configuration disponibles :

- **flight.base_url** - Remplace l'URL de base de la demande. (par défaut : null)
- **flight.case_sensitive** - Correspondance sensible à la casse pour les URL. (par défaut : false)
- **flight.handle_errors** - Autorise Flight à gérer toutes les erreurs en interne. (par défaut : true)
- **flight.log_errors** - Journalise les erreurs dans le fichier de journal d'erreurs du serveur web. (par défaut : false)
- **flight.views.path** - Répertoire contenant les fichiers de modèles de vue. (par défaut : ./views)
- **flight.views.extension** - Extension du fichier de modèle de vue. (par défaut : .php)

## Variables

Flight vous permet de sauvegarder des variables afin qu'elles puissent être utilisées n'importe où dans votre application.

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

Flight utilise également des variables à des fins de configuration.

```php
Flight::set('flight.log_errors', true);
```

## Gestion des Erreurs

### Erreurs et Exceptions

Toutes les erreurs et exceptions sont attrapées par Flight et transmises à la méthode `error`.
Le comportement par défaut est d'envoyer une réponse générique `HTTP 500 Internal Server Error` avec quelques informations sur l'erreur.

Vous pouvez remplacer ce comportement selon vos besoins :

```php
Flight::map('error', function (Throwable $error) {
  // Gérer l'erreur
  echo $error->getTraceAsString();
});
```

Par défaut, les erreurs ne sont pas journalisées sur le serveur web. Vous pouvez activer ceci en changeant la configuration :

```php
Flight::set('flight.log_errors', true);
```

### Page Non Trouvée

Lorsqu'une URL ne peut être trouvée, Flight appelle la méthode `notFound`. Le comportement par défaut est d'envoyer une réponse `HTTP 404 Not Found` avec un message simple.

Vous pouvez remplacer ce comportement selon vos besoins :

```php
Flight::map('notFound', function () {
  // Gérer la page non trouvée
});
```