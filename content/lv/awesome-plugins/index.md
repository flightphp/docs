# Lieliskie spraudņi

Flight ir neaprakstāmi paplašināms. Ir vairāki spraudņi, kas var tikt izmantoti, lai pievienotu funkcionalitāti jūsu Flight lietojumprogrammai. Daži no tiem tiek oficiāli atbalstīti ar FlightPHP komandas palīdzību, bet citi ir mikro/nedaudz bibliotēkas, lai palīdzētu jums sākt.

## Kešatmiņa

Kešatmiņa ir lielisks veids, kā paātrināt jūsu lietojumprogrammu. Ir vairākas kešatmiņas bibliotēkas, ko var izmantot ar Flight.

- [Wruczek/PHP-File-Cache](/lieliskie-spraudņi/php-failu-keša) - Gaismas, vienkārša un neatkarīga PHP failu kešēšanas klase

## Kļūdu labošana

Kļūdu labošana ir svarīga, kad jūs attīstāt savā lokālajā vidē. Ir daži spraudņi, kas var uzlabot jūsu kļūdu labošanas pieredzi.

- [tracy/tracy](/lieliskie-spraudņi/tracy) - Šis ir pilnībā funkcionējošs kļūdu apstrādātājs, kas var tikt izmantots ar Flight. Tam ir vairāki paneli, kas var palīdzēt jums labot jūsu lietojumprogrammu. To ir arī ļoti viegli paplašināt un pievienot savus paneļus.
- [flightphp/tracy-extensions](/lieliskie-spraudņi/tracy-paplašinājumi) - Lietots ar [Tracy](/lieliskie-spraudņi/tracy) kļūdu apstrādātāju, šis spraudnis pievieno vairākus papildu paneļus, lai palīdzētu ar kļūdu labošanu speciāli Flight projektos.

## Datubāzes

Datubāzes ir pamats lielākajai daļai lietojumprogrammu. Šī ir veids, kā saglabāt un atgūt datus. Dažas datubāzu bibliotēkas vienkārši ir ietina, lai rakstītu vaicājumus, bet dažas ir pilnīgi attīstītas ORMs.

- [flightphp/core PdoWrapper](/lieliskie-spraudņi/pdo-ietījums) - Oficiālais Flight PDO ietinējs, kas ir daļa no pamata. Tas ir vienkāršs ietinējs, lai palīdzētu vienkāršot vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- [flightphp/active-record](/lieliskie-spraudņi/aktīvā-ieraksts) - Oficiālais Flight ActiveRecord ORM/Mapētājs. Liela maza bibliotēka, lai viegli atgūtu un saglabātu datus savā datubāzē.

## Sesija

Sesijas nav tiešām noderīgas API, bet, veidojot tīmekļa lietojumprogrammu, sesijas var būt svarīgas, lai saglabātu stāvokli un pieteikšanās informāciju.

- [Ghostff/Session](/lieliskie-spraudņi/sesija) - PHP sesiju pārvaldnieks (nebloķēšana, zibatmiņa, segmts, sesijas šifrēšana). Izmanto PHP open_ssl sesiju datu izvēles šifrēšanai/atkļūšanai.

## Templēšana

Templēšana ir pamats jebkurai tīmekļa lietojumprogrammai ar lietotāja saskarni. Ir vairākas templēšanas dzinēji, ko var izmantot ar Flight.

- [flightphp/core View](/mācīties#skati) - Tas ir ļoti pamata templēšanas dzinējs, kas ir daļa no pamata. Nav ieteicams izmantot to, ja jūs projektā esat vairāk nekā pāris lapas.
- [latte/latte](/lieliskie-spraudņi/latte) - Latte ir pilnībā aprīkots templēšanas dzinējs, kas ir ļoti viegli lietojams un jūtas tuvāk PHP sintaksei nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Piedalīšanās

Vai ir spraudnis, ko vēlētos koplietot? Iesniedziet plaiskanas pieprasījumu, lai to pievienotu sarakstam!