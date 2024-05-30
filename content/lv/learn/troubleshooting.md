# Problemløkšana

Šī lapā jums palīdzēs novērst bieži sastopamos jautājumus, ar kuriem varat saskarties, izmantojot Flight.

## Bieži sastopamie jautājumi

### 404 nav atrasts vai negaidīta maršruta uzvedība

Ja redzat kļūdu 404 nav atrasts (bet jūs zvērējat par savu dzīvību, ka tas tur tiešām ir un tas nav tikai kļūda) tas faktiski varētu būt problēma, ka atgriežat vērtību savā maršruta beigu punktā, nevis vienkārši to izvadot. Iemesls tam ir nodomāts, bet tas varētu izslīdēt no roku dažiem izstrādātājiem.

```php

Flight::route('/hello', function(){
	// Tas var izraisīt kļūdu 404 nav atrasts
	return 'Sveika, pasaule!';
});

// To, ko jūs visticamāk vēlaties
Flight::route('/hello', function(){
	echo 'Sveika, pasaule!';
});

```

Iemesls tam ir speciāla mehānisma iebūvēšana maršrutētājā, kas apstrādā atgriezenošanās izvadi kā vienīgu "doties uz nākamo maršrutu". Jūs varat redzēt šādu uzvedību dokumentētu sadaļā [Maršrutēšana](/learn/routing#passing).