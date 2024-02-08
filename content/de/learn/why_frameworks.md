# Warum ein Framework?

Einige Programmierer sind vehement gegen die Verwendung von Frameworks. Sie argumentieren, dass Frameworks aufgebläht, langsam und schwer zu erlernen sind. Sie sagen, dass Frameworks unnötig sind und dass man besseren Code ohne sie schreiben kann. Es gibt sicherlich einige gültige Punkte bezüglich der Nachteile der Verwendung von Frameworks. Es gibt jedoch auch viele Vorteile bei der Verwendung von Frameworks.

## Gründe für die Verwendung eines Frameworks

Hier sind ein paar Gründe, warum du vielleicht in Betracht ziehen solltest, ein Framework zu verwenden:

- **Schnelle Entwicklung**: Frameworks bieten von Anfang an viele Funktionen. Das bedeutet, dass du Webanwendungen schneller entwickeln kannst. Du musst nicht so viel Code schreiben, weil das Framework einen Großteil der Funktionalität bereitstellt, die du benötigst.
- **Konsistenz**: Frameworks bieten eine konsistente Art, Dinge zu erledigen. Dies macht es einfacher zu verstehen, wie der Code funktioniert, und macht es anderen Entwicklern einfacher zu verstehen, deinen Code. Wenn du es skript für Skript hast, könntest du die Konsistenz zwischen Skripts verlieren, insbesondere wenn du mit einem Team von Entwicklern arbeitest.
- **Sicherheit**: Frameworks bieten Sicherheitsfunktionen, die deine Webanwendungen vor gängigen Sicherheitsbedrohungen schützen. Das bedeutet, dass du dich nicht so sehr um die Sicherheit sorgen musst, weil sich das Framework größtenteils darum kümmert.
- **Gemeinschaft**: Frameworks haben große Entwicklergemeinschaften, die zum Framework beitragen. Das bedeutet, dass du Hilfe von anderen Entwicklern erhalten kannst, wenn du Fragen oder Probleme hast. Es bedeutet auch, dass viele Ressourcen zur Verfügung stehen, um dir beim Erlernen der Verwendung des Frameworks zu helfen.
- **Beste Praktiken**: Frameworks werden unter Verwendung von bewährten Methoden erstellt. Das bedeutet, dass du vom Framework lernen und dieselben besten Praktiken in deinem eigenen Code verwenden kannst. Dies kann dir helfen, ein besserer Programmierer zu werden. Manchmal weißt du nicht, was du nicht weißt, und das kann dich am Ende einholen.
- **Erweiterbarkeit**: Frameworks sind darauf ausgelegt, erweitert zu werden. Das bedeutet, dass du eigene Funktionen zum Framework hinzufügen kannst. Dies ermöglicht es dir, Webanwendungen zu erstellen, die auf deine spezifischen Bedürfnisse zugeschnitten sind.

Flight ist ein Mikro-Framework. Das bedeutet, es ist klein und leichtgewichtig. Es bietet nicht so viel Funktionalität wie größere Frameworks wie Laravel oder Symfony. Es bietet jedoch einen Großteil der Funktionalität, die du benötigst, um Webanwendungen zu erstellen. Es ist auch einfach zu erlernen und zu verwenden. Dies macht es zu einer guten Wahl, um Webanwendungen schnell und einfach zu erstellen. Wenn du neu in Frameworks bist, ist Flight ein großartiges Einsteiger-Framework, um zu beginnen. Es wird dir helfen, die Vorteile der Verwendung von Frameworks kennenzulernen, ohne dich mit zu viel Komplexität zu überfordern. Nachdem du etwas Erfahrung mit Flight gesammelt hast, wird es einfacher sein, auf komplexere Frameworks wie Laravel oder Symfony zu wechseln, jedoch kann Flight immer noch eine erfolgreiche robuste Anwendung erstellen.

## Was ist Routing?

Routing ist der Kern des Flight-Frameworks, aber was genau ist das? Routing ist der Prozess, eine URL zu nehmen und sie mit einer spezifischen Funktion in deinem Code abzugleichen. So kannst du deine Website basierend auf der angeforderten URL unterschiedliche Dinge tun lassen. Zum Beispiel könntest du das Profil eines Benutzers anzeigen wollen, wenn sie `/benutzer/1234` besuchen, aber eine Liste aller Benutzer anzeigen, wenn sie `/benutzer` besuchen. Dies geschieht alles über Routing.

Es könnte so funktionieren:

- Ein Benutzer geht zu deinem Browser und gibt `http://beispiel.com/benutzer/1234` ein.
- Der Server empfängt die Anfrage, betrachtet die URL und leitet sie an deinen Flight-Anwendungscod weiter.
- Angenommen, in deinem Flight-Code hast du so etwas wie `Flight::route('/benutzer/@id', [ 'BenutzerController', 'BenutzerProfilAnzeigen' ]);`. Dein Flight-Anwendungscod betrachtet die URL und erkennt, dass sie mit einer von dir definierten Route übereinstimmt, und führt dann den von dir für diese Route definierten Code aus.
- Der Flight-Router wird dann laufen und die Methode `BenutzerProfilAnzeigen($id)` in der Klasse `BenutzerController` aufrufen und gibt `1234` als das `$id` Argument in der Methode weiter.
- Der Code in deiner `BenutzerProfilAnzeigen()` Methode wird dann ausgeführt und das tun, was du ihm gesagt hast. Du könntest möglicherweise etwas HTML für die Benutzerprofilseite ausgeben oder wenn es sich um eine RESTful-API handelt, könntest du eine JSON-Antwort mit den Benutzerinformationen ausgeben.
- Flight verpackt dies in eine hübsche Schleife, generiert die Antwortheader und sendet sie an den Browser des Benutzers zurück.
- Der Benutzer ist voller Freude und gibt sich selbst eine warme Umarmung!

### Und warum ist das wichtig?

Das haben zu haben kann tatsächlich dein Leben dramatisch einfacher machen! Es könnte nur schwer zu erkennen sein. Hier sind ein paar Gründe warum:

- **Zentrales Routing**: Du kannst alle deine Routen an einem Ort behalten. Das macht es einfacher zu sehen, welche Routen du hast und was sie tun. Es macht es auch einfacher, sie zu ändern, wenn nötig.
- **Routenparameter**: Du kannst Routenparameter verwenden, um Daten an deine Routenmethoden zu übergeben. Das ist eine großartige Möglichkeit, deinen Code sauber und organisiert zu halten.
- **Routengruppen**: Du kannst Routen zusammen gruppieren. Das ist großartig, um deinen Code zu organisieren und Middleware auf eine Gruppe von Routen anzuwenden.
- **Route-Alias**: Du kannst einem Pfad eine Alias zuweisen, damit die URL später in deinem Code dynamisch generiert werden kann (z. B. wie eine Vorlage). Zum Beispiel, anstelle von `/benutzer/1234` hartcodiert in deinem Code könntest du stattdessen auf den Alias `benutzer_ansicht` verweisen und die `id` als Parameter übergeben. Das macht es wunderbar, falls du dich entscheidest, sie später in `/admin/benutzer/1234` zu ändern. Du musst nicht alle deine fest codierten URLs ändern, sondern nur die URL, die der Route zugeordnet ist.
- **Routen-Middleware**: Du kannst Middleware zu deinen Routen hinzufügen. Middleware ist äußerst leistungsstark und fügt spezifische Verhaltensweisen zu deiner Anwendung hinzu, wie die Authentifizierung, dass ein bestimmter Benutzer auf eine Route oder Gruppe von Routen zugreifen kann.

Sicher kennst du den skript für Skript Weg, eine Website zu erstellen. Du könntest eine Datei namens `index.php` haben, die eine Menge `if`-Anweisungen hat, um die URL zu überprüfen und dann eine bestimmte Funktion basierend auf der URL auszuführen. Dies ist eine Form des Routings, aber es ist nicht sehr organisiert und kann schnell außer Kontrolle geraten. Flight's Routingsystem ist eine viel organisiertere und leistungsstärkere Möglichkeit, um das Routing zu handhaben.

Dies?

```php

// /benutzer/benutzerprofil_ansehen.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	benutzerprofilansehen($id);
}

// /benutzer/benutzerprofil_bearbeiten.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	benutzerprofilebearbeiten($id);
}

// etc...
```

Oder dies?

```php

// index.php
Flight::route('/benutzer/@id', [ 'BenutzerController', 'BenutzerProfilAnzeigen' ]);
Flight::route('/benutzer/@id/bearbeiten', [ 'BenutzerController', 'BenutzerProfilBearbeiten' ]);

// Vielleicht in deiner app/controllers/BenutzerController.php
class BenutzerController {
	public function BenutzerProfilAnzeigen($id) {
		// mache etwas
	}

	public function BenutzerProfilBearbeiten($id) {
		// mache etwas
	}
}
```

Hoffentlich kannst du langsam die Vorteile der Verwendung eines zentralen Routingsystems erkennen. Es ist viel einfacher zu verwalten und zu verstehen auf lange Sicht!

## Anfragen und Antworten

Flight bietet eine einfache und unkomplizierte Methode, um Anfragen und Antworten zu bearbeiten. Dies ist der Kern dessen, was ein Webframework macht. Es nimmt eine Anforderung eines Benutzers aus dem Browser entgegen, verarbeitet sie und sendet dann eine Antwort zurück. Damit kannst du Webanwendungen erstellen, die Dinge wie das Anzeigen eines Benutzerprofils, das Einloggen eines Benutzers oder das Erstellen eines neuen Blogbeitrags ermöglichen.

### Anfragen

Eine Anfrage ist das, was ein Benutzerbrowser an deinen Server sendet, wenn er deine Website besucht. Diese Anfrage enthält Informationen darüber, was der Benutzer tun möchte. Zum Beispiel kann sie Informationen darüber enthalten, welche URL der Benutzer besuchen möchte, welche Daten der Benutzer an deinen Server senden möchte oder welche Art von Daten der Benutzer von deinem Server erhalten möchte. Es ist wichtig zu wissen, dass eine Anfrage schreibgeschützt ist. Du kannst die Anfrage nicht ändern, aber du kannst sie lesen.

Flight bietet eine einfache Möglichkeit, Informationen über die Anfrage abzurufen. Du kannst Informationen über die Anfrage mithilfe der `Flight::request()`-Methode abrufen. Diese Methode gibt ein `Request`-Objekt zurück, das Informationen über die Anfrage enthält. Du kannst dieses Objekt verwenden, um Informationen über die Anfrage abzurufen, wie die URL, die Methode oder die Daten, die der Benutzer an deinen Server gesendet hat.

### Antworten

Eine Antwort ist das, was dein Server an den Browser eines Benutzers zurücksendet, wenn er deine Website besucht. Diese Antwort enthält Informationen darüber, was dein Server tun möchte. Zum Beispiel kann sie Informationen darüber enthalten, welche Art von Daten dein Server an den Benutzer senden möchte, welche Art von Daten dein Server vom Benutzer erhalten möchte oder welche Art von Daten dein Server auf dem Computer des Benutzers speichern möchte.

Flight bietet eine einfache Möglichkeit, eine Antwort an den Browser eines Benutzers zu senden. Du kannst eine Antwort mit der `Flight::response()`-Methode senden. Diese Methode nimmt ein `Response`-Objekt als Argument und sendet die Antwort an den Browser des Benutzers. Du kannst dieses Objekt verwenden, um eine Antwort an den Browser des Benutzers zu senden, wie HTML, JSON oder eine Datei. Flight hilft dir dabei, einige Teile der Antwort automatisch zu generieren, um es leicht zu machen, aber letztendlich hast du die Kontrolle darüber, was du dem Benutzer zurücksendest.