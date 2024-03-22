# Cookies

[overclokk/cookie](https://github.com/overclokk/cookie) это простая библиотека для управления куки в вашем приложении.

## Установка

Установка проста с помощью composer.

```bash
composer require overclokk/cookie
```

## Использование

Использование так же просто, как регистрация нового метода в классе Flight.

```php
use Overclokk\Cookie\Cookie;

/*
 * Установите в вашем файле bootstrap или public/index.php
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// Установить куки

		// вам нужно, чтобы это было false, чтобы получить новый экземпляр
		// используйте комментарий ниже, если хотите автозаполнение
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // имя куки
			'1', // значение, которое вы хотите установить
			86400, // количество секунд, на которое должно длиться куки
			'/', // путь, по которому куки будут доступны
			'example.com', // домен, на котором будут доступны куки
			true, // куки будут передаваться только через безопасное соединение HTTPS
			true // куки будут доступны только через протокол HTTP
		);

		// необязательно, если вы хотите сохранить значения по умолчанию
		// и иметь быстрый способ установить куки на длительное время
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// Проверить, есть ли у вас куки
		if (Flight::cookie()->has('stay_logged_in')) {
			// поместите их в область панели управления, например.
			Flight::redirect('/dashboard');
		}
	}
}