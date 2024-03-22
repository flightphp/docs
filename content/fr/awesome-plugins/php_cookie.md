## Cookies

[overclokk/cookie](https://github.com/overclokk/cookie) est une bibliothèque simple pour gérer les cookies au sein de votre application.

## Installation

L'installation est simple avec composer.

```bash
composer require overclokk/cookie
```

## Utilisation

L'utilisation est aussi simple que d'enregistrer une nouvelle méthode sur la classe Flight.

```php
use Overclokk\Cookie\Cookie;

/*
 * Définissez dans votre fichier bootstrap ou public/index.php
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// Définir un cookie

		// vous voudrez que ce soit faux pour obtenir une nouvelle instance
		// utilisez le commentaire ci-dessous si vous souhaitez l'autocomplétion
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // nom du cookie
			'1', // la valeur que vous souhaitez définir
			86400, // nombre de secondes pendant lesquelles le cookie doit durer
			'/', // chemin où le cookie sera disponible
			'example.com', // domaine où le cookie sera disponible
			true, // le cookie ne sera transmis que sur une connexion sécurisée HTTPS
			true // le cookie ne sera disponible que via le protocole HTTP
		);

		// éventuellement, si vous voulez conserver les valeurs par défaut
		// et avoir un moyen rapide de définir un cookie pour une longue durée
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// Vérifiez si vous avez le cookie
		if (Flight::cookie()->has('stay_logged_in')) {
			// placez-les dans la zone du tableau de bord par exemple.
			Flight::redirect('/dashboard');
		}
	}
}