# FlightPHP/Permissions

Tas ir atļauju modulis, ko var izmantot jūsu projektos, ja jums ir vairāki lomas jūsu lietotnē, un katrai rolai ir mazliet atšķirīga funkcionalitāte. Šis modulis ļauj definēt atļaujas katrai rolei un pēc tam pārbaudīt, vai pašreizējam lietotājam ir atļauja piekļūt konkrētai lapai vai veikt konkrētu darbību.

Uzstādīšana
-------
Palaist `composer require flightphp/permissions` un jūs esat gatavs darbam!

Lietošana
-------
Vispirms jums ir jāiestata savas atļaujas, pēc tam jums jāpaskaidro, ko nozīmē šīs atļaujas jūsu lietotnē. Galu galā jūs pārbaudīsiet savas atļaujas ar `$Permissions->has()`, `->can()` vai `is()`. `has()` un `can()` ir vienādas funkcionalitātes, bet tām ir dažādi nosaukumi, lai jūsu kods būtu vieglāk lasāms.

## Pamata paraugs

Iedomāsimies, ka jums ir iespēja jūsu lietotnē, kas pārbauda, vai lietotājs ir pierakstījies. Jūs varat izveidot atļauju objektu šādi:

```php
// index.php
require 'vendor/autoload.php';

// kods

// tad, visticamāk, jums ir kaut kas, kas paziņo, kāda ir personas pašreizējā loma
// iespējams, jums ir kaut kas, kas izvelk pašreizējo lomu
// no sesijas mainīgā, kas to definē
// pēc tam, kad kāds pierakstās, pretējā gadījumā viņiem būs "viesis" vai "publisks" loma.
$current_role = 'admin';

// iestatīt atļaujas
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// Jums drošamākērt būs šo objektu saglabāt Flight kaut kur
Flight::set('permission', $permission);
```

Tad kontrolētājā, iespējams, būs kaut kas līdzīgs tam.

```php
<?php

// kāds kontrolētājs
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// darīt kaut ko
		} else {
			// darīt kaut ko citu
		}
	}
}
```

Jūs to varat izmantot arī, lai sekotu, vai viņiem ir atļauja veikt kaut ko jūsu lietotnē.
Piemēram, ja ir iespējams, ka lietotāji var mijiedarboties, veicot ierakstus jūsu programmatūrā, jūs varat
pārbaudiet, vai viņiem ir atļauja veikt kādas darbības.

```php
$current_role = 'admin';

// iestatīt atļaujas
$permission = new \flight\Permission($current_role);
$permission->defineRule('post', function($current_role) {
	if($current_role === 'admin') {
		$permissions = ['create', 'read', 'update', 'delete'];
	} else if($current_role === 'editor') {
		$permissions = ['create', 'read', 'update'];
	} else if($current_role === 'author') {
		$permissions = ['create', 'read'];
	} else if($current_role === 'contributor') {
		$permissions = ['create'];
	} else {
		$permissions = [];
	}
	return $permissions;
});
Flight::set('permission', $permission);
```

Tad kaut kur kontrolētājā...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// darīt kaut ko
		} else {
			// darīt kaut ko citu
		}
	}
}
```

## Piesaistīšanas atkarības
Jūs varat piesaistīt atkarības cietumā, kas nosaka atļaujas. Tas ir noderīgi, ja jums ir kāda tumbiņa, id vai jebkura cita datu punkta, pret kuru vēlaties pārbaudīt. Tas pats darbojas ar Klas->Metode calls, izņemot to, ka argumentus definējat metode.

### Tumbiņas

```php
$Permission->defineRule('order', function(string $current_role, MyDependency $MyDependency = null) {
	// ... kods
});

// jūsu kontrolētāja failā
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('permission');
	if ($permission->can('order.create', $MyDependency)) {
		// darīt kaut ko
	} else {
		// darīt kaut ko citu
	}
}
```

### Klases

```php
namespace MyApp;

class Permissions {

	public function order(string $current_role, MyDependency $MyDependency = null) {
		// ... kods
	}
}
```

## Saīsinājums lai iestatītu atļaujas ar klasēm
Jūs varat izmantot arī klases, lai definētu savas atļaujas. Tas ir noderīgi, ja jums ir daudz atļauju, un jūs vēlaties saglabāt savu kodu tīru. Jūs varat darīt kaut ko līdzīgu:

```php
<?php

// starta kods
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// Pieņemsim, ka esat to uzstādījuši iepriekš
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'read' ]; // ikviens var apskatīt pasūtījumu
		if($current_role === 'manager') {
			$allowed_permissions[] = 'create'; // vadītāji var veidot pasūtījumus
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'update'; // ja lietotājam ir īpaša slīdera, viņi var atjaunināt pasūtījumus
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'delete'; // administratori var dzēst pasūtījumus
		}
		return $allowed_permissions;
	}
}
```
Lieliskā daļa ir tā, ka ir arī saīsinājums, ko varat izmantot (kas var tikt kešota!), kur jūs tikai sakāt atļauju klasei atspoguļot visus metodus klasē par atļaujām. Tāpēc, ja jums ir metode ar nosaukumu `order()` un metode ar nosaukumu `company()`, šie automātiski tiks atspoguļoti, tāpēc jūs varat vienkārši izpildīt `$Permissions->has('order.read')` vai `$Permissions->has('company.read')` tas strādās. Definēšana šī ir ļoti sarežģīta, tāpēc palieciet līdzi šeit. Jums vienkārši jāizdara šādi:

Izveidojiet atļauju klasi, kuru vēlaties grupēt kopā.
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// kods, lai noteiktu atļaujas
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// kods, lai noteiktu atļaujas
		return $permissions_array;
	}
}
```

Tad padariet atļaujas atpazīstamas, izmantojot šo bibliotēku.

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

Visbeidzot, izsauciet atļauju savā kodolā, lai pārbaudītu, vai lietotājam ir atļauts veikt noteiktu atļauju.

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.create') === false) {
			die('Jums nav atļauts izveidot pasūtījumu. Atvainojiet!');
		}
	}
}
```

### Kešošana

Lai iespējotu kešošanu, skatiet vienkāršo [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) bibliotēku. Piemērs, kā to iespējot, ir tālāk.
```php

// šis $app var būt jūsu koda daļa vai
// jūs vienkārši varat padot null, un tas 
// izvilkts no Flight::app() konstruktorā
$app = Flight::app();

// Pašreiz tas akceptē tikai faila kešu. Turpmāk var viegli
// pievienot citus
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 ir cik daudz sekunžu kešot tam. Atstājiet to atslēgtu, ja nevēlaties lietot kešošanu
```

Un jūs esat gatavi!