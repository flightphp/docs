# FlightPHP/Atļaujas

Tas ir atļauju modulis, ko var izmantot jūsu projektos, ja jums ir vairākas lomas jūsu lietotnē, un katram no tiem ir nedaudz atšķirīga funkcionalitāte. Šis modulis ļauj definēt atļaujas katrai rolei un pēc tam pārbaudīt, vai pašreizējam lietotājam ir atļauja piekļūt konkrētai lapai vai veikt konkrētu darbību.

Noklikšķiniet [šeit](https://github.com/flightphp/permissions) GitHub repozitorijai.

Uzstādīšana
-------
Izpildiet `composer require flightphp/permissions`, un esat gatavs darbam!

Lietošana
-------
Vispirms jums ir jāiestata savas atļaujas, tad jums jāpasaka savai lietotnei, ko šīs atļaujas nozīmē. Galu galā jūs pārbaudīsiet savas atļaujas ar `$Permissions->has()`, `->can()` vai `is()`. `has()` un `can()` ir vienādas funkcijas, bet sauktas atšķirīgi, lai jūsu kods būtu lasāmāks.

## Pamata piemērs

Iedomāsimies, ka jums ir funkcija jūsu lietotnē, kas pārbauda, vai lietotājs ir pierakstījies. Jūs varat izveidot atļauju objektu šādi:

```php
// index.php
require 'vendor/autoload.php';

// kods

// pēc tam, iespējams, jums ir kaut kas, kas jums paziņo, kāda ir pašreizējā lietotāja loma
// visticamāk, jums ir kaut kas, kas nosaka pašreizējo lomu
// no sesijas mainīgā, kas to definē
// pēc reģistrēšanās, pretējā gadījumā viņiem būs "viesis" vai "publiska" loma.
$current_role = 'admins';

// iestatīt atļaujas
$permission = new \flight\Permissions($current_role);
$permission->defineRule('pieslēdzies', function($current_role) {
	return $current_role !== 'viesis';
});

// Iespējams, ka vēlēsieties šo objektu noturētiegavēkur visur
Flight::set('permission', $permission);
```

Tad kādā kontrolierī varētu būt kaut kas tāds.

```php
<?php

// dažs kontrolieris
class DaļasKontrolieris {
	public function daļasDarbība() {
		$atļauja = Flight::get('permission');
		if ($atļauja->has('pieslēdzies')) {
			// dari kaut ko
		} else {
			// dari ko citu
		}
	}
}
```

Jūs varat izmantot arī to, lai sekotu, vai tiem ir atļauja kaut ko darīt jūsu lietotnē.
Piemēram, ja jums ir veids, kā lietotāji var mijiedarboties ar saviem ierakstiem jūsu programmā, jūs varat
pārbaudiet, vai viņiem ir atļauja veikt konkrētas darbības.

```php
$current_role = 'admins';

// iestatīt atļaujas
$permission = new \flight\Permission($current_role);
$permission->defineRule('ieraksts', function($current_role) {
	if($current_role === 'admins') {
		$atļaujas = ['izveidot', 'lasīt', 'labot', 'dzēst'];
	} else if($current_role === 'redaktors') {
		$atļaujas = ['izveidot', 'lasīt', 'labot'];
	} else if($current_role === 'autors') {
		$atļaujas = ['izveidot', 'lasīt'];
	} else if($current_role === 'līdzstrādnieks') {
		$atļaujas = ['izveidot'];
	} else {
		$atļaujas = [];
	}
	return $atļaujas;
});
Flight::set('permission', $permission);
```

Tad kaut kur kontrolierī...

```php
class IerakstaKontrolieris {
	public function izveidot() {
		$atļauja = Flight::get('permission');
		if ($atļauja->can('ieraksts.izveidot')) {
			// dari kaut ko
		} else {
			// dari ko citu
		}
	}
}
```

## Injicēt atkarības
Jūs varat injicēt atkarības closure, kas definē atļaujas. Tas ir noderīgi, ja jums ir kāda veida pārslēgšana, ID vai jebkura cita dati, pret kuriem vēlaties pārbaudīt. Tas pats attiecas uz Klases->Metodes zvana veidiem, izņemot to, ka argumentus definējat metodē.

### Closure

```php
$Permission->defineRule('pasūtījums', function(string $current_role, MyDependency $MyDependency = null) {
	// ... kods
});

// jūsu kontroliera failā
public function izveidotPasūtījumu() {
	$MyDependency = Flight::myDependency();
	$atļauja = Flight::get('permission');
	if ($atļauja->can('pasūtījums.izveidot', $MyDependency)) {
		// dari kaut ko
	} else {
		// dari ko citu
	}
}
```

### Klases

```php
namespace ManLietotne;

class Atļaujas {

	public function pasūtījums(string $current_role, MyDependency $MyDependency = null) {
		// ... kods
	}
}
```

## Saīsinājums, lai iestatītu atļaujas ar klasēm
Jūs varat izmantot arī klases, lai definētu savas atļaujas. Tas ir noderīgi, ja jums ir daudz atļauju, un vēlaties uzturēt savu kodu tīru. Jūs varat darīt kaut ko tādu kā šis:
```php
<?php

// palaistāmais kods
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('pasūtījums', 'ManLietotne\Atļaujas->pasūtījums');

// myapp/Permissions.php
namespace ManLietotne;

class Atļaujas {

	public function pasūtījums(string $current_role, int $lietotāja_id) {
		// Asumējot, ka šis ir iestatīts iepriekš
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$atļautās_atļaujas = [ 'lasīt' ]; // ikviens var skatīties pasūtījumu
		if($current_role === 'menedžeris') {
			$atļautās_atļaujas[] = 'izveidot'; // vadītāji var izveidot pasūtījumus
		}
		$dažs īpašs_tētis_no_db = $db->fetchField('SELECT dažs_īpašs_tētis FROM iestatījumi WHERE id = ?', [ $lietotāja_id ]);
		if($dažs_īpašs_tētis_no_db) {
			$atļautās_atļaujas[] = 'labot'; // Ja lietotājam ir īpaša tētis, viņi var atjaunināt pasūtījumus
		}
		if($current_role === 'admins') {
			$atļautās_atļaujas[] = 'dzēst'; // administratori var dzēst pasūtījumus
		}
		return $atļautās_atļaujas;
	}
}
```
Cool daļa ir tāda, ka ir arī saīsinājums, ko varat izmantot (kas var būt arī kešēta!!!), kur vienkārši pateiksit atļauju klasei atkartot visus metodus klasē. Tāpēc, ja ir metode ar nosaukumu `pasūtījums()` un metode ar nosaukumu `uzņēmums()`, šīs automātiski tiks atkartotas, lai jūs varētu vienkārši izpildīt `$Permissions->has('pasūtījums.lasīt')` vai `$Permissions->has('uzņēmums.lasīt')` un tas strādās. To definēt ir ārkārtīgi grūti, tāpēc palieciet šeit kopā ar mani. Jums vienkārši jāizdara šādi:

Izveidojiet atļauju klases grupēšanai.
```php
class ManasAtļaujas {
	public function pasūtījums(string $current_role, int $pasūtījuma_id = 0): array {
		// kods, lai noteiktu atļaujas
		return $atļaujas_masīvs;
	}

	public function uzņēmums(string $current_role, int $uzņēmuma_id): array {
		// kods, lai noteiktu atļaujas
		return $atļaujas_masīvs;
	}
}
```

Tad padarīt atļaujas atrodamas, izmantojot šo bibliotēku.

```php
$Atļaujas = new \flight\Permission($current_role);
$Atļaujas->defineRulesFromClassMethods(ManasAtļaujas::class);
Flight::set('permissions', $Atļaujas);
```

Visbeidzot, zvaniet atļaujai savā kodā, lai pārbaudītu, vai lietotājam ir atļauts veikt noteiktu atļauju.

```php
class DažsKontrolieris {
	public function izveidotPasūtījumu() {
		if(Flight::get('permissions')->can('pasūtījums.izveidot') === false) {
			die('Jums nav atļauts izveidot pasūtījumu. Atvainojiet!');
		}
	}
}
```

### Kešošana

Lai iespējotu kešošanu, skatiet vienkāršo [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) bibliotēku. Zemāk ir piemērs, kā iespējot to.
```php

// šis $app var būt daļa no jūsu koda vai
// varat vienkārši padot null, un tas tiks iegūts
// no Flight::app() konstruktorā
$app = Flight::app();

// Pašlaik tas pieņem šo vienumu kā failu kešu. Citus var viegli
// pievienot nākotnē. 
$Kešatmiņa = new Wruczek\PhpFailaKešatmiņa\PhpFailaKešatmiņa;

$Atļaujas = new \flight\Permission($current_role, $app, $Kešatmiņa);
$Atļaujas->defineRulesFromClassMethods(ManasAtļaujas::class, 3600); // 3600 ir cik daudz sekundes to kešot. Atstājiet to uz neatcelšanu
```

Un dodieties!

