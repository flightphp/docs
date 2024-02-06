# Kļūdu apstrāde

## Kļūdas un izņēmumi

Visas kļūdas un izņēmumi tiek noķerti ar Flight un padoti `error` metodei.
Pēc noklusējuma uzvedība ir nosūtīt vispārēju `HTTP 500 Iekšēja servera kļūda` atbildi ar kādu kļūdas informāciju.

Jūs varat pārrakstīt šo uzvedību savām vajadzībām:

```php
Flight::map('error', function (Throwable $error) {
  // Apstrādāt kļūdu
  echo $error->getTraceAsString();
});
```

Pēc noklusējuma kļūdas netiek reģistrētas tīmekļa serverī. Jūs varat to iespējot, mainot konfigurāciju:

```php
Flight::set('flight.log_errors', true);
```

## Nav Atrasts

Kad URL nav atrodams, Flight izsauc `notFound` metodi. Noklusējuma uzvedība ir nosūtīt `HTTP 404 Nav atrasts` atbildi ar vienkāršu ziņojumu.

Jūs varat pārrakstīt šo uzvedību savām vajadzībām:

```php
Flight::map('notFound', function () {
  // Apstrādāt nav atrasts
});
```