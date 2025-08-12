# WordPress Integrācija: n0nag0n/wordpress-integration-for-flight-framework

Vai vēlaties izmantot Flight PHP savā WordPress vietnē? Šis spraudnis to padara vienkāršu! Ar `n0nag0n/wordpress-integration-for-flight-framework`, jūs varat palaist pilnu Flight lietotni tieši blakus jūsu WordPress instalācijai—ideāli piemērots pielāgotu API, mikropakalpojumu vai pat pilnvērtīgu lietotņu izveidošanai, neizejot no WordPress komforta.

---

## Ko tas dara?

- **Bezšuvju integrācija Flight PHP ar WordPress**
- Novirza pieprasījumus uz Flight vai WordPress, pamatojoties uz URL modeļiem
- Organizējiet savu kodu ar kontrolieriem, modeļiem un skatiem (MVC)
- Vienkārši iestatiet ieteikto Flight mapju struktūru
- Izmantojiet WordPress datu bāzes savienojumu vai savu pašu
- Precīzi noregulējiet, kā Flight un WordPress mijiedarbojas
- Vienkārša administrācijas saskarne konfigurācijai

## Instalācija

1. Augšupielādējiet `flight-integration` mapi uz jūsu `/wp-content/plugins/` direktoriju.
2. Aktivizējiet spraudni WordPress administrācijā (Plugins izvēlnē).
3. Dodieties uz **Settings > Flight Framework**, lai konfigurētu spraudni.
4. Iestatiet piegādātāja ceļu uz savu Flight instalāciju (vai izmantojiet Composer, lai instalētu Flight).
5. Konfigurējiet savu lietotnes mapes ceļu un izveidojiet mapju struktūru (spraudnis var palīdzēt ar to!).
6. Sāciet veidot savu Flight lietotni!

## Izmantošanas piemēri

### Pamata maršrutēšanas piemērs
Jūsu `app/config/routes.php` failā:

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### Kontroliera piemērs

Izveidojiet kontrolieri `app/controllers/ApiController.php`:

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // Jūs varat izmantot WordPress funkcijas iekš Flight!
        $users = get_users();
        $result = [];
        foreach($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email
            ];
        }
        Flight::json($result);
    }
}
```

Pēc tam jūsu `routes.php`:

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## BUJ

**J: Vai man jāzina Flight, lai izmantotu šo spraudni?**  
A: Jā, tas ir domāts izstrādātājiem, kuri vēlas izmantot Flight iekš WordPress. Ieteicams pamata zināšanas par Flight maršrutēšanu un pieprasījumu apstrādi.

**J: Vai tas palēninās manu WordPress vietni?**  
A: Nē! Spraudnis apstrādā tikai pieprasījumus, kas atbilst jūsu Flight maršrutiem. Visi citi pieprasījumi tiek novirzīti uz WordPress kā parasti.

**J: Vai es varu izmantot WordPress funkcijas savā Flight lietotnē?**  
A: Protams! Jums ir pilna piekļuve visām WordPress funkcijām, āķiem un globālajiem mainīgajiem no Flight maršrutiem un kontrolieriem.

**J: Kā es varu izveidot pielāgotus maršrutus?**  
A: Definējiet savus maršrutus `config/routes.php` failā savā lietotnes mapē. Skatiet parauga failu, ko izveido mapju struktūras ģenerators, piemēriem.

## Izmaiņu žurnāls

**1.0.0**  
Sākotnējais izlaidums.

---

Papildus informācijai skatiet [GitHub repo](https://github.com/n0nag0n/wordpress-integration-for-flight-framework).