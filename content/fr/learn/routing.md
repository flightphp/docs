# Routage

> **Remarque :** Vous voulez en savoir plus sur le routage ? Consultez la page ["pourquoi un framework ?"](/learn/why-frameworks) pour une explication plus approfondie.

Le routage de base dans Flight est réalisé en associant un motif d'URL à une fonction de rappel ou à un tableau d'une classe et d'une méthode.

```php
Flight::route('/', function(){
    echo 'Bonjour le monde !';
});
```

> Les routes sont associées dans l'ordre où elles sont définies. La première route correspondant à une requête sera invoquée.

### Rappels/Fonctions
Le rappel peut être n'importe quel objet appelable. Vous pouvez donc utiliser une fonction normale :

```php
function bonjour(){
    echo 'Bonjour le monde !';
}

Flight::route('/', 'bonjour');
```

### Classes
Vous pouvez également utiliser une méthode statique d'une classe :

```php
class Salutation {
    public static function bonjour() {
        echo 'Bonjour le monde !';
    }
}

Flight::route('/', [ 'Salutation','bonjour' ]);
```

Ou en créant d'abord un objet puis en appelant la méthode :

```php

// Greeting.php
class Salutation
{
    public function __construct() {
        $this->name = 'Jean Dupont';
    }

    public function bonjour() {
        echo "Bonjour, {$this->name} !";
    }
}

// index.php
$salutation = new Salutation();

Flight::route('/', [ $salutation, 'bonjour' ]);
// Vous pouvez également le faire sans créer d'abord l'objet
// Remarque : Aucun argument ne sera injecté dans le constructeur
Flight::route('/', [ 'Salutation', 'bonjour' ]);
```