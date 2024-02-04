# Instalação

## **1\. Baixe os arquivos.**

Se estiver usando [Composer](https://getcomposer.org), você pode executar o seguinte
comando:

```bash
composer require flightphp/core
```

OU você pode [baixar](https://github.com/flightphp/core/archive/master.zip)
diretamente e extrai-los para o diretório da web.

## **2\. Configure seu servidor web.**

Para *Apache*, edite seu arquivo `.htaccess` com o seguinte:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Nota**: Se precisar usar o Flight em um subdiretório, adicione a linha
> `RewriteBase /subdir/` logo após `RewriteEngine On`.
> **Nota**: Se quiser proteger todos os arquivos do servidor, como um arquivo db ou env.
> Coloque isso em seu arquivo `.htaccess`:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

Para *Nginx*, adicione o seguinte à declaração do seu servidor:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```
## **3\. Crie seu arquivo `index.php`.**

```php
<?php

// Se estiver usando o Composer, requer o autoloader.
require 'vendor/autoload.php';
// se não estiver usando o Composer, carregue o framework diretamente
// require 'flight/Flight.php';

// Em seguida, defina uma rota e atribua uma função para lidar com a solicitação.
Flight::route('/', function () {
  echo 'olá mundo!';
});

// Por fim, inicie o framework.
Flight::start();
```