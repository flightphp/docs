# Lieliskie spraudņi

Flight ir neticami paplašināms. Ir vairāki spraudņi, ko var izmantot, lai pievienotu funkcionalitāti jūsu Flight lietotnei. Dažus oficiāli atbalsta Flight komanda, bet citi ir mikro/vieglās bibliotēkas, kas palīdzēs jums sākt.

## AI rīki

Flight var padarīt vēl foršāku ar AI darbināmiem spraudņiem.

- [Flight MCP](/awesome-plugins/mcp) - Spraudnis MCP (Model Control Protocol) integrēšanai ar Flight, nodrošinot nevainojamu ar AI darbināmu funkcionalitāti. Galvenokārt koncentrējas uz dokumentācijas lapām, tas palīdz samazināt tokenu izmaksas, sniedzot visjaunāko informāciju par jūsu Flight projektiem.
- [stribus/mcp-flightphp-server-skeleton](https://github.com/stribus/mcp-flightphp-server-skeleton) - FlightPHP MCP servera skelets ar HTTP un stdio, kā arī automātisku rīku, uzvedņu un resursu atklāšanu.

## API dokumentācija

API dokumentācija ir ļoti svarīga jebkuram API. Tā palīdz izstrādātājiem saprast, kā mijiedarboties ar jūsu API un ko sagaidīt pretī. Ir pieejami pāris rīki, kas var palīdzēt ģenerēt API dokumentāciju jūsu Flight projektiem.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniela Šreibera raksts emuārā par to, kā izmantot OpenAPI specifikāciju ar FlightPHP, lai izveidotu savu API, izmantojot API first pieeju.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ir lielisks rīks, kas palīdz ģenerēt API dokumentāciju jūsu Flight projektiem. Tas ir ļoti vienkārši lietojams un pielāgojams jūsu vajadzībām. Šī ir PHP bibliotēka, kas palīdz ģenerēt Swagger dokumentāciju.

## Lietotņu veiktspējas monitorings (APM)

Lietotņu veiktspējas monitorings (APM) ir ļoti svarīgs jebkurai lietotnei. Tas palīdz saprast, kā darbojas jūsu lietotne un kur atrodas sašaurinājumi. Ir vairāki APM rīki, ko var izmantot ar Flight.
- <span class="badge bg-primary">oficiāls</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM ir vienkārša APM bibliotēka, ko var izmantot jūsu Flight lietotņu monitoringam. To var izmantot, lai uzraudzītu jūsu lietotnes veiktspēju un palīdzētu identificēt sašaurinājumus.

## Asinhronā apstrāde

Flight jau ir ātrs ietvars, bet turbo dzinēja pievienošana padara visu jautrāku (un izaicinošāku)!

- [flightphp/async](/awesome-plugins/async) - Oficiālā Flight Async bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot asinhrono apstrādi jūsu lietotnei. Tā zem pārsega izmanto Swoole/Openswoole, lai nodrošinātu vienkāršu un efektīvu veidu, kā asinhroni palaist uzdevumus.

## Autorizācija/Atļaujas

Autorizācija un atļaujas ir ļoti svarīgas jebkurai lietotnei, kurā nepieciešami kontroles mehānismi tam, kurš kam var piekļūt.

- <span class="badge bg-primary">oficiāls</span> [flightphp/permissions](/awesome-plugins/permissions) - Oficiālā Flight Permissions bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot lietotāja un lietotnes līmeņa atļaujas jūsu lietotnei. 

## Autentifikācija

Autentifikācija ir būtiska lietotnēm, kurām jāpārbauda lietotāja identitāte un jāaizsargā API galapunkti.

- [firebase/php-jwt](/awesome-plugins/jwt) - JSON Web Token (JWT) bibliotēka PHP. Vienkāršs un drošs veids, kā ieviest uz tokeniem balstītu autentifikāciju jūsu Flight lietotnēs. Lieliski piemērots bezstāvokļa API autentifikācijai, maršrutu aizsardzībai ar starpprogrammatūru un OAuth stila autorizācijas plūsmu ieviešanai.

## Kešošana

Kešošana ir lielisks veids, kā paātrināt jūsu lietotni. Ir vairākas kešošanas bibliotēkas, ko var izmantot ar Flight.

- <span class="badge bg-primary">oficiāls</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Viegls, vienkāršs un patstāvīgs PHP kešatmiņas klases risinājums failā

## CLI

CLI lietotnes ir lielisks veids, kā mijiedarboties ar savu lietotni. Varat tās izmantot, lai ģenerētu kontrolierus, parādītu visus maršrutus un citādi.

- <span class="badge bg-primary">oficiāls</span> [flightphp/runway](/awesome-plugins/runway) - Runway ir CLI lietotne, kas palīdz pārvaldīt jūsu Flight lietotnes.

## Sīkdatnes

Sīkdatnes ir lielisks veids, kā klienta pusē glabāt nelielus datu apjomus. Tās var izmantot, lai glabātu lietotāja preferences, lietotnes iestatījumus un citādi.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ir PHP bibliotēka, kas nodrošina vienkāršu un efektīvu veidu, kā pārvaldīt sīkdatnes.

## Atkļūdošana

Atkļūdošana ir ļoti svarīga, izstrādājot lokālajā vidē. Ir daži spraudņi, kas var uzlabot jūsu atkļūdošanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Šis ir pilnvērtīgs kļūdu apstrādātājs, ko var izmantot ar Flight. Tam ir vairāki paneļi, kas var palīdzēt atkļūdot jūsu lietotni. To arī ir ļoti viegli paplašināt un pievienot savus paneļus.
- <span class="badge bg-primary">oficiāls</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Izmanto kopā ar [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju; šis spraudnis pievieno dažus papildu paneļus, kas palīdz atkļūdot tieši Flight projektus.

## Datubāzes

Datubāzes ir vairuma lietotņu pamatā. Tajās glabājat un no tām iegūstat datus. Dažas datubāzu bibliotēkas ir vienkārši ietvari vaicājumu rakstīšanai, bet citas ir pilnvērtīgi ORM.

- <span class="badge bg-primary">oficiāls</span> [flightphp/core SimplePdo](/learn/simple-pdo) - Oficiālais Flight PDO palīgs, kas ir daļa no kodola. Šis ir moderns apvalks ar ērtām palīgmetodēm, piemēram, `insert()`, `update()`, `delete()` un `transaction()`, lai vienkāršotu datubāzes darbības. Visi rezultāti tiek atgriezti kā Collections, nodrošinot elastīgu piekļuvi masīvam/objektam. Tas nav ORM, tikai labāks veids, kā strādāt ar PDO.
- <span class="badge bg-warning">novecojis</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - Oficiālais Flight PDO apvalks, kas ir daļa no kodola (novecojis kopš v3.18.0). Tā vietā izmantojiet SimplePdo.
- <span class="badge bg-primary">oficiāls</span> [flightphp/active-record](/awesome-plugins/active-record) - Oficiālais Flight ActiveRecord ORM/Mapper. Lieliska maza bibliotēka, kas atvieglo datu iegūšanu un glabāšanu jūsu datubāzē.
- [byjg/php-migration](/awesome-plugins/migrations) - Spraudnis, kas seko līdzi visām datubāzes izmaiņām jūsu projektā.
- [knifelemon/easy-query](/awesome-plugins/easy-query) - Viegls, plūstošs SQL vaicājumu veidotājs, kas ģenerē SQL un parametrus sagatavotajiem vaicājumiem. Lieliski darbojas ar [SimplePdo](/learn/simple-pdo).

## Šifrēšana

Šifrēšana ir ļoti svarīga jebkurai lietotnei, kas glabā sensitīvus datus. Datu šifrēšana un atšifrēšana nav pārāk sarežģīta, taču pareiza šifrēšanas atslēgas glabāšana [var](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [būt](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [grūti](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Vissvarīgākais ir nekad neglabāt šifrēšanas atslēgu publiskā direktorijā vai iekļaut to koda repozitorijā.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Šī ir bibliotēka, ko var izmantot datu šifrēšanai un atšifrēšanai. Tās iestatīšana un palaišana ir diezgan vienkārša, lai sāktu šifrēt un atšifrēt datus.

## E-pasts

E-pasta sūtīšana ir būtiska vajadzība lielākajai daļai tīmekļa lietotņu — apsveikuma ziņojumi, paroles atiestatīšana, paziņojumi. Šīs bibliotēkas padara to nesāpīgu, vienlaikus saglabājot stabilu piegādājamību.

- [ryanstubbs/flightmail](/awesome-plugins/flightmail) - FlightMail aptin Symfony Mailer ar plūstošu, Flight draudzīgu API. Sūtiet, izmantojot SMTP vai jebkuru lielāko pakalpojumu sniedzēju, ar vienkāršām DSN virknēm, maršrutējiet dažādus pakalpojumu sniedzējus atsevišķiem ziņojumiem un renderējiet saturu ar Twig vai Latte veidnēm. Šis ir neoficiāls Flight spraudnis, un to neuztur Flight komanda.

## Darbu rinda

Darbu rindas ir patiešām noderīgas, lai asinhroni apstrādātu uzdevumus. Tas var būt e-pastu sūtīšana, attēlu apstrāde vai jebkas, kas nav jāveic reāllaikā.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue ir bibliotēka, ko var izmantot, lai asinhroni apstrādātu darbus. To var izmantot ar beanstalkd, MySQL/MariaDB, SQLite un PostgreSQL.

## Sesija

Sesijas nav īpaši noderīgas API, taču, veidojot tīmekļa lietotni, sesijas var būt ļoti svarīgas stāvokļa un pieteikšanās informācijas uzturēšanai.

- <span class="badge bg-primary">oficiāls</span> [flightphp/session](/awesome-plugins/session) - Oficiālā Flight Session bibliotēka. Šī ir vienkārša sesiju bibliotēka, ko var izmantot sesiju datu glabāšanai un iegūšanai. Tā izmanto PHP iebūvēto sesiju apstrādi.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (nebloķējošs, flash, segmentu, sesiju šifrēšana). Izmanto PHP open_ssl, lai pēc izvēles šifrētu/atšifrētu sesijas datus.

## Veidņošana

Veidņošana ir būtiska jebkurai tīmekļa lietotnei ar lietotāja saskarni. Ir vairākas veidņu sistēmas, ko var izmantot ar Flight.

- <span class="badge bg-warning">novecojis</span> [flightphp/core View](/learn#views) - Šī ir ļoti vienkārša veidņu sistēma, kas ir daļa no kodola. Nav ieteicams to izmantot, ja jūsu projektā ir vairāk nekā pāris lapas.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilnvērtīga veidņu sistēma, kas ir ļoti viegli lietojama un šķiet tuvāka PHP sintaksei nekā Twig vai Smarty. To arī ir ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.
- [twig/twig](/awesome-plugins/twig) - Twig ir elastīga, ātra un droša veidņu sistēma (tā pati, ko izmanto Symfony). AI rīki un daudzi PHP izstrādātāji to labi pārzina, tā pēc noklusējuma automātiski aizsargā izvadi, un tai ir milzīga paplašinājumu ekosistēma.
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate ir jaudīga PHP veidņu sistēma ar resursu kompilēšanu, veidņu pārmantošanu un mainīgo apstrādi. Tā piedāvā automātisku CSS/JS minimizēšanu, kešošanu, Base64 kodējumu un neobligātu Flight PHP ietvara integrāciju.

## WordPress integrācija

Vēlaties izmantot Flight savā WordPress projektā? Tam ir ērts spraudnis!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Šis WordPress spraudnis ļauj darbināt Flight tieši līdzās WordPress. Tas ir lieliski piemērots, lai pievienotu pielāgotus API, mikropakalpojumus vai pat pilnas lietotnes jūsu WordPress vietnei, izmantojot Flight ietvaru. Ļoti noderīgi, ja vēlaties labāko no abām pasaulēm!

## Pienesums

Vai ir spraudnis, ar ko vēlaties dalīties? Iesniedziet pull request, lai to pievienotu sarakstam!