# Lieliskie Spraudņi

Flight ir ļoti paplašināms. Ir daudzi spraudņi, kas var tikt izmantoti, lai pievienotu funkcionalitāti jūsu Flight aplikācijai. Daži no tiem ir oficiāli atbalstīti ar FlightPHP komandas, bet citi ir mikro/lite bibliotēkas, lai palīdzētu jums sākt.

## Kešošana

Kešošana ir lielisks veids, kā paātrināt jūsu aplikāciju. Ir daudzas kešošanas bibliotēkas, kas var tikt izmantotas ar Flight.

- [Wruczek/PHP-File-Cache](/lieliskie-spraudņi/php-failu-kešošana) - Viega, vienkārša un neatkarīga PHP failu kešošanas klase

## Debugošana

Debugošana ir kritiska, kad jūs izstrādājat savā lokālajā vidē. Ir daži spraudņi, kas var uzlabot jūsu debugošanas pieredzi.

- [tracy/tracy](/lieliskie-spraudņi/tracy) - Šis ir pilnīgi iekļauts kļūdu apstrādātājs, ko var izmantot ar Flight. Tam ir daudz panelu, kas var palīdzēt jums atkļūdot jūsu aplikāciju. To ir arī ļoti viegli paplašināt un pievienot savus paneļus.
- [flightphp/tracy-extensions](/lieliskie-spraudņi/tracy-paplašinājumi) - Lietots ar [Tracy](/lieliskie-spraudņi/tracy) kļūdu apstrādātāju, šis spraudnis pievieno vairākas papildus darbvirsmas, kas palīdz ar atkļūdošanu speciāli Flight projektos.

## Datubāzes

Datubāzes ir lielākās daudzu aplikāciju pamats. Tā ir veids, kā jūs saglabājat un iegūstat datus. Dažas datubāzu bibliotēkas vienkārši ir apvalki, lai rakstītu vaicājumus, un dažas ir pilnvērtīgi ORM.

- [flightphp/core PdoWrapper](/lieliskie-spraudņi/pdo-apvalks) - Oficiālais Flight PDO apvalks, kas ir daļa no pamata. Tas ir vienkāršs apvalks, lai palīdzētu vienkāršot vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- [flightphp/active-record](/lieliskie-spraudņi/aktīvais-ieraksts) - Oficiālais Flight ActiveRecord ORM/Mapper. Lieliska neliela bibliotēka, lai viegli iegūtu un saglabātu datus savā datubāzē.

## Sesija

Sesijas nav tiešām noderīgas API'iem, bet, veidojot tīmekļa aplikāciju, sesijas var būt kritiskas stāvokļa un pieteikšanās informācijas uzturēšanai.

- [Ghostff/Session](/lieliskie-spraudņi/sesija) - PHP sesiju vadītājs (nebloķējošs, flash, segments, sesiju šifrēšana). Lieto PHP open_ssl sesiju datu izvēles šifrēšanai/atkodēšanai.

## Templēšana

Tempļēšana ir būtiska jebkurai tīmekļa aplikācijai ar lietotāja saskarni. Ir daudz tempļēšanas dzinēju, kas var tikt izmantoti ar Flight.

- [flightphp/core View](/mācīties#skati) - Tas ir ļoti pamata tempļēšanas dzinējs, kas ir daļa no pamata. Nav ieteicams lietot, ja jums ir vairāk nekā dažas lappuses jūsu projektā.
- [latte/latte](/lieliskie-spraudņi/latte) - Latte ir pilnīga iekļauta tempļēšanas dzinējs, kas ir ļoti viegli lietojams un jūtamas tuvāk PHP sintaksei nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Ieguldīšana

Ir spraudnis, ko vēlaties koplietot? Iesniedziet pieprasījumu, lai to pievienotu sarakstam!