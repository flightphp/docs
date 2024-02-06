# Aptur

Jūs varat apturēt pamatstruktūru jebkurā brīdī, izsaucot `halt` metodi:

```php
Flight::halt();
```

Jūs arī varat norādīt neobligātu `HTTP` statusa kodu un ziņojumu:

```php
Flight::halt(200, 'Jau drīz atgriezīšos...');
```

Izsaukot `halt`, tiks atcelts jebkāds atbildes saturs līdz tam brīdim. Ja vēlaties apturēt
pamatstruktūru un izvadīt pašreizējo atbildi, izmantojiet `stop` metodi:

```php
Flight::stop();
```