# Cookies

[overclokk/cookie](https://github.com/overclokk/cookie) — це проста бібліотека для керування куки у вашому додатку.

## Installation

Встановлення є простим за допомогою composer.

```bash
composer require overclokk/cookie
```

## Usage

Використання таке ж просте, як реєстрація нового методу в класі Flight.

```php
use Overclokk\Cookie\Cookie;

/*
 * Встановіть у вашому bootstrap або public/index.php файлі
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// Встановіть куки

		// ви захочете, щоб це було false, щоб отримати новий екземпляр
		// використовуйте наведену нижче коментар, якщо хочете автозаповнення
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // назва куки
			'1', // значення, яке ви хочете встановити
			86400, // кількість секунд, протягом яких куки повинні існувати
			'/', // шлях, за яким куки будуть доступні
			'example.com', // домен, за яким куки будуть доступні
			true, // куки будуть передаватися лише через безпечне HTTPS з'єднання
			true // куки будуть доступні лише через HTTP протокол
		);

		// за бажанням, якщо ви хочете зберегти значення за замовчуванням
		// і мати швидкий спосіб встановити куки на тривалий час
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// Перевірте, чи маєте ви куки
		if (Flight::cookie()->has('stay_logged_in')) {
			// помістіть їх у область інформаційної панелі, наприклад.
			Flight::redirect('/dashboard');
		}
	}
}