# Instalação

## Baixe os arquivos.

Se estiver a utilizar [Composer](https://getcomposer.org), pode executar o seguinte
comando:

```bash
composer require flightphp/core
```

OU pode [baixar os arquivos](https://github.com/flightphp/core/archive/master.zip)
 diretamente e extrai-los para o seu diretório web.

## Configure o seu Servidor Web.

### Apache
Para o Apache, edite o arquivo `.htaccess` com o seguinte:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Nota**: Se precisar de utilizar o flight num subdiretório, adicione a linha
> `RewriteBase /subdir/` imediatamente após `RewriteEngine On`.

> **Nota**: Se quiser proteger todos os ficheiros do servidor, como um ficheiro db ou env.
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

// Se estiver a utilizar o Composer, requer o autoloader.
require 'vendor/autoload.php';
// se não estiver a utilizar o Composer, carregue o framework diretamente
// require 'flight/Flight.php';

// Em seguida, defina uma rota e atribua uma função para lidar com o pedido.
Flight::route('/', function () {
  echo 'olá mundo!';
});

// Por último, inicie o framework.
Flight::start();
```