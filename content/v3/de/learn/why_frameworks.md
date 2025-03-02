# Warum ein Framework?

Einige Programmierer sind vehement gegen die Verwendung von Frameworks. Sie argumentieren, dass Frameworks aufgebläht, langsam und schwer zu erlernen sind. 
Sie sagen, dass Frameworks unnötig sind und dass man besseren Code ohne sie schreiben kann. 
Es gibt sicherlich einige überzeugende Argumente gegen die Verwendung von Frameworks vorzubringen. Allerdings gibt es auch viele Vorteile bei der Verwendung von Frameworks. 

## Gründe für die Verwendung eines Frameworks

Hier sind ein paar Gründe, warum Sie in Betracht ziehen sollten, ein Framework zu verwenden:

- **Schnelle Entwicklung**: Frameworks bieten von Haus aus viel Funktionalität. Das bedeutet, dass Sie Webanwendungen schneller erstellen können. Sie müssen nicht so viel Code schreiben, da das Framework einen Großteil der Funktionalität bereitstellt, die Sie benötigen.
- **Konsistenz**: Frameworks bieten eine konsistente Möglichkeit der Durchführung von Aufgaben. Dies erleichtert es Ihnen zu verstehen, wie der Code funktioniert, und anderen Entwicklern, Ihren Code zu verstehen. Wenn Sie es Script für Script haben, könnte die Konsistenz zwischen den Skripts verloren gehen, besonders wenn Sie mit einem Team von Entwicklern arbeiten.
- **Sicherheit**: Frameworks bieten Sicherheitsfunktionen, die Ihre Webanwendungen vor gängigen Sicherheitsbedrohungen schützen. Das bedeutet, dass Sie sich nicht so sehr um die Sicherheit kümmern müssen, da das Framework einen Großteil davon für Sie erledigt.
- **Gemeinschaft**: Frameworks haben große Entwickler-Communities, die zum Framework beitragen. Das bedeutet, dass Sie Hilfe von anderen Entwicklern erhalten können, wenn Sie Fragen oder Probleme haben. Es bedeutet auch, dass es viele Ressourcen gibt, die Ihnen helfen können, zu lernen, wie das Framework verwendet wird.
- **Beste Praktiken**: Frameworks werden unter Verwendung von besten Praktiken erstellt. Das bedeutet, dass Sie vom Framework lernen und die gleichen besten Praktiken in Ihrem eigenen Code verwenden können. Dies kann Ihnen helfen, ein besserer Programmierer zu werden. Manchmal weiß man nicht, was man nicht weiß, und das kann einem am Ende schaden.
- **Erweiterbarkeit**: Frameworks sind darauf ausgelegt, erweitert zu werden. Das bedeutet, dass Sie Ihre eigene Funktionalität zum Framework hinzufügen können. Dies ermöglicht es Ihnen, Webanwendungen zu erstellen, die auf Ihre spezifischen Bedürfnisse zugeschnitten sind.

Flight ist ein Mikro-Framework. Das bedeutet, dass es klein und leichtgewichtig ist. Es bietet nicht so viele Funktionen wie größere Frameworks wie Laravel oder Symfony. 
Allerdings bietet es viele der Funktionen, die Sie benötigen, um Webanwendungen zu erstellen. Es ist auch einfach zu erlernen und zu verwenden. 
Das macht es zu einer guten Wahl, um Webanwendungen schnell und einfach zu erstellen. Wenn Sie neu in der Welt der Frameworks sind, ist Flight ein großartiges Anfänger-Framework, mit dem Sie beginnen können. 
Es hilft Ihnen, die Vorteile der Verwendung von Frameworks kennenzulernen, ohne Sie mit zu viel Komplexität zu überfordern. 
Nachdem Sie etwas Erfahrung mit Flight gesammelt haben, wird es einfacher sein, auf komplexere Frameworks wie Laravel oder Symfony umzusteigen, 
Flight kann jedoch immer noch eine erfolgreiche robuste Anwendung ermöglichen.

## Was ist Routing?

Routing ist der Kern des Flight Frameworks, aber was genau ist das? Routing ist der Prozess, bei dem eine URL genommen und mit einer bestimmten Funktion in Ihrem Code abgeglichen wird. 
So können Sie Ihre Website basierend auf der angeforderten URL unterschiedliche Dinge tun lassen. Zum Beispiel möchten Sie möglicherweise das Profil eines Benutzers anzeigen, wenn er 
`/user/1234` besucht, aber eine Liste aller Benutzer anzeigen, wenn er `/users` besucht. All dies geschieht durch Routing.

Es könnte etwa so funktionieren:

- Ein Benutzer geht in Ihren Browser und gibt `http://beispiel.com/user/1234` ein.
- Der Server empfängt die Anfrage, betrachtet die URL und leitet sie an Ihren Flight-Anwendungscode weiter.
- Angenommen, in Ihrem Flight-Code haben Sie so etwas wie `Flight::route('/user/@id', [ 'BenutzerController', 'BenutzerprofilAnzeigen' ]);`. Ihr Flight-Anwendungscode betrachtet die URL und erkennt, dass sie mit einem von Ihnen definierten Routen übereinstimmt, und führt dann den für diese Route definierten Code aus.  
- Der Flight-Router wird dann den `BenutzerprofilAnzeigen($id)`-Methode in der `BenutzerController`-Klasse aufrufen und die `1234` als `$id`-Argument an die Methode übergeben.
- Der Code in Ihrer `BenutzerprofilAnzeigen()`-Methode wird dann ausgeführt und das tun, was Sie ihm gesagt haben. Sie können z. B. HTML für die Profilseite des Benutzers ausgeben oder wenn es sich um eine RESTful API handelt, können Sie eine JSON-Antwort mit den Benutzerinformationen ausgeben.
- Flight fasst das Ganze in eine hübsche Schleife, generiert die Antwortheader und sendet sie an den Browser des Benutzers zurück.
- Der Benutzer ist erfüllt und verteilt sich selbst eine herzliche Umarmung!

### Und warum ist das wichtig?

Eine ordnungsgemäß zentralisierte Routerung kann tatsächlich Ihr Leben dramatisch vereinfachen! Es kann nur anfangs schwer zu erkennen sein. Hier sind ein paar Gründe:

- **Zentralisierte Routerung**: Sie können alle Ihre Routen an einem Ort aufbewahren. Dies erleichtert es Ihnen zu sehen, welche Routen Sie haben und was sie tun. Es erleichtert auch das Ändern, wenn Sie müssen.
- **Routenparameter**: Sie können Routenparameter verwenden, um Daten an Ihre Routenmethoden zu übergeben. Dies ist eine großartige Möglichkeit, Ihren Code sauber und organisiert zu halten.
- **Routengruppen**: Sie können Routen zusammenfassen. Dies ist großartig, um Ihren Code organisiert zu halten und [Middleware](middleware) auf eine Gruppe von Routen anzuwenden.
- **Routenaliasing**: Sie können einer Route einen Alias zuweisen, damit die URL später in Ihrem Code dynamisch generiert werden kann (z. B. wie eine Vorlage). Zum Beispiel, anstatt `/user/1234` fest im Code zu codieren, könnten Sie stattdessen auf den Alias `user_view` verweisen und die `id` als Parameter übergeben. Das ist großartig, falls Sie sich später dazu entscheiden, sie auf `/admin/user/1234` zu ändern. Sie müssen nicht alle fest codierten URLs ändern, sondern nur die URL, die der Route zugeordnet ist.
- **Routen-Middleware**: Sie können Middleware zu Ihren Routen hinzufügen. Middleware ist unglaublich stark darin, spezifische Verhaltensweisen zu Ihrer Anwendung hinzuzufügen, wie z. B. die Authentifizierung, dass ein bestimmter Benutzer auf eine Route oder eine Gruppe von Routen zugreifen kann.

Sie sind sicherlich vertraut mit dem Script für Script-Weg, eine Website zu erstellen. Sie könnten eine Datei namens `index.php` haben, die eine Reihe von `if`-Anweisungen enthält, um die URL zu überprüfen und dann eine bestimmte Funktion auf der Grundlage der URL auszuführen. Dies ist eine Form der Routenführung, aber sie ist nicht sehr organisiert und kann schnell außer Kontrolle geraten. Flights Routensystem ist eine viel organisiertere und leistungsfähigere Art, die Routenführung zu handhaben.

Dies hier?

```php

// /user/view_profile.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	viewUserProfile($id);
}

// /user/edit_profile.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	editUserProfile($id);
}

// etc...
```

Oder das hier?

```php

// index.php
Flight::route('/user/@id', [ 'BenutzerController', 'BenutzerprofilAnzeigen' ]);
Flight::route('/user/@id/edit', [ 'BenutzerController', 'BenutzerprofilBearbeiten' ]);

// Vielleicht in Ihrem app/controllers/UserController.php
class UserController {
	public function viewUserProfile($id) {
		// Mach etwas
	}

	public function editUserProfile($id) {
		// Mach etwas
	}
}
```

Hoffentlich erkennen Sie langsam die Vorteile der Verwendung eines zentralisierten Routingsystems. Es ist viel einfacher zu verwalten und zu verstehen auf lange Sicht!

## Anfragen und Antworten

Flight bietet eine einfache und unkomplizierte Möglichkeit, Anfragen und Antworten zu bearbeiten. Dies ist der Kern dessen, was ein Web-Framework macht. Es nimmt eine Anfrage 
von einem Benutzerbrowser entgegen, verarbeitet sie und sendet dann eine Antwort zurück. So können Sie Webanwendungen erstellen, die Dinge wie das Anzeigen eines Benutzerprofils, das Einloggen eines Benutzers oder das Posten eines neuen Blogposts ermöglichen.

### Anfragen

Eine Anfrage ist das, was der Browser eines Benutzers an Ihren Server sendet, wenn er Ihre Website besucht. Diese Anfrage enthält Informationen darüber, was der Benutzer tun möchte. 
Zum Beispiel könnte sie Informationen darüber enthalten, welche URL der Benutzer besuchen möchte, welche Daten der Benutzer an Ihren Server senden möchte oder welche Art von Daten der Benutzer von Ihrem Server erhalten möchte. Es ist wichtig zu wissen, dass eine Anfrage schreibgeschützt ist. Sie können die Anfrage nicht ändern, aber Sie können daraus lesen.

Flight bietet eine einfache Möglichkeit, Informationen zur Anfrage abzurufen. Sie können über die Methode `Flight::request()` Informationen zur Anfrage abrufen. Diese Methode gibt ein `Request`-Objekt zurück, das Informationen zur Anfrage enthält. Mit diesem Objekt können Sie Informationen zur Anfrage abrufen, wie die URL, die Methode oder die Daten, die der Benutzer an Ihren Server gesendet hat.

### Antworten

Eine Antwort ist das, was Ihr Server an den Browser eines Benutzers zurücksendet, wenn er Ihre Website besucht. Diese Antwort enthält Informationen darüber, was Ihr Server tun möchte. 
Zum Beispiel könnte es Informationen darüber enthalten, welche Art von Daten Ihr Server an den Benutzer senden möchte, welche Art von Daten Ihr Server von dem Benutzer erhalten möchte oder welche Art von Daten Ihr Server auf dem Computer des Benutzers speichern möchte.

Flight bietet eine einfache Möglichkeit, eine Antwort an den Browser eines Benutzers zu senden. Sie können eine Antwort mit der Methode `Flight::response()` senden. Diese Methode nimmt ein `Response`-Objekt als Argument und sendet die Antwort an den Browser des Benutzers. 
Sie können dieses Objekt verwenden, um eine Antwort an den Browser des Benutzers zu senden, wie z. B. HTML, JSON oder eine Datei. Flight hilft Ihnen dabei, einige Teile der Antwort automatisch zu generieren, um die Dinge zu vereinfachen, aber letztendlich haben Sie die Kontrolle darüber, was Sie dem Benutzer zurücksenden.