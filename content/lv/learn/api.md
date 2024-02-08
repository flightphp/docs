# Programmas API metodes

Flight tika izstrādāts, lai būtu viegli lietojams un saprotams. Šeit ir pilns metožu komplekts
programmai. Tas sastāv no pamatmetodēm, kas ir regulāras
statiskās metodes, un paplašināmām metodēm, kas ir atainotas metodes, kuras var filtrēt
vai pārrakstīt.

## Pamatmetodes

Šīs metodes ir pamata pametodes un tās nevar tikt pārrakstītas.

```php
Flight::map(virkne $nosaukums, izsaukamais $atkārtojums, booleiskais $padot_maršrutu = viltus) // Izveido pielāgotu pamata metodi.
Flight::registret(virkne $nosaukums, virkne $klase, masīvs $parametri = [], ?izsaukamais $atkārtojums = null) // Reģistrē klasi pamata metodē.
Flight::noreģistrēt(virkne $nosaukums) // Atcelt klasi pamata metodē.
Flight::pirms(virkne $nosaukums, izsaukamais $atkārtojums) // Pievieno filtru pirms pamata metodes.
Flight::pēc(virkne $nosaukums, izsaukamais $atkārtojums) // Pievieno filtru pēc pamata metodes.
Flight::ceļš(virkne $ceļš) // Pievieno ceļu klases automātiskajai ielādei.
Flight::ielabot(virkne $atslēga) // Iegūt mainīgo.
Flight::iestatīt(virkne $atslēga, jaukts $vērtība) // Iestatīt mainīgo.
Flight::ir(virkne $atslēga) // Pārbauda, vai mainīgais ir iestatīts.
Flight::notīrīt(masīvs|virkne $atslēga = []) // Notīrīt mainīgo.
Flight::init() // Inicializē pamata iestatījumus.
Flight::lietotne() // Atgūt lietojumprogrammas objekta instanci
Flight::pieprasījums() // Iegūt pieprasījuma objekta instanci
Flight::atbilde() // Iegūt atbildes objekta instanci
Flight::maisītājs() // Iegūt maršrutētāja objekta instanci
Flight::skats() // Iegūt skata objekta instanci
```

## Paplašināmās metodēs

```php
Flight::sākt() // Sākt pamatprogrammu.
Flight::apturēt() // Apturēt pamatprogrammu un nosūtīt atbildi.
Flight::apturēt(int $kods = 200, virkne $ziņojums = '') // Apturēt pamatprogrammu, norādot nepieciešamības gadījumā statusa kodu un ziņojumu.
Flight::maršruts(virkne $modelis, izsaukamais $atkārtojums, booleiskais $padot_maršrutu = viltus, virkne $aliase = '') // Attēlo URL modeli atbilstoši atsauces atgriezumam.
Flight::pašniedz(virkne $modelis, izsaukamais $atkārtojums, booleiskais $padot_maršrutu = viltus, virkne $aliase = '') // Attēlo PASTA pieprasījuma URL modeli atbilstoši atsauces atgriezumam.
Flight::novietot(virkne $modelis, izsaukamais $atkārtojums, booleiskais $padot_maršrutu = viltus, virkne $aliase = '') // Attēlo PUT pieprasījuma URL modeli atbilstoši atsauces atgriezumam.
Flight::daļa(virkne $modelis, izsaukamais $atkārtojums) // Izveido grupēšanu vietnēm, modelim jābūt virknei.
Flight::iegūtUrl(virkne $nosaukums, masīvs $parametri = []) // Ģenerē URL, pamatojoties uz maršruta aizstājvārdu.
Flight::novirzīt(virkne $url, int $kods) // Novirza uz citu vietni.
Flight::rendēt(virkne $fails, masīvs $dati, ?virkne $atslēga = null) // Atveido veidnes failu.
Flight::kļūda(Throwable $kļūda) // Nosūta HTTP 500 atbildi.
Flight::navAtrasts() // Nosūta HTTP 404 atbildi.
Flight::etag(virkne $id, virkne $veids = 'virkne') // Veic ETag HTTP kešatmiņu.
Flight::pēdējaisModificēts(int $laiks) // Veic pēdējo modificēto HTTP kešatmiņu.
Flight::json(jaukts $dati, int $kods = 200, booleiskais $kodēt = viltus, virkne $kodējums = 'utf8', int $opcija) // Nosūta JSON atbildi.
Flight::jsonp(jaukts $dati, virkne $param = 'jsonp', int $kods = 200, booleiskais $kodēt = viltus, virkne $kodējums = 'utf8', int $opcija) // Nosūta JSONP atbildi.
```

Visi pielāgotie metodes, kas pievienoti, izmantojot `map` un `registret`, arī var tikt filtrēti.