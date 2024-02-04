# Apturēšana

Jūs varat apturēt struktūru jebkurā brīdī, izsaucot metodi `halt`:

```php
Flight::halt();
```

Jūs varat arī norādīt neobligātu `HTTP` statusa kodu un ziņojumu:

```php
Flight::halt(200, 'Drīz atgriezīšos...');
```

Izsaukums `halt` noraidīs jebkuru atbildes saturu līdz šim brīdim. Ja vēlaties apturēt
struktūru un izvadīt pašreizējo atbildi, izmantojiet metodi `stop`:

```php
Flight::stop();
```