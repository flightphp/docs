# Instalación

## **1\. Descargar los archivos.**

Si estás utilizando [Composer](https://getcomposer.org), puedes ejecutar el siguiente comando:

```bash
composer require flightphp/core
```

O puedes [descargar](https://github.com/flightphp/core/archive/master.zip) los archivos directamente y extraerlos en tu directorio web.

## **2\. Configurar tu servidor web.**

Para *Apache*, edita tu archivo `.htaccess` con lo siguiente:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Nota**: Si necesitas usar Flight en un subdirectorio, agrega la línea
> `RewriteBase /subdir/` justo después de `RewriteEngine On`.
> **Nota**: Si deseas proteger todos los archivos del servidor, como un archivo de base de datos o environment.
> Coloca esto en tu archivo `.htaccess`:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

Para *Nginx*, agrega lo siguiente a la declaración de tu servidor:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```
## **3\. Crea tu archivo `index.php`.**

```php
<?php

// Si estás utilizando Composer, requiere el cargador automático.
require 'vendor/autoload.php';
// si no estás utilizando Composer, carga el framework directamente
// require 'flight/Flight.php';

// Luego define una ruta y asigna una función para manejar la solicitud.
Flight::route('/', function () {
  echo '¡hola mundo!';
});

// Finalmente, inicia el framework.
Flight::start();
```