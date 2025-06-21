# Brīnišķīgi spraudņi

Flight ir neticami paplašināms. Ir vairāki spraudņi, ko var izmantot, lai pievienotu funkcionalitāti jūsu Flight aplikācijai. Daži no tiem oficiāli atbalsta Flight komanda, bet citi ir mikro/lite bibliotēkas, lai palīdzētu jums sākt darbu.

## API dokumentācija

API dokumentācija ir ļoti svarīga jebkurai API. Tā palīdz izstrādātājiem saprast, kā mijiedarboties ar jūsu API un ko sagaidīt pretī. Ir pieejami daži rīki, lai palīdzētu ģenerēt API dokumentāciju jūsu Flight projektiem.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Emu postenis, ko sarakstījis Daniels Šraibers, par to, kā izmantot OpenAPI specifikāciju ar FlightPHP, lai izveidotu jūsu API, izmantojot API vispirms pieeju.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ir lielisks rīks, lai palīdzētu ģenerēt API dokumentāciju jūsu Flight projektiem. Tas ir ļoti viegli lietojams un to var pielāgot jūsu vajadzībām. Tas ir PHP bibliotēka, lai palīdzētu ģenerēt Swagger dokumentāciju.

## Lietojumprogrammas veiktspējas uzraudzība (APM)

Lietojumprogrammas veiktspējas uzraudzība (APM) ir ļoti svarīga jebkurai aplikācijai. Tā palīdz saprast, kā jūsu aplikācija darbojas un kur ir aizkavējumi. Ir vairāki APM rīki, ko var izmantot ar Flight.
- <span class="badge bg-info">bēta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM ir vienkārša APM bibliotēka, ko var izmantot, lai uzraudzītu jūsu Flight aplikācijas. To var izmantot, lai uzraudzītu aplikācijas veiktspēju un palīdzētu identificēt aizkavējumus.

## Autentifikācija/Autorizācija

Autentifikācija un autorizācija ir ļoti svarīgas jebkurai aplikācijai, kurai nepieciešami kontroles mehānismi, lai noteiktu, kurš var piekļūt kam.

- <span class="badge bg-primary">oficiāls</span> [flightphp/permissions](/awesome-plugins/permissions) - Oficiālā Flight Permissions bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot lietotāju un aplikācijas līmeņa atļaujas jūsu aplikācijai. 

## Kešošana

Kešošana ir lielisks veids, kā paātrināt jūsu aplikāciju. Ir vairāki kešošanas bibliotēkas, ko var izmantot ar Flight.

- <span class="badge bg-primary">oficiāls</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Viegls, vienkāršs un neatkarīgs PHP failā kešošanas klase

## CLI

CLI aplikācijas ir lielisks veids, kā mijiedarboties ar jūsu aplikāciju. Jūs varat izmantot tās, lai ģenerētu kontrolierus, parādītu visas maršrutus un daudz ko citu.

- <span class="badge bg-primary">oficiāls</span> [flightphp/runway](/awesome-plugins/runway) - Runway ir CLI aplikācija, kas palīdz pārvaldīt jūsu Flight aplikācijas.

## Sīkdatnes

Sīkdatnes ir lielisks veids, kā glabāt mazus datu gabalus klientu pusē. Tās var izmantot, lai glabātu lietotāja preferences, aplikācijas iestatījumus un daudz ko citu.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ir PHP bibliotēka, kas nodrošina vienkāršu un efektīvu veidu, kā pārvaldīt sīkdatnes.

## Debugošana

Debugošana ir ļoti svarīga, kad jūs attīstāt savā lokālajā vidē. Ir daži spraudņi, kas var uzlabot jūsu debugošanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Tas ir pilnībā aprīkots kļūdu apstrādātājs, ko var izmantot ar Flight. Tam ir vairāki paneļi, kas var palīdzēt debugot jūsu aplikāciju. Tas arī ir ļoti viegli paplašināms un pievienot savus paneļus.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Izmantojams kopā ar [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, šis spraudnis pievieno dažus papildu paneļus, lai palīdzētu ar debugošanu tieši Flight projektos.

## Datubāzes

Datubāzes ir kodols lielākajai daļai aplikāciju. Tā ir veids, kā glabāt un izgūt datus. Dažas datubāzu bibliotēkas ir vienkārši aploksnes, lai rakstītu vaicājumus, bet citas ir pilnībā izstrādātas ORM.

- <span class="badge bg-primary">oficiāls</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālā Flight PDO Wrapper, kas ir daļa no kodola. Tas ir vienkāršs aploksne, lai vienkāršotu vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- <span class="badge bg-primary">oficiāls</span> [flightphp/active-record](/awesome-plugins/active-record) - Oficiālā Flight ActiveRecord ORM/Mapper. Lieliska maza bibliotēka, lai viegli izgūtu un glabātu datus jūsu datubāzē.
- [byjg/php-migration](/awesome-plugins/migrations) - Spraudnis, lai izsekotu visas datubāzes izmaiņas jūsu projektā.

## Šifrēšana

Šifrēšana ir ļoti svarīga jebkurai aplikācijai, kas glabā sensitīvus datus. Šifrēt un dešifrēt datus nav pārāk grūti, bet pareizi glabāt šifrēšanas atslēgu [var](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [būt](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [grūti](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Vissvarīgākais ir nekad neglabāt savu šifrēšanas atslēgu publiskā direktorijā vai neiekļaut to jūsu kodu repozitorijā.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Tas ir bibliotēka, ko var izmantot, lai šifrētu un dešifrētu datus. Sākt darbu ir diezgan vienkārši, lai sāktu šifrēt un dešifrēt datus.

## Darba rinda

Darba rindas ir ļoti noderīgas, lai asinhroni apstrādātu uzdevumus. Tas var būt e-pastu sūtīšana, attēlu apstrāde vai viss, kas nav jāveic reāllaikā.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue ir bibliotēka, ko var izmantot, lai apstrādātu darbus asinhroni. To var izmantot ar beanstalkd, MySQL/MariaDB, SQLite un PostgreSQL.

## Sesija

Sesijas nav īsti noderīgas API, bet, veidojot tīmekļa aplikāciju, sesijas var būt ļoti svarīgas, lai uzturētu stāvokli un pieteikšanās informāciju.

- <span class="badge bg-primary">oficiāls</span> [flightphp/session](/awesome-plugins/session) - Oficiālā Flight Session bibliotēka. Tas ir vienkāršs sesijas bibliotēka, ko var izmantot, lai glabātu un izgūtu sesijas datus. Tas izmanto PHP iebūvētās sesijas apstrādi.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (neblokējošs, zibspuldze, segments, sesijas šifrēšana). Izmanto PHP open_ssl, lai pēc izvēles šifrētu/dešifrētu sesijas datus.

## Šablonu veidošana

Šablonu veidošana ir kodols jebkurai tīmekļa aplikācijai ar lietotāja interfeisu. Ir vairāki šablonu dzinēji, ko var izmantot ar Flight.

- <span class="badge bg-warning">novecojis</span> [flightphp/core View](/learn#views) - Tas ir ļoti pamata šablonu dzinējs, kas ir daļa no kodola. To nav ieteicams izmantot, ja jūsu projektā ir vairāk nekā pāris lapas.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilnībā aprīkots šablonu dzinējs, kas ir ļoti viegli lietojams un jūtas tuvāk PHP sintaksei nekā Twig vai Smarty. Tas arī ir ļoti viegli paplašināms un pievienot savus filtrus un funkcijas.

## Dalība

Vai jums ir spraudnis, ko vēlaties dalīties? Iesniedziet pull request, lai pievienotu to sarakstam!