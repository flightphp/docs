# Konfigurācija

Jūs varat pielāgot noteiktas Flight uzvedības, iestatot konfigurācijas vērtības, izmantojot `set` metodi.

```php
Flight::set('flight.log_errors', true);
```

Zemāk ir saraksts ar visām pieejamajām konfigurācijas iestatījumiem:

- **flight.base_url** - Pārrakstīt pieprasījuma pamata URL. (noklusējums: null)
- **flight.case_sensitive** - Lielo un mazo burtu atbilstība URL. (noklusējums: false)
- **flight.handle_errors** - Atļaut Flight apstrādāt visas kļūdas iekšpusē. (noklusējums: true)
- **flight.log_errors** - Reģistrēt kļūdas tīmekļa servera kļūdu žurnālā. (noklusējums: false)
- **flight.views.path** - Katalogs, kas satur skata sagataves failus. (noklusējums: ./skati)
- **flight.views.extension** - Skata sagataves faila paplašinājums. (noklusējums: .php)