# Tracy

Tracy est un gestionnaire d'erreurs incroyable qui peut être utilisé avec Flight. Il dispose de plusieurs panneaux qui peuvent vous aider à déboguer votre application. Il est également très facile à étendre et à ajouter vos propres panneaux. L'équipe de Flight a créé quelques panneaux spécifiquement pour les projets Flight avec le plugin [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions).

## Installation

Installez avec composer. Et en fait, vous voudrez installer ceci sans la version dev car Tracy est livré avec un composant de gestion des erreurs en production.

```bash
composer require tracy/tracy
```

## Configuration de base

Il y a quelques options de configuration de base pour commencer. Vous pouvez en savoir plus à leur sujet dans la [documentation de Tracy](https://tracy.nette.org/fr/configuring).

```php
require 'vendor/autoload.php';

use Tracy\Debugger;

// Activer Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // parfois il faut être explicite (aussi Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // vous pouvez également fournir un tableau d'adresses IP

// C'est là que les erreurs et exceptions seront enregistrées. Assurez-vous que ce répertoire existe et est accessible en écriture.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // afficher toutes les erreurs
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // toutes les erreurs sauf les notifications obsolètes
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // si la barre Debugger est visible, alors la longueur du contenu ne peut pas être définie par Flight

	// Cela est spécifique à l'extension Tracy pour Flight si vous l'avez incluse
	// sinon commentez ceci.
	new TracyExtensionLoader($app);
}
```

## Conseils utiles

Lorsque vous déboguez votre code, il existe quelques fonctions très utiles pour afficher les données pour vous.

- `bdump($var)` - Cela affichera la variable dans la Barre Tracy dans un panneau séparé.
- `dumpe($var)` - Cela affichera la variable puis arrêtera immédiatement.