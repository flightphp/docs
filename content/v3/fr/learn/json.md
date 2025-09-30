# Wrapper JSON

## Aperçu

La classe `Json` dans Flight fournit une façon simple et cohérente d'encoder et de décoder des données JSON dans votre application. Elle enveloppe les fonctions JSON natives de PHP avec une meilleure gestion des erreurs et certains paramètres par défaut utiles, rendant plus facile et plus sûr de travailler avec JSON.

## Comprendre

Travailler avec JSON est très courant dans les applications PHP modernes, surtout lors de la construction d'API ou de la gestion de requêtes AJAX. La classe `Json` centralise tout l'encodage et le décodage JSON, afin que vous n'ayez pas à vous soucier de cas limites étranges ou d'erreurs cryptiques des fonctions intégrées de PHP.

Fonctionnalités clés :
- Gestion cohérente des erreurs (lève des exceptions en cas d'échec)
- Options par défaut pour l'encodage/décodage (comme les barres obliques non échappées)
- Méthodes utilitaires pour l'impression formatée et la validation

## Utilisation de base

### Encodage des données en JSON

Pour convertir des données PHP en une chaîne JSON, utilisez `Json::encode()` :

```php
use flight\util\Json;

$data = [
  'framework' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$json = Json::encode($data);
echo $json;
// Output: {"framework":"Flight","version":3,"features":["routing","views","extending"]}
```

Si l'encodage échoue, vous obtiendrez une exception avec un message d'erreur utile.

### Impression formatée

Voulez-vous que votre JSON soit lisible par un humain ? Utilisez `prettyPrint()` :

```php
echo Json::prettyPrint($data);
/*
{
  "framework": "Flight",
  "version": 3,
  "features": [
    "routing",
    "views",
    "extending"
  ]
}
*/
```

### Décodage de chaînes JSON

Pour convertir une chaîne JSON en données PHP, utilisez `Json::decode()` :

```php
$json = '{"framework":"Flight","version":3}';
$data = Json::decode($json);
echo $data->framework; // Output: Flight
```

Si vous voulez un tableau associatif au lieu d'un objet, passez `true` comme second argument :

```php
$data = Json::decode($json, true);
echo $data['framework']; // Output: Flight
```

Si le décodage échoue, vous obtiendrez une exception avec un message d'erreur clair.

### Validation de JSON

Vérifiez si une chaîne est un JSON valide :

```php
if (Json::isValid($json)) {
  // C'est valide !
} else {
  // Pas un JSON valide
}
```

### Obtenir la dernière erreur

Si vous voulez vérifier le dernier message d'erreur JSON (des fonctions PHP natives) :

```php
$error = Json::getLastError();
if ($error !== '') {
  echo "Last JSON error: $error";
}
```

## Utilisation avancée

Vous pouvez personnaliser les options d'encodage et de décodage si vous avez besoin de plus de contrôle (voir [les options de json_encode de PHP](https://www.php.net/manual/en/json.constants.php)) :

```php
// Encoder avec l'option JSON_HEX_TAG
$json = Json::encode($data, JSON_HEX_TAG);

// Décoder avec une profondeur personnalisée
$data = Json::decode($json, false, 1024);
```

## Voir aussi

- [Collections](/learn/collections) - Pour travailler avec des données structurées qui peuvent être facilement converties en JSON.
- [Configuration](/learn/configuration) - Comment configurer votre application Flight.
- [Extending](/learn/extending) - Comment ajouter vos propres utilitaires ou surcharger les classes de base.

## Dépannage

- Si l'encodage ou le décodage échoue, une exception est levée — enveloppez vos appels dans try/catch si vous voulez gérer les erreurs gracieusement.
- Si vous obtenez des résultats inattendus, vérifiez vos données pour des références circulaires ou des caractères non-UTF8.
- Utilisez `Json::isValid()` pour vérifier si une chaîne est un JSON valide avant de la décoder.

## Journal des modifications

- v3.16.0 - Ajout de la classe utilitaire wrapper JSON.