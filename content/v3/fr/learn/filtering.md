# Filtrage

## Aperçu

Flight vous permet de filtrer les [méthodes mappées](/learn/extending) avant et après leur appel.

## Comprendre
Il n'y a pas de hooks prédéfinis que vous devez mémoriser. Vous pouvez filtrer n'importe laquelle des méthodes par défaut du framework ainsi que n'importe quelles méthodes personnalisées que vous avez mappées.

Une fonction de filtre ressemble à ceci :

```php
/**
 * @param array $params Les paramètres passés à la méthode filtrée.
 * @param string $output (v2 buffering de sortie uniquement) La sortie de la méthode filtrée.
 * @return bool Retournez true/vide ou ne retournez rien pour continuer la chaîne, false pour interrompre la chaîne.
 */
function (array &$params, string &$output): bool {
  // Code de filtre
}
```

En utilisant les variables passées, vous pouvez manipuler les paramètres d'entrée et/ou la sortie.

Vous pouvez faire exécuter un filtre avant une méthode en faisant :

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Faites quelque chose
});
```

Vous pouvez faire exécuter un filtre après une méthode en faisant :

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Faites quelque chose
});
```

Vous pouvez ajouter autant de filtres que vous voulez à n'importe quelle méthode. Ils seront appelés dans l'ordre où ils sont déclarés.

Voici un exemple du processus de filtrage :

```php
// Mappez une méthode personnalisée
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// Ajoutez un filtre avant
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipulez le paramètre
  $params[0] = 'Fred';
  return true;
});

// Ajoutez un filtre après
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipulez la sortie
  $output .= " Have a nice day!";
  return true;
});

// Invoquez la méthode personnalisée
echo Flight::hello('Bob');
```

Cela devrait afficher :

```
Hello Fred! Have a nice day!
```

Si vous avez défini plusieurs filtres, vous pouvez interrompre la chaîne en retournant `false`
dans l'une de vos fonctions de filtre :

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // Cela mettra fin à la chaîne
  return false;
});

// Ceci ne sera pas appelé
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

> **Note :** Les méthodes de base telles que `map` et `register` ne peuvent pas être filtrées car elles
sont appelées directement et non invoquées dynamiquement. Voir [Extending Flight](/learn/extending) pour plus d'informations.

## Voir aussi
- [Extending Flight](/learn/extending)

## Dépannage
- Assurez-vous de retourner `false` depuis vos fonctions de filtre si vous voulez que la chaîne s'arrête. Si vous ne retournez rien, la chaîne continuera.

## Journal des modifications
- v2.0 - Version initiale.