# Встановлення

### 1. Завантажте файли.

Якщо ви використовуєте [Composer](https://getcomposer.org), ви можете виконати наступну
команду:

```bash
composer require flightphp/core
```

АБО ви можете [завантажити](https://github.com/flightphp/core/archive/master.zip)
їх безпосередньо та розпакувати їх у вашій веб-директорії.

### 2. Налаштуйте ваш веб-сервер.

Для *Apache*, відредагуйте ваш файл `.htaccess` з наступним:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Примітка**: Якщо вам потрібно використовувати Flight у підкаталозі, додайте рядок 
> `RewriteBase /subdir/` одразу після `RewriteEngine On`.
> **Примітка**: Якщо ви хочете захистити всі файли сервера, такі як файл db або env.
> Додайте це у ваш файл `.htaccess`:

```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

Для *Nginx*, додайте наступне до вашої декларації сервера:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

### 3. Створіть ваш файл `index.php`.

Спочатку підключіть фреймворк.

```php
require 'flight/Flight.php';
```

Якщо ви використовуєте Composer, виконайте автозавантажувач замість цього.

```php
require 'vendor/autoload.php';
```

Потім визначте маршрут та призначте функцію для обробки запиту.

```php
Flight::route('/', function () {
  echo 'hello world!';
});
```

Нарешті, запустіть фреймворк.

```php
Flight::start();
```
