# Kāpēc ietvaru?

Daudzi programmētāji stingri iebilst pret ietvaru izmantošanu. Viņi argumentē, ka ietvari ir pārpildīti, lēni un grūti apgūstami. Viņi saka, ka ietvari nav nepieciešami un ka jūs varat rakstīt labāku kodu bez tiem. Noteikti var izteikt dažus pamatoti iebildumus pret ietvaru izmantošanu. Tomēr ir arī daudz priekšrocību, izmantojot ietvarus.

## Iemesli izmantot ietvaru

Šeit ir daži iemesli, kāpēc jums varētu būt jāapsver ietvara izmantošana:

- **Ātrā attīstība**: Ietvari nodrošina daudz funkcionalitātes no paša sākuma. Tas nozīmē, ka jūs varat ātrāk veidot tīmekļa lietojumprogrammas. Jums nav jāraksta tik daudz koda, jo ietvars nodrošina lielu daļu funkcionalitātes, kas jums nepieciešama.
- **Konzistence**: Ietvari nodrošina vienotu veidu, kā darīt lietas. Tas padara vieglāku saprast, kā kods darbojas un atvieglo citiem izstrādātājiem saprast jūsu kodu. Ja jums ir skripti pēc skripta, varat zaudēt konsekvenci starp skriptiem, īpaši, ja strādājat ar izstrādātāju komandu.
- **Drošība**: Ietvari nodrošina drošības funkcijas, kas palīdz aizsargāt jūsu tīmekļa lietojumprogrammas no bieži sastopamām drošības draudiem. Tas nozīmē, ka jums nav jāuztraucas tik daudz par drošību, jo ietvars daudzās jomās rūpējas par to.
- **Kopiena**: Ietvariem ir lieli izstrādātāju kopienas, kas dod ieguldījumus ietvarā. Tas nozīmē, ka jūs varat saņemt palīdzību no citiem izstrādātājiem, kad jums ir jautājumi vai problēmas. Tas arī nozīmē, ka ir daudz resursu, kas palīdz jums apgūt, kā izmantot ietvaru.
- **Labās prakses**: Ietvari tiek izstrādāti, izmantojot labās prakses. Tas nozīmē, ka jūs varat mācīties no ietvara un izmantot tās pašas labās prakses savā kodā. Tas var palīdzēt jums kļūt par labāku programmētāju. Dažreiz jums nav zināšanu par to, ko nezināt, un tas jums visbeidzot var likt sasistas.
- **Pievienojamība**: Ietvari ir izstrādāti, lai tos varētu paplašināt. Tas nozīmē, ka varat pievienot savu funkcionalitāti ietvaram. Tas ļauj jums veidot tīmekļa lietojumprogrammas, kas pielāgotas jūsu konkrētajām vajadzībām.

Flight ir mikroietvars. Tas nozīmē, ka tas ir neliels un viegls. Tas nepiedāvā tik daudz funkcionalitātes kā lielāki ietvari, piemēram, Laravel vai Symfony. Tomēr tas nodrošina lielu daļu funkcionalitātes, kas jums nepieciešama, lai veidotu tīmekļa lietojumprogrammas. Tas arī ir viegli apgūstams un lietojams. Tas padara to par labu izvēli, lai ātri un viegli veidotu tīmekļa lietojumprogrammas. Ja esat jauns ietvaru jomā, Flight ir lielisks iesācēju ietvars, ar kuru sākt. Tas palīdzēs jums iemācīties par ietvaru izmantošanas priekšrocībām, neplūstot jums ar pārāk daudz sarežģītības. Kad esat ieguvis kādu pieredzi ar Flight, būs vieglāk pāriet uz sarežģītākiem ietvariem, piemēram, Laravel vai Symfony, taču Flight joprojām var veiksmīgi veidot izturīgu lietojumprogrammu.

## Kas ir Maršrutēšana?

Maršrutēšana ir Flight ietvara pamatā, bet kas tas īsti ir? Maršrutēšana ir process, kurā tiek ņemts URL un tiek saskaņots ar konkrētu funkciju jūsu kodā. Tā ir veids, kā padarīt jūsu tīmekļa vietni dažāda satura, balstoties uz pieprasīto URL. Piemēram, jūs varētu vēlēties parādīt lietotāja profilu, kad viņi apmeklē `/lietotājs/1234`, bet parādīt visu lietotāju sarakstu, kad viņi apmeklē `/lietotāji`. Tas ir visu izdarīts caur maršrutēšanu.

Tas varētu darboties šādi:

- Lietotājs dodas uz jūsu pārlūkprogrammu un ieraksta `http://piemērs.com/lietotājs/1234`.
- Serveris saņem pieprasījumu un skatās URL, nododot to jūsu Flight lietojumprogrammas kodam.
- Iedomāsimies, ka jūsu Flight kodā ir kaut kas tāds kā `Flight::route('/lietotājs/@id', ['LietotājuKontrolieris', 'skatītLietotājaProfila']);`. Jūsu Flight lietojumprogrammas kods apskata URL un redz, ka tas atbilst jums definētajam maršrutam, un tad izpilda kodu, ko esat definējis šim maršrutam.
- Flight maršrutētājs pēc tam palaiž un izsauc `skatītLietotājaProfila($id)` metodi `LietotājuKontrolieris` klasē, padodot `1234` kā `$id` argumentu šajā metodē.
- Koda jūsu `skatītLietotājaProfila()` metode pēc tam darbosies un darīs to, ko esat tai pateicis. Jūs varat beigt ar ātras lietotāja profila sākuma HTML atspoguļošanu, vai ja tas ir RESTful API, jūs varat atspoguļot JSON atbildi ar lietotāja informāciju.
- Flight ietina to skaisti, ģenerē atbildes galvenes un nosūta to atpakaļ uz lietotāja pārlūku.
- Lietotājs ir piepildīts ar prieku un dod sev siltu apskāvi!

### Un kāpēc ir svarīgi?

Pareiza centralizēta maršrutētāja sistēma faktiski var padarīt jūsu dzīvi dramatiski vieglāku! Tas var būt grūti pamanāms sākumā. Šeit ir daži iemesli, kāpēc:

- **Centralizēta Maršrutēšana**: Jūs varat turēt visus savus maršrutus vienuviet. Tas padara vieglāk redzēt, kādus maršrutus jums ir un ko tie dara. Tas arī atvieglo tos mainīt, ja ir nepieciešams.
- **Maršruta Parametri**: Jūs varat izmantot maršruta parametrus, lai padotu datus savām maršruta metodēm. Tas ir lielisks veids, kā saglabāt kodu tīru un organizētu.
- **Maršrutu Grupēšana**: Jūs varat grupēt maršrutus kopā. Tas ir lielisks veids, kā organizēt kodu un piemērot [starpniekprogrammatūru](middleware) maršrutu grupai.
- **Maršruta Aliasing**: Jūs varat piešķirt aliasu maršrutam, lai vēlāk dinamiski varētu ģenerēt URL savā kodā (piemēram, šablona gadījumā). Piemēram, tajā vietā, lai ciet kodētu `/lietotājs/1234` savā kodā, jūs varētu atsauce vietā lietot `lietotājs_skatīt` un padot `id` kā parametru. Tas padara to brīnišķīgu gadījumā, ja nolēmīsiet to vēlāk mainīt uz `/administrators/lietotājs/1234`. Jums nebūs jāmaina visi jūsu cietkodētās URL, vienkārši URL, kas pievienots maršrutam.
- **Maršruta Starpniekprogrammatūra**: Jūs varat pievienot starpniekprogrammatūru savām maršrutām. Starpniekprogrammatūra ir ārkārtīgi spēcīga, pievienojot konkrētas darbības jūsu lietojumprogrammai, piemēram, autentificējot !{a} lietotāju piekļuvi maršrutai vai maršrutu grupai.

Es esmu drošs, ka esat iepazinies ar skriptu pa skriptam veidu, kā radīt tīmekļa vietni. Jums var būt fails, ko sauc `index.php`, kurā ir daudz `if` paziņojumu, lai pārbaudītu URL un tad izpildītu konkrētu funkciju, pamatojoties uz URL. Tas ir veids, kā maršrutēšana, bet tas nav ļoti organizēts un tas var ātri izvērsties. Flight maršrutēšanas sistēma ir daudz organizētāka un spēcīgāka veidā, kā apstrādāt maršrutēšanu.

Tas?

```php
// /lietotajs/skatit_profila.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	skatitLietotajaProfilu($id);
}

// /lietotajs/rediget_profila.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	reditLietotajaProfilu($id);
}

// u.c...
```

Vai tas?

```php
// index.php
Flight::route('/lietotajs/@id', ['LietotajaKontrolieris', 'skatitLietotajaProfilu']);
Flight::route('/lietotajs/@id/edit', ['LietotajaKontrolieris', 'reditLietotajaProfilu']);

// Varbūt jūsu app/controllers/LietotajaKontrolieris.php
class LietotajaKontrolieris {
	public function skatitLietotajaProfilu($id) {
		// darīt kaut ko
	}

	public function reditLietotajaProfilu($id) {
		// darīt kaut ko
	}
}
```

Cerams, jūs sākat ieraudzīt ieguvumus no centralizētas maršrutēšanas sistēmas izmantošanas. Ilgtermiņā to ir daudz vieglāk pārvaldīt un saprast!

## Pieprasījumi un Atbildes

Flight nodrošina vienkāršu un vieglu veidu, kā apstrādāt pieprasījumus un atbildes. Tas ir tīmekļa ietvaru būtība. Tas pieņem pieprasījumu 
no lietotāja pārlūka, apstrādā to, un pēc tam nosūta atbildi. Tā ir veids, kā jūs varat veidot tīmekļa lietojumprogrammas, kas darbojas, piemēram, 
rāda lietotāja profilu, ļauj lietotājam pierakstīties vai ļauj lietotājam publicēt jaunu bloga ierakstu.

### Pieprasījumi

Pieprasījums ir tas, ko lietotāja pārlūks sūta uz jūsu serveri, kad viņi apmeklē jūsu tīmekļa vietni. Šis pieprasījums satur informāciju par to, ko lietotājs 
vēlas darīt. Piemēram, tas var saturēt informāciju par to, kādu URL lietotājs vēlas apmeklēt, kādu datu lietotājs vēlas nosūtīt uz jūsu serveri vai kādu veidu 
dati lietotājs vēlas saņemt no jūsu servera. Svarīgi ir zināt, ka pieprasījums ir tikai lasīšanas režīmā. Jūs nevarat mainīt pieprasījumu, bet jūs to varat lasīt.

Flight nodrošina vienkāršu veidu, kā piekļūt informācijai par pieprasījumu. Jūs varat piekļūt informācijai par pieprasījumu, izmantojot `Flight::request()` 
metodi. Šī metode atgriež `Pieprasījums` objektu, kas satur informāciju par pieprasījumu. Jūs varat izmantot šo objektu, lai piekļūtu informācijai par 
pieprasījumu, piemēram, URL, metodi vai datiem, ko lietotājs nosūtījis uz jūsu serveri.

### Atbildes

Atbilde ir tas, ko jūsu serveris nosūta atpakaļ uz lietotāja pārlūku, kad viņi apmeklē jūsu tīmekļa vietni. Šī atbilde satur informāciju par to, 
ko jūsu serveris vēlas darīt. Piemēram, tas var saturēt informāciju par to, kāda veida datus jūsu serveris vēlas nosūtīt lietotājam, kāda veida datus jūsu 
serveris vēlas saņemt no lietotāja vai kāda veida datus jūsu serveris vēlas saglabāt lietotāja datorā.

Flight nodrošina vienkāršu veidu, kā nosūtīt atbildi uz lietotāja pārlūku. Jūs varat nosūtīt atbildi, izmantojot `Flight::response()` metodi. Šī metode 
nospiež `Atbilde` objektu kā argumentu un nosūta atbildi uz lietotāja pārlūku. Jūs varat izmantot šo objektu, lai nosūtītu atbildi uz lietotāja pārlūku, piemēram, 
HTML, JSON vai failu. Flight palīdz jums automātiski ģenerēt dažas atbildes daļas, lai padarītu lietas vieglas,# Kāpēc ietvaru?

Daudzi programmētāji stingri iebilst pret ietvaru izmantošanu. Viņi argumentē, ka ietvari ir pārpildīti, lēni un grūti apgūstami. Viņi saka, ka ietvari nav nepieciešami un ka jūs varat rakstīt labāku kodu bez tiem. Noteikti var izteikt dažus pamatoti iebildumus pret ietvaru izmantošanu. Tomēr ir arī daudz priekšrocību, izmantojot ietvarus.

## Iemesli izmantot ietvaru

Šeit ir daži iemesli, kāpēc jums varētu būt jāapsver ietvara izmantošana:

- **Ātrā attīstība**: Ietvari nodrošina daudz funkcionalitātes no paša sākuma. Tas nozīmē, ka jūs varat ātrāk veidot tīmekļa lietojumprogrammas. Jums nav jāraksta tik daudz koda, jo ietvars nodrošina lielu daļu funkcionalitātes, kas jums nepieciešama.
- **Konzistence**: Ietvari nodrošina vienotu veidu, kā darīt lietas. Tas padara vieglāku saprast, kā kods darbojas un atvieglo citiem izstrādātājiem saprast jūsu kodu. Ja jums ir skripti pēc skripta, varat zaudēt konsekvenci starp skriptiem, īpaši, ja strādājat ar izstrādātāju komandu.
- **Drošība**: Ietvari nodrošina drošības funkcijas, kas palīdz aizsargāt jūsu tīmekļa lietojumprogrammas no bieži sastopamām drošības draudiem. Tas nozīmē, ka jums nav jāuztraucas tik daudz par drošību, jo ietvars daudzās jomās rūpējas par to.
- **Kopiena**: Ietvariem ir lieli izstrādātāju kopienas, kas dod ieguldījumus ietvarā. Tas nozīmē, ka jūs varat saņemt palīdzību no citiem izstrādātājiem, kad jums ir jautājumi vai problēmas. Tas arī nozīmē, ka ir daudz resursu, kas palīdz jums apgūt, kā izmantot ietvaru.
- **Labās prakses**: Ietvari tiek izstrādāti, izmantojot labās prakses. Tas nozīmē, ka jūs varat mācīties no ietvara un izmantot tās pašas labās prakses savā kodā. Tas var palīdzēt jums kļūt par labāku programmētāju. Dažreiz jums nav zināšanu par to, ko nezināt, un tas jums visbeidzot var likt sasistas.
- **Pievienojamība**: Ietvari ir izstrādīti, lai tos varētu paplašināt. Tas nozīmē, ka varat pievienot savu funkcionalitāti ietvaram. Tas ļauj jums veidot tīmekļa lietojumprogrammas, kas pielāgotas jūsu konkrētajām vajadzībām.

Flight ir mikroietvars. Tas nozīmē, ka tas ir neliels un viegls. Tas nepiedāvā tik daudz funkcionalitātes kā lielāki ietvari, piemēram, Laravel vai Symfony. Tomēr tas nodrošina lielu daļu funkcionalitātes, kas jums nepieciešama, lai veidotu tīmekļa lietojumprogrammas. Tas arī ir viegli apgūstams un lietojams. Tas padara to par labu izvēli, lai ātri un viegli veidotu tīmekļa lietojumprogrammas. Ja esat jauns ietvaru jomā, Flight ir lielisks iesācēju ietvars, ar kuru sākt. Tas palīdzēs jums iemācīties par ietvaru izmantošanas priekšrocībām, neplūstot jums ar pārāk daudz sarežģītības. Kad esat ieguvis kādu pieredzi...
