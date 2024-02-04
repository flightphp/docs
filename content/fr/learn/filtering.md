# Filtrage

Flight vous permet de filtrer les méthodes avant et après leur appel. Il n'y a pas
de crochets prédéfinis que vous devez mémoriser. Vous pouvez filtrer n'importe laquelle des méthodes par défaut du framework
ainsi que n'importe quelle méthode personnalisée que vous avez mappée.

Une fonction de filtre ressemble à cela :

```php
function (array &$params, string &$output): bool {
  // Code de filtre
}
```

En utilisant les variables passées en paramètre, vous pouvez manipuler les paramètres d'entrée et/ou la sortie.

Vous pouvez exécuter un filtre avant une méthode en faisant :

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Faire quelque chose
});
```

Vous pouvez exécuter un filtre après une méthode en faisant :

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Faire quelque chose
});
```

Vous pouvez ajouter autant de filtres que vous le souhaitez à n'importe quelle méthode. Ils seront appelés dans l'ordre où ils sont déclarés.

Voici un exemple du processus de filtrage :

```php
// Mapper une méthode personnalisée
Flight::map('hello', function (string $name) {
  return "Bonjour, $name!";
});

// Ajouter un filtre avant
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipuler le paramètre
  $params[0] = 'Fred';
  return true;
});

// Ajouter un filtre après
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipuler la sortie
  $output .= " Passez une bonne journée !";
  return true;
});

// Invoquer la méthode personnalisée
echo Flight::hello('Bob');
```

Cela devrait afficher :

```
Bonjour Fred ! Passez une bonne journée !
```

Si vous avez défini plusieurs filtres, vous pouvez interrompre la chaîne en renvoyant `false`
dans l'une de vos fonctions de filtre :

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'un';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'deux';

  // Cela mettra fin à la chaîne
  return false;
});

// Cela ne sera pas appelé
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'trois';
  return true;
});
```

Remarque, les méthodes de base telles que `map` et `register` ne peuvent pas être filtrées car elles
sont appelées directement et non invoquées dynamiquement.