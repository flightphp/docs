# Webserver configuration

For *Apache*, edit your `.htaccess` file with the following:

```config
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Note**: If you need to use flight in a subdirectory add the line
`RewriteBase /subdir/` just after `RewriteEngine On`.
**Note**: If you want to protect all server files, like a db or env file.
Put this in your `.htaccess` file:

```config
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

For *Nginx*, add the following to your server declaration:

```config
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```