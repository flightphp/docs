# Apsveicīgi spraudņi

Flight ir neticami paplašināms. Ir vairāki spraudņi, kurus var izmantot, lai pievienotu funkcionalitāti jūsu Flight lietojumprogrammai. Daži ir oficiāli atbalstīti no Flight komandas, bet citi ir mikro/lite bibliotēkas, lai palīdzētu jums sākt.

## API dokumentācija

API dokumentācija ir izšķiroša jebkuram API. Tā palīdz izstrādātājiem saprast, kā mijiedarboties ar jūsu API un ko sagaidīt kā atgriezenisko saiti. Ir pieejami vairāki rīki, kas palīdzēs jums ģenerēt API dokumentāciju jūsu Flight projektiem.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Emuāra ieraksts, ko uzrakstījis Daniels Šraibers par to, kā izmantot OpenAPI ģeneratoru kopā ar FlightPHP, lai ģenerētu API dokumentāciju.
- [Swagger UI](https://github.com/zircote/swagger-php) - Swagger UI ir lielisks rīks, kas palīdz jums ģenerēt API dokumentāciju jūsu Flight projektiem. To ir ļoti viegli lietot un var pielāgot jūsu vajadzībām. Šī ir PHP bibliotēka, kas palīdz jums ģenerēt Swagger dokumentāciju.

## Autentifikācija/Autorizācija

Autentifikācija un Autorizācija ir izšķiroša jebkurai lietojumprogrammai, kurai ir nepieciešami kontroles mehānismi, lai noteiktu, kas var piekļūt kādam.

- [flightphp/permissions](/awesome-plugins/permissions) - Oficiālā Flight atļauju bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot lietotāju un lietojumprogrammas līmeņa atļaujas jūsu lietojumprogrammai.

## Kešatmiņa

Kešatmiņa ir lielisks veids, kā paātrināt jūsu lietojumprogrammu. Ir vairāki kešatmiņas bibliotēkas, kuras var izmantot kopā ar Flight.

- [flightphp/cache](/awesome-plugins/php-file-cache) - Gaisīga, vienkārša un patstāvīga PHP iekšējās kešatmiņas klase

## CLI

CLI lietojumprogrammas ir lielisks veids, kā mijiedarboties ar jūsu lietojumprogrammu. Jūs tās varat izmantot, lai ģenerētu kontrolierus, attēlotu visus maršrutus un vēl.

- [flightphp/runway](/awesome-plugins/runway) - Runway ir CLI lietojumprogramma, kas palīdz jums pārvaldīt jūsu Flight lietojumprogrammas.

## Sīkdatnes

Sīkdatnes ir lielisks veids, kā uzglabāt nelielas datu daļas klienta pusē. Tās var izmantot, lai uzglabātu lietotāju preferences, lietojumprogrammas iestatījumus un vēl.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Sīkdatne ir PHP bibliotēka, kas nodrošina vienkāršu un efektīvu veidu, kā pārvaldīt sīkdatnes.

## Kļūdu novēršana

Kļūdu novēršana ir izšķiroša, kad jūs izstrādājat savā vietējā vidē. Ir daži spraudņi, kas var uzlabot jūsu kļūdu novēršanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Šis ir pilnīgs kļūdu apstrādātājs, ko var izmantot kopā ar Flight. Tam ir vairāki paneli, kas var palīdzēt jums novērst kļūdas jūsu lietojumprogrammā. To ir arī ļoti viegli paplašināt un pievienot savus paneļus.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Izmantoto kopā ar [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, šis spraudnis pievieno dažus papildu paneļus, lai palīdzētu ar kļūdu novēršanu, īpaši Flight projektiem.

## Datu bāzes

Datu bāzes ir kodols lielākajai daļai lietojumprogrammu. Tā ir veids, kā uzglabāt un iegūt datus. Dažas datu bāzu bibliotēkas vienkārši ir apvalki, lai rakstītu vaicājumus, bet dažas ir pilnvērtīgi ORM.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālais Flight PDO apvalks, kas ir daļa no kodola. Šis ir vienkāršs apvalks, kas palīdz vienkāršot vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Oficiālā Flight ActiveRecord ORM/Mapper. Lieliska maza bibliotēka, lai viegli iegūtu un uzglabātu datus jūsu datu bāzē.
- [byjg/php-migration](/awesome-plugins/migrations) - Spraudnis, lai sekotu visām datu bāzu izmaiņām jūsu projektā.

## Šifrēšana

Šifrēšana ir izšķiroša jebkurai lietojumprogrammai, kas uzglabā sensitīvus datus. Datu šifrēšana un atšifrēšana nav ļoti grūta, bet pareiza šifrēšanas atslēgas glabāšana [var](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [būt](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [grūti](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Visizšķirošākais ir nekad neglabāt savu šifrēšanas atslēgu publiskajā direktorijā vai to iekļaut savā koda krātuvē.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Šī ir bibliotēka, ko var izmantot datu šifrēšanai un atšifrēšanai. Sākt lietot to ir salīdzinoši vienkārši, lai sāktu šifrēt un atšifrēt datus.

## Sesija

Sesijas patiesībā nav ļoti noderīgas API, bet, veidojot tīmekļa lietojumprogrammu, sesijas var būt izšķirožas, lai saglabātu stāvokli un pieteikšanās informāciju.

- [Ghostff/Session](/awesome-plugins/session) - PHP Sesiju pārvaldnieks (nebloķējošs, flash, segments, sesijas šifrēšana). Izmanto PHP open_ssl, lai opcionalizētu sesiju datu šifrēšanu/atšifrēšanu.

## Veidnes

Veidnes ir kodols jebkurai tīmekļa lietojumprogrammai ar lietotāja saskarni. Ir pieejami vairāki veidņu dzinēji, kurus var izmantot kopā ar Flight.

- [flightphp/core View](/learn#views) - Šī ir ļoti pamata veidņu dzinējs, kas ir daļa no kodola. To neiesaka izmantot, ja jūsu projektā ir vairāk par dažām lapām.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilnībā aprīkots veidņu dzinējs, kuru ļoti viegli lietot un kas jūtas tuvāk PHP sintaksei nekā Twig vai Smarty. To arī ir ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Ieguldījumi

Vai jums ir spraudnis, ko vēlaties dalīt? Iesniedziet pull pieprasījumu, lai pievienotu to sarakstam!