# Augšupielādētā faila apstrādātājs

## Pārskats

`UploadedFile` klase Flight padara vieglu un drošu failu augšupielādes apstrādi jūsu lietojumprogrammā. Tā aptver PHP failu augšupielādes procesa detaļas, piedāvājot vienkāršu, objektorientētu veidu, kā piekļūt faila informācijai un pārvietot augšupielādētos failus.

## Saprašana

Kad lietotājs augšupielādē failu caur formu, PHP saglabā informāciju par failu `$_FILES` superglobālajā mainīgajā. Flight vidē jūs reti mijiedarbojaties ar `$_FILES` tieši. Tā vietā Flight `Request` objekts (pieejams caur `Flight::request()`) nodrošina `getUploadedFiles()` metodi, kas atgriež `UploadedFile` objektu masīvu, padarot failu apstrādi daudz ērtāku un izturīgāku.

`UploadedFile` klase nodrošina metodes, lai:
- Iegūtu oriģinālo faila nosaukumu, MIME tipu, izmēru un pagaidu atrašanās vietu
- Pārbaudītu augšupielādes kļūdas
- Pārvietotu augšupielādēto failu uz pastāvīgu atrašanās vietu

Šī klase palīdz izvairīties no izplatītām kļūdām ar failu augšupielādi, piemēram, kļūdu apstrādes vai drošas failu pārvietošanas.

## Pamata izmantošana

### Piekļuve augšupielādētajiem failiem no pieprasījuma

Ieteicamais veids, kā piekļūt augšupielādētajiem failiem, ir caur pieprasījuma objektu:

```php
Flight::route('POST /upload', function() {
    // Par formu lauku ar nosaukumu <input type="file" name="myFile">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    $file = $uploadedFiles['myFile'];

    // Tagad jūs varat izmantot UploadedFile metodes
    if ($file->getError() === UPLOAD_ERR_OK) {
        $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
        echo "Fails augšupielādēts veiksmīgi!";
    } else {
        echo "Augšupielāde neizdevās: " . $file->getError();
    }
});
```

### Daudzu failu augšupielādes apstrāde

Ja jūsu forma izmanto `name="myFiles[]"` vairāku augšupielāžu gadījumā, jūs saņemsiet `UploadedFile` objektu masīvu:

```php
Flight::route('POST /upload', function() {
    // Par formu lauku ar nosaukumu <input type="file" name="myFiles[]">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    foreach ($uploadedFiles['myFiles'] as $file) {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
            echo "Augšupielādēts: " . $file->getClientFilename() . "<br>";
        } else {
            echo "Neizdevās augšupielādēt: " . $file->getClientFilename() . "<br>";
        }
    }
});
```

### UploadedFile instances manuāla izveide

Parasti jūs neizveidosiet `UploadedFile` manuāli, bet to var izdarīt, ja nepieciešams:

```php
use flight\net\UploadedFile;

$file = new UploadedFile(
  $_FILES['myfile']['name'],
  $_FILES['myfile']['type'],
  $_FILES['myfile']['size'],
  $_FILES['myfile']['tmp_name'],
  $_FILES['myfile']['error']
);
```

### Piekļuve faila informācijai

Jūs viegli varat iegūt detaļas par augšupielādēto failu:

```php
echo $file->getClientFilename();   // Oriģinālais faila nosaukums no lietotāja datora
echo $file->getClientMediaType();  // MIME tips (piem., image/png)
echo $file->getSize();             // Faila izmērs baitos
echo $file->getTempName();         // Pagaidu faila ceļš uz servera
echo $file->getError();            // Augšupielādes kļūdas kods (0 nozīmē bez kļūdas)
```

### Augšupielādētā faila pārvietošana

Pēc faila validēšanas pārvietojiet to uz pastāvīgu atrašanās vietu:

```php
try {
  $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
  echo "Fails augšupielādēts veiksmīgi!";
} catch (Exception $e) {
  echo "Augšupielāde neizdevās: " . $e->getMessage();
}
```

`moveTo()` metode izmet izņēmumu, ja kaut kas noiet greizi (piemēram, augšupielādes kļūda vai atļauju problēma).

### Augšupielādes kļūdu apstrāde

Ja augšupielādes laikā radās problēma, jūs varat iegūt lasāmu kļūdas ziņojumu cilvēkam:

```php
if ($file->getError() !== UPLOAD_ERR_OK) {
  // Jūs varat izmantot kļūdas kodu vai noķert izņēmumu no moveTo()
  echo "Radās kļūda, augšupielādējot failu.";
}
```

## Skatīt arī

- [Requests](/learn/requests) - Uzziniet, kā piekļūt augšupielādētajiem failiem no HTTP pieprasījumiem un redzēt vairāk failu augšupielādes piemēru.
- [Configuration](/learn/configuration) - Kā konfigurēt augšupielādes ierobežojumus un direktorijas PHP.
- [Extending](/learn/extending) - Kā pielāgot vai paplašināt Flight kodola klases.

## Traucējummeklēšana

- Vienmēr pārbaudiet `$file->getError()` pirms faila pārvietošanas.
- Pārliecinieties, ka jūsu augšupielādes direktorija ir rakstāma tīmekļa serverim.
- Ja `moveTo()` neizdodas, pārbaudiet izņēmuma ziņojumu detaļām.
- PHP `upload_max_filesize` un `post_max_size` iestatījumi var ierobežot failu augšupielādi.
- Vairāku failu augšupielādes gadījumā vienmēr iterējiet caur `UploadedFile` objektu masīvu.

## Izmaiņu žurnāls

- v3.12.0 - Pievienota `UploadedFile` klase pieprasījuma objektam vieglākai failu apstrādei.