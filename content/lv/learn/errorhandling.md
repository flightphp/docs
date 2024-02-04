# Kļūdu apstrāde

## Kļūdas un izņēmumi

Visas kļūdas un izņēmumi tiek uztverti un padoti Flight un nodoti `error` metodei.
Noklusētā rīcība ir nosūtīt vispārēju `HTTP 500 Iekšējā servera kļūda`
atbildi ar dažādu kļūdu informāciju.

Jūs varat pārkāpt šo rīcību savām vajadzībām:

```php
Flight::map('error', function (Throwable $error) {
  // Handle error
  echo $error->getTraceAsString();
});
```

Pēc noklusējuma kļūdas netiek reģistrētas tīmekļa serverī. Jūs varat to aktivizēt
mainot konfigurāciju:

```php
Flight::set('flight.log_errors', true);
```

## Nav Atrasts

Ja URL nav atrasts, Flight izsauc `notFound` metodi. Noklusētā
rīcība ir nosūtīt `HTTP 404 Nav atrasts` atbildi ar vienkāršu ziņu.

Jūs varat pārkāpt šo rīcību savām vajadzībām:

```php
Flight::map('notFound', function () {
  // Handle not found
});
```