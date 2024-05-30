# Kāpēc ietvaro?

Daži programmētāji kategoriski iebilst pret ietvaru izmantošanu. Viņi argumentē, ka ietvari ir pārbāzti, lēni un grūti uzsākt. Viņi saka, ka ietvari nav nepieciešami un ka jūs varat rakstīt labāku kodu bez tiem. Noteikti ir daži argumenti, kas varētu tikt izvirzīti par ietvaru izmantošanas trūkumiem. Tomēr ir arī daudz priekšrocību, izmantojot ietvarus.

## Iemesli izmantot ietvaru

Šeit ir daži iemesli, kāpēc jūs varētu vēlēties izvērtēt ietvaru izmantošanu:

- **Ātrs izstrādes process**: Ietvari nodrošina daudz funkciju jau no sākuma. Tas nozīmē, ka jūs varat ātrāk veidot tīmekļa lietojumprogrammas. Jums nav jāraksta tik daudz koda, jo ietvars nodrošina daudz funkciju, kas jums ir nepieciešamas.
- **Vienmērība**: Ietvari nodrošina vienmērīgu veidu, kā darīt lietas. Tas padara vieglāk saprast, kā darbojas kods, un atvieglo citiem izstrādātājiem saprast jūsu kodu. Ja jums ir skripts pēc skripta, jūs varētu zaudēt vienmērību starp skriptiem, īpaši ja strādājat ar izstrādātāju komandu.
- **Drošība**: Ietvari nodrošina drošības funkcijas, kas palīdz aizsargāt jūsu tīmekļa lietojumprogrammas no kopīgiem drošības draudiem. Tas nozīmē, ka jums nav jāuztraucas tik daudz par drošību, jo ietvars rūpējas par lielu daļu par jums.
- **Kopiena**: Ietvariem ir lielas izstrādātāju kopienas, kuras dod ieguldījumu ietvarā. Tas nozīmē, ka jūs varat saņemt palīdzību no citiem izstrādātājiem, kad jums ir jautājumi vai problēmas. Tas arī nozīmē, ka ir pieejami daudz resursu, lai palīdzētu jums izprast, kā izmantot ietvaru.
- **Labās prakses**: Ietvari ir izstrādāti, izmantojot labās prakses. Tas nozīmē, ka jūs varat mācīties no ietvara un izmantot tās pašas labās prakses savā kodā. Tas var palīdzēt jums kļūt par labāku programmētāju. Dažreiz jums nav zināms, kas jums nav zināms, un tas beigās var jums uzlikt ķibiru.
- **Paplašināmība**: Ietvari ir izstrādāti, lai tie būtu paplašināmi. Tas nozīmē, ka jūs varat pievienot savu funkcionalitāti ietvaram. Tas ļauj jums veidot tīmekļa lietojumprogrammas, kas ir pielāgotas jūsu īpašajām vajadzībām.

Flight ir mikro-ietvars. Tas nozīmē, ka tas ir neliels un viegls. Tas nenodrošina tik daudz funkciju kā lielāki ietvari, piemēram, Laravel vai Symfony. Tomēr tas nodrošina daudz funkciju, kas jums ir nepieciešamas, lai veidotu tīmekļa lietojumprogrammas. Tas ir arī viegli uzzināt un izmantot. Tas padara to par labu izvēli, lai viegli un ātri veidotu tīmekļa lietojumprogrammas. Ja esat jauns ietvaru lietotājs, Flight ir lielisks ietvars iesācējiem, ar kuru sākt. Tas palīdzēs jums uzzināt par ietvaru izmantošanas priekšrocībām, neapslīpējoties ar pārāk lielu sarežģītību. Kad jums ir nedaudz pieredzes ar Flight, būs vieglāk pāriet uz sarežģītākiem ietvariem, piemēram, Laravel vai Symfony, tomēr Flight joprojām var veiksmīgi izveidot izturīgu lietojumprogrammu.

## Kas ir Maršrutēšana?

Maršrutēšana ir Flight ietvara pamats, bet ko tā īsti nozīmē? Maršrutēšana ir process, kas ņem URL un saskaņo to ar konkrētu funkciju jūsu kodā. Tādējādi jūs varat padarīt savu tīmekļa vietni dažāda veida pamatojoties uz pieprasīto URL. Piemēram, jūs varētu vēlēties parādīt lietotāja profilu, kad tie apmeklē `/lietotājs/1234`, bet rādīt visu lietotāju sarakstu, kad viņi apmeklē `/lietotāji`. Tas viss tiek darīts, izmantojot maršrutēšanu.

Varētu darboties kaut kas tāds:

- Lietotājs apmeklē jūsu pārlūku un ieraksta `http://piemērs.com/lietotājs/1234`.
- Serveris saņem pieprasījumu un aplūko URL, pārsūta to uz jūsu Flight lietojumprogrammas kodu.
- Pieņemsim, ka jūsu Flight kodā ir kaut kas tāds kā `Flight::route('/lietotājs/@id', [ 'UserController', 'skatītLietotājaProfilu' ]);`. Jūsu Flight lietojumprogrammas kods aplūko URL un redz, ka tas atbilst jums definētajai maršrutē, pēc tam palaiž kodu, ko esat definējis šai maršrutē.
- Flight maršrutētājs tad palaiž un izsauc `skatītLietotājaProfilu($id)` metodi `UserController` klasē, padodot `1234` kā `$id` argumentu šajā metodē.
- Kods jūsu `skatītLietotājaProfilu()` metodē tad darbosies un izdarīs to, ko esat tam pavēstījis. Jūs varat beigt ar HTML izvadi lietotāja profila lapai vai, ja tā ir RESTful API, varat izvadīt JSON atbildi ar lietotāja informāciju.
- Flight ietin šo visu skaistā lentītē, ģenerē atbildes galvenes un nosūta to atpakaļ uz lietotāja pārlūku.
- Lietotājs ir piepildīts ar prieku un dod sev siltu apskāvi!

### Un kāpēc tas ir svarīgi?

Pareizi centralizēta maršrutēšanas sistēma faktiski var ievērojami atvieglot jūsu dzīvi! Pirmajā reizē to var būt grūti saprast. Šeit ir daži iemesli, kāpēc:

- **Centralizēta Maršrutēšana**: Jūs varat uzturēt visas savas maršrutas vienuviet. Tas padara vieglāk redzēt, kādas maršrutas jums ir un ko tās dara. Tas arī padara vienkāršāku tos mainīt, ja nepieciešams.
- **Maršruta Parametri**: Jūs varat izmantot maršruta parametrus, lai padotu datus maršruta metodēm. Tas ir lielisks veids, kā saglabāt kodu tīru un kārtīgu.
- **Maršruta Grupas**: Jūs varat grupēt maršrutas kopā. Tas ir lieliski, lai saglabātu kodu kārtībā un pielietotu [starpvirsotnes](starpvirsotne) grupai maršrutu.
- **Maršruta Nosaukšana**: Jūs varat piešķirt nosaukumu maršrutai, lai URL varētu dinamiski ģenerētā vēlāk jūsu kodā (piemēram, kādā veidnē). Piemērs: vietojot `/lietotājs/1234` cietā kodolā, jūs varētu atsauce uz aļi `lietotājs_skats` un padot `id` kā parametru. Tas ir brīnišķīgi gadījumā, ja vēlaties to vēlāk mainīt uz `/administrators/lietotājs/1234`. Jums nebūs jāmaina visi cietie url, tikai URL, kas pievienots maršrutai.
- **Maršruta Starpvirsotne**: Jūs varat pievienot maršrutām starpvirsotni. Starpvirsotne ir ārkārtīgi spēcīga, pievienojot konkrētus uzvedības veidus savai lietojumprogrammai, piemēram, autentificējot, ka noteikts lietotājs var piekļūt maršrutam vai maršrutu grupai.

Esmu pārliecināts, ka jūs esat iepazinies ar skriptu pa skriptam veidu, kā izveidot tīmekļa vietni. Jums varētu būt fails, ko sauc par `index.php`, kurā ir daudz `if` izteiksmes, lai pārbaudītu URL un pēc tam palaistu konkrētu funkciju, pamatojoties uz URL. Tas ir veida maršrutēšana, bet tas nav ļoti organizēts un tas ātri var izvirtuļoties no kontroles. Flight maršrutēšanas sistēma ir daudz labāk organizēta un spēcīgāka maršrutēšanas veids.

Šis?

```php

// /lietotājs/skatīt_profila.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	skatītLietotājaProfilu($id);
}

// /lietotājs/rediģēt_profila.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	rediģētLietotājaProfilu($id);
}

// utt...
```

Vai šis?

```php

// index.php
Flight::route('/lietotājs/@id', [ 'UserController', 'skatītLietotājaProfilu' ]);
Flight::route('/lietotājs/@id/rediģēt', [ 'UserController', 'rediģētLietotājaProfilu' ]);

// Iespējams, jūsu app/controllers/UserController.php
class UserController {
	public function skatītLietotājaProfilu($id) {
		// dari kaut ko
	}

	public function rediģētLietotājaProfilu($id) {
		// dari kaut ko
	}
}
```

Cerams, jūs varat sākt izprast priekšrocības izmantojot centralizētu maršrutēšanas sistēmu. Tā ir daudz ērtāk pārvaldāma un saprotamā nākotnē!

## Pieprasījumi un Atbildes

Flight nodrošina vienkāršu un vieglu veidu, kā apstrādāt pieprasījumus un atbildes. Tas ir tīmekļa ietvara kodols. Tas ievieš pieprasījumu no lietotāja pārlūka, apstrādā to un pēc tam nosūta atbildi. Šis ir veids, kā jūs varat veidot tīmekļa lietojumprogrammas, kas darbojas tā, kā parāda lietotāja profilu, ļauj lietotājam ienākt, vai ļauj lietotājam izveidot jaunu bloga ierakstu.

### Pieprasījumi

Pieprasījums ir tas, ko lietotāja pārlūks sūta uz jūsu serveri, kad viņi apmeklē jūsu tīmekļa vietni. Šis pieprasījums satur informāciju par to, ko lietotājs vēlas darīt. Piemēram, tas var saturēt informāciju par to, kādu URL lietotājs vēlas apmeklēt, kādus datus lietotājs vēlas nosūtīt uz jūsu serveri vai kādu veidu datus lietotājs vēlas saņemt no jūsu servera. Svarīgi ir zināt, ka pieprasījums ir tikai lasījams. Jūs nevarat mainīt pieprasījumu, bet varat to lasīt.

Ar Flight ir vienkārši piekļūt informācijai par pieprasījumu. Jūs varat piekļūt informācijai par pieprasījumu, izmantojot metodi `Flight::request()`. Šī metode atgriež `Request` objektu, kas satur informāciju par pieprasījumu. Jūs varat izmantot šo objektu, lai piekļūtu informācijai par pieprasījumu, piemēram, URL, metodi vai datiem, ko lietotājs nosūtījis uz jūsu serveri.

### Atbildes

Atbilde ir tas, ko jūsu serveris nosūta atpakaļ uz lietotāja pārlūku, kad viņi apmeklē jūsu tīmekļa vietni. Šī atbilde satur informāciju par to, ko jūsu serveris vēlas darīt. Piemēram, tā var saturēt informāciju par to, kāda veida datus jūsu serveris vēlas nosūtīt lietotājam, kāda veida datus jūsu serveris vēlas saņemt no lietotāja vai kāda veida datus jūsu serveris vēlas saglabāt uz lietotāja datora.

Ar Flight ir vienkārši nosūtīt atbildi uz lietotāja pārlūku. Jūs varat nosūtīt atbildi, izmantojot metodi `Flight::response()`. Šī metode ņem `Response` objektu kā argumentu un nosūta atbildi lietotāja pārlūkam. Jūs varat izmantot šo objektu, lai nosūtītu atbildi lietotāja pārlūkam, piemēram, HTML, JSON vai failu. Flight palīdz jums automātiski ģenerēt dažas atbildes daļas, lai padarītu lietas vieglas, bet, galu galā, jums ir kontrole par to, ko atgriezt lietotājam.