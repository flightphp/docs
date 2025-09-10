# Instalação

### 1. Baixe os arquivos.

Se você estiver usando [Composer](https://getcomposer.org), pode executar o seguinte
comando:

```bash
composer require flightphp/core
```

OU você pode [baixá-los](https://github.com/flightphp/core/archive/master.zip)
diretamente e extrair para o seu diretório web.

### 2. Configure seu servidor web.

Para *Apache*, edite seu arquivo `.htaccess` com o seguinte:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Nota**: Se você precisar usar flight em um subdiretório, adicione a linha
> `RewriteBase /subdir/` logo após `RewriteEngine On`.
> **Nota**: Se você quiser proteger todos os arquivos do servidor, como um arquivo db ou env.
> Coloque isso no seu arquivo `.htaccess`:

```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

Para *Nginx*, adicione o seguinte à sua declaração de servidor:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

### 3. Crie seu arquivo `index.php`.

Primeiro, inclua o framework.

```php
require 'flight/Flight.php';
```

Se você estiver usando Composer, execute o autoloader em vez disso.

```php
require 'vendor/autoload.php';
```

Então, defina uma rota e atribua uma função para lidar com a solicitação.

```php
Flight::route('/', function () {
  echo 'hello world!';
});
```

Finalmente, inicie o framework.

```php
Flight::start();
```
