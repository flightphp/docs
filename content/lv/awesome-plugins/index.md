# Lieliskie spraudņi

Flight ir ļoti paplašināms. Ir daudz spraudņu, kas var tikt izmantoti, lai pievienotu funkcionalitāti jūsu Flight lietojumprogrammai. Daži no tiem oficiāli tiek atbalstīti no Flight komandas, savukārt citi ir mikro/lite bibliotēkas, lai palīdzētu jums sākt.

## Kešošana

Kešošana ir lieliska veids, kā paātrināt jūsu lietojumprogrammu. Ir daudz kešošanas bibliotēku, kas var tikt izmantotas ar Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Gaiša, vienkārša un neatkarīga PHP faila kešošanas klase

## Dīversija

Dīversija ir būtiska, kad jūs izstrādājat savā lokālajā vide. Ir daži spraudņi, kas var uzlabot jūsu dīversijas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Tā ir pilna funkcionāla kļūdu apstrāde, kas var tikt izmantota ar Flight. Tā disponē ar daudzām paneļu, kas var palīdzēt jums novērst jūsu lietojumprogrammas kļūdas. To ir arī ļoti viegli paplašināt un pievienot savus paneļus.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Lietots ar [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, šis spraudnis pievieno dažus papildu paneļus, lai palīdzētu ar dīversijas konkrēti Flight projektos.

## Datubāzes

Datubāzes ir vairumam lietojumprogrammu pamats. Tā ir veids, kā saglabāt un atgūt datus. Dažas datubāzu bibliotēkas vienkārši ir wrapperi, lai rakstītu vaicājumus, bet dažas ir pilnvērtīgi ORM.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālais Flight PDO apvalks, kas ir daļa no kodola. Šis ir vienkāršs apvalks, lai palīdzētu vienkāršot vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Oficiālais Flight ActiveRecord ORM/Mapper. Lieliska maza bibliotēka, lai viegli atgūtu un saglabātu datus jūsu datubāzē.

## Sesija

Sesijas nav īsti noderīgas API, bet, lai izveidotu tīmekļa lietojumprogrammu, sesijas var būt būtiskas, lai saglabātu stāvokli un pieteikšanās informāciju.

- [Ghostff/Session](/awesome-plugins/session) - PHP sesiju pārvaldītājs (nebloķējošs, flash, segments, sesijas šifrēšana). Izmanto PHP open_ssl sesiju datu izvēlēti šifrēt/šifrēt.

## Templēšana

Templēšana ir būtiska jebkurai tīmekļa lietojumprogrammai ar lietotāja saskarni. Ir daudz templēšanas dzinēju, kas var tikt izmantoti ar Flight.

- [flightphp/core View](/learn#views) - Tas ir ļoti pamata templēšanas dzinējs, kas ir daļa no kodola. Nav ieteicams izmantot to, ja jūsu projektā ir vairāk nekā dažas lapas.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilna funkcionāla templēšanas dzinējs, kas ir ļoti viegli lietojams un jūtas tuvāks PHP sintaksei nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Contributing

Vai jums ir spraudnis, ko vēlaties koplietot? Iesniedziet pieprasījumu pull, lai to pievienotu sarakstam!