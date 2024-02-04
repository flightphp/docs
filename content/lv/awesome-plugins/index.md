# Lielisks spraudņi

Flight ir ļoti paplašināms. Ir vairāki spraudņi, kas var tikt izmantoti, lai pievienotu funkcionalitāti jūsu Flight lietojumprogrammai. Daži no tiem oficiāli tiek atbalstīti arī ar FlightPHP komandas palīdzību, bet citi ir mikro/mīkstas bibliotēkas, lai jums palīdzētu sākt darbu.

## Kešatmiņa

Kešatmiņa ir lielisks veids, kā paātrināt jūsu lietojumprogrammu. Ir daudz kešatmiņas bibliotēku, kas var tikt izmantotas ar Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Gaismas, vienkārša un neatkarīga PHP faila kešatmiņas klase

## Kļūdu labošana

Kļūdu labošana ir būtiska, kad jūs strādājat savā lokālajā vidē. Ir daži spraudņi, kas var uzlabot jūsu labošanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Šī ir pilnīgi iezīmēta kļūdu apstrādātāja, kas var tikt izmantota ar Flight. Tai ir vairākas panelis, kas var palīdzēt jums labot jūsu lietojumprogrammu. To ir arī ļoti viegli paplašināt un pievienot savas paneļu.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Izmantots kopā ar [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, šis spraudnis pievieno dažus papildu paneļus, lai palīdzētu labot kļūdas, kas būtiski saistītas ar Flight projektiem.

## Datubāzes

Datubāzes ir pamats lielākajai daļai lietojumprogrammu. Tā ir veids, kā saglabāt un atgūt datus. Dažas datubāzu bibliotēkas ir vienkārši apvalki, lai rakstītu vaicājumus, bet citas ir pilnveidotas ORM sistēmas.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālais Flight PDO apvalks, kas ir daļa no pamata. Tas ir vienkāršs apvalks, kas palīdz vienkāršot vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Oficiālais Flight ActiveRecord ORM/Attēlveidotājs. Lieliska maza bibliotēka, lai viegli atgūtu un saglabātu datus jūsu datubāzē.

## Sesija

Sesijas tiešām nav noderīgas API, bet, veidojot tīmekļa lietojumprogrammu, sesijas var būt būtiskas, lai uzturētu stāvokli un pieteikšanās informāciju.

- [Ghostff/Session](/awesome-plugins/session) - PHP Sesiju pārvaldnieks (nebloķējošs, flash, segments, sesiju šifrēšana). Izmantots PHP open_ssl sesiju datu pēc iespējas šifrēšanai/atšifrēšanai.

## Veidnes

Veidnes ir būtiskas jebkurai tīmekļa lietojumprogrammai ar lietotāja saskarni. Ir daudz veidņu dzinēju, kas var tikt izmantoti ar Flight.

- [flightphp/core View](/learn#view) - Tas ir ļoti pamata veidnes dzinējs, kas ir daļa no pamata. Nav ieteicams izmantot, ja jums ir vairākas lapas jūsu projektā.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilnveidots veidņu dzinējs, kas ir ļoti viegli lietojams un šķiet tuvāks PHP sintaksei nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savas filtrus un funkcijas.

## Contributing

Vai jums ir spraudnis, ko vēlaties kopīgot? Iesniedziet izvilkuma pieprasījumu, lai to pievienotu sarakstam!