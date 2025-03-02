# Instalação

## Baixe os arquivos.

Se estiver a usar [Composer](https://getcomposer.org), pode executar o seguinte comando:

```bash
composer require flightphp/core
```

OU então pode [baixar os arquivos](https://github.com/flightphp/core/archive/master.zip) diretamente e extrair para o seu diretório web.

## Configure o seu servidor web.

### Apache
Para o Apache, edite o seu ficheiro `.htaccess` com o seguinte:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Nota**: Se necessitar de utilizar o Flight numa subpasta, adicione a linha
> `RewriteBase /subdir/` logo após `RewriteEngine On`.

> **Nota**: Se quiser proteger todos os ficheiros do servidor, como um ficheiro de bd ou env.
> Coloque isto no seu ficheiro `.htaccess`:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Para o Nginx, adicione o seguinte à declaração do seu servidor:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Crie o seu ficheiro `index.php`.

```php
<?php

// Se estiver a usar o Composer, requer o autoloader.
require 'vendor/autoload.php';
// se não estiver a usar o Composer, carregue o framework diretamente
// require 'flight/Flight.php';

// Depois defina uma rota e atribua uma função para lidar com o pedido.
Flight::route('/', function () {
  echo 'hello world!';
});

// Por fim, inicie o framework.
Flight::start();
```