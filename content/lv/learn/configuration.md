# Konfigurācija

Jūs varat pielāgot noteiktus Flight uzvedības aspektus, iestatot konfigurācijas vērtības, izmantojot `set` metodi.

```php
Flight::set('flight.log_errors', true);
```

Zemāk ir saraksts ar visām pieejamajām konfigurācijas iestatījumiem:

- **flight.base_url** - Aizstāj pieprasījuma pamata URL. (default: null)
- **flight.case_sensitive** - Lielo un mazo burtu atšķirīga atbilstība URL'iem. (default: false)
- **flight.handle_errors** - Atļauj Flight apstrādāt visas kļūdas iekšpusē. (default: true)
- **flight.log_errors** - Reģistrē kļūdas tīmekļa servera kļūdu žurnālā. (default: false)
- **flight.views.path** - Mape, kurā atrodas skata veidnes. (default: ./views)
- **flight.views.extension** - Skata veidnes faila paplašinājums. (default: .php)