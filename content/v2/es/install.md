# Instalación

### 1. Descarga los archivos.

Si estás utilizando [Composer](https://getcomposer.org), puedes ejecutar el siguiente
comando:

```bash
composer require flightphp/core
```

O puedes [descargarlos](https://github.com/flightphp/core/archive/master.zip)
directamente y extraerlos a tu directorio web.

### 2. Configura tu servidor web.

Para *Apache*, edita tu archivo `.htaccess` con lo siguiente:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Nota**: Si necesitas usar flight en un subdirectorio, agrega la línea
> `RewriteBase /subdir/` justo después de `RewriteEngine On`.
> **Nota**: Si quieres proteger todos los archivos del servidor, como un archivo de base de datos o de entorno.
> Pon esto en tu archivo `.htaccess`:

```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

Para *Nginx*, agrega lo siguiente a tu declaración de servidor:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

### 3. Crea tu archivo `index.php`.

Primero incluye el framework.

```php
require 'flight/Flight.php';
```

Si estás utilizando Composer, ejecuta el cargador automático en su lugar.

```php
require 'vendor/autoload.php';
```

Luego define una ruta y asigna una función para manejar la solicitud.

```php
Flight::route('/', function () {
  echo '¡hola mundo!';
});
```

Finalmente, inicia el framework.

```php
Flight::start();
```
