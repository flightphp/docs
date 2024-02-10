# Warum ein Framework?

Einige Programmierer sind vehement gegen die Verwendung von Frameworks. Sie argumentieren, dass Frameworks überladen, langsam und schwer zu erlernen sind. Sie sagen, dass Frameworks unnötig sind und dass man besseren Code ohne sie schreiben kann. Es gibt sicherlich einige gültige Punkte bezüglich der Nachteile der Verwendung von Frameworks. Allerdings gibt es auch viele Vorteile bei der Verwendung von Frameworks.

## Gründe für die Verwendung eines Frameworks

Hier sind ein paar Gründe, warum Sie möglicherweise in Betracht ziehen möchten, ein Framework zu verwenden:

- **Schnelle Entwicklung**: Frameworks bieten von Grund auf viele Funktionen. Das bedeutet, dass Sie Webanwendungen schneller erstellen können. Sie müssen nicht so viel Code schreiben, da das Framework viele der Funktionen bereitstellt, die Sie benötigen.
- **Konsistenz**: Frameworks bieten eine konsistente Möglichkeit, Dinge zu erledigen. Dies erleichtert es Ihnen, zu verstehen, wie der Code funktioniert, und erleichtert es anderen Entwicklern, Ihren Code zu verstehen. Wenn Sie es von Skript zu Skript machen, könnten Sie die Konsistenz zwischen den Skripts verlieren, insbesondere wenn Sie mit einem Team von Entwicklern arbeiten.
- **Sicherheit**: Frameworks bieten Sicherheitsfunktionen, die Ihre Webanwendungen vor gängigen Sicherheitsbedrohungen schützen. Das bedeutet, dass Sie sich nicht so viele Sorgen um die Sicherheit machen müssen, da das Framework einen Großteil davon für Sie erledigt.
- **Gemeinschaft**: Frameworks haben große Entwicklergemeinschaften, die zum Framework beitragen. Das bedeutet, dass Sie Hilfe von anderen Entwicklern bekommen können, wenn Sie Fragen oder Probleme haben. Es bedeutet auch, dass es viele Ressourcen gibt, die Ihnen helfen können, zu lernen, wie man das Framework verwendet.
- **Best Practices**: Frameworks sind nach bewährten Praktiken aufgebaut. Das bedeutet, dass Sie vom Framework lernen und dieselben bewährten Praktiken in Ihrem eigenen Code verwenden können. Dies kann Ihnen helfen, ein besserer Programmierer zu werden. Manchmal wissen Sie nicht, was Sie nicht wissen und das kann Sie am Ende einholen.
- **Erweiterbarkeit**: Frameworks sind darauf ausgelegt, erweitert zu werden. Das bedeutet, dass Sie Ihre eigene Funktionalität zum Framework hinzufügen können. Dies ermöglicht es Ihnen, Webanwendungen zu erstellen, die auf Ihre spezifischen Bedürfnisse zugeschnitten sind.

Flight ist ein Mikro-Framework. Das bedeutet, dass es klein und leicht ist. Es bietet nicht so viele Funktionen wie größere Frameworks wie Laravel oder Symfony. Es bietet jedoch viele der Funktionen, die Sie benötigen, um Webanwendungen zu erstellen. Es ist auch einfach zu lernen und zu verwenden. Dies macht es zu einer guten Wahl für den schnellen und einfachen Aufbau von Webanwendungen. Wenn Sie neu in der Verwendung von Frameworks sind, ist Flight ein großartiges Anfänger-Framework, mit dem Sie beginnen können. Es wird Ihnen helfen, die Vorteile der Verwendung von Frameworks kennenzulernen, ohne Sie mit zu viel Komplexität zu überfordern. Nachdem Sie etwas Erfahrung mit Flight gesammelt haben, wird es einfacher sein, auf komplexere Frameworks wie Laravel oder Symfony zu wechseln. Allerdings kann Flight immer noch eine erfolgreiche robuste Anwendung erstellen.

## Was ist Routing?

Routing ist der Kern des Flight-Frameworks, aber was ist es genau? Routing ist der Prozess, eine URL zu nehmen und sie mit einer bestimmten Funktion in Ihrem Code abzugleichen. So können Sie Ihre Website basierend auf der angeforderten URL unterschiedliche Dinge tun lassen. Zum Beispiel möchten Sie möglicherweise das Profil eines Benutzers anzeigen, wenn er `/benutzer/1234` besucht, oder eine Liste aller Benutzer anzeigen, wenn er `/benutzer` besucht. All dies geschieht über Routing.

Es könnte so funktionieren:

- Ein Benutzer geht zu Ihrem Browser und gibt `http://beispiel.com/benutzer/1234` ein.
- Der Server empfängt die Anfrage, betrachtet die URL und übergibt sie Ihrem Flight-Anwendungscode.
- Angenommen in Ihrem Flight-Code haben Sie so etwas wie `Flight::route('/benutzer/@id', [ 'BenutzerController', 'BenutzerprofilAnzeigen' ]);`. Ihr Flight-Anwendungscode betrachtet die URL und sieht, dass sie mit einer von Ihnen definierten Route übereinstimmt, und führt dann den Code aus, den Sie für diese Route definiert haben.
- Der Flight-Router ruft dann die Methode `BenutzerprofilAnzeigen($id)` in der Klasse `BenutzerController` auf und übergibt die `1234` als Argument `$id` an die Methode.
- Der Code in Ihrer `BenutzerprofilAnzeigen()`-Methode wird dann ausgeführt und das tun, was Sie ihm gesagt haben. Möglicherweise geben Sie HTML für die Benutzerprofilseite aus oder wenn dies eine RESTful-API ist, geben Sie möglicherweise eine JSON-Antwort mit den Benutzerinformationen aus.
- Flight verpackt dies in einer hübschen Schleife, generiert die Antwortheader und sendet sie an den Browser des Benutzers zurück.
- Der Benutzer ist voller Freude und gibt sich selbst eine warme Umarmung!

### Und warum ist das wichtig?

Ein ordnungsgemäß zentralisierter Router kann Ihr Leben tatsächlich erheblich erleichtern! Es könnte nur schwer zu erkennen sein, wenn Sie zuerst darauf schauen. Hier sind ein paar Gründe, warum:

- **Zentrales Routing**: Sie können alle Ihre Routen an einem Ort aufbewahren. Dies erleichtert es zu sehen, welche Routen Sie haben und was sie tun. Es erleichtert auch deren Änderung, wenn dies erforderlich ist.
- **Routenparameter**: Sie können Routenparameter verwenden, um Daten an Ihre Routenmethoden zu übergeben. Dies ist eine großartige Möglichkeit, Ihren Code sauber und organisiert zu halten.
- **Routengruppen**: Sie können Routen gruppieren. Dies ist großartig, um Ihren Code organisiert zu halten und um [Middleware](middleware) auf eine Gruppe von Routen anzuwenden.
- **Routenalias**: Sie können einer Route einen Alias zuweisen, damit die URL später in Ihrem Code dynamisch generiert werden kann (wie z. B. eine Vorlage). Beispielsweise könnten Sie statt `/benutzer/1234` im Code festcodiert zu haben, den Alias `benutzer_anzeigen` referenzieren und die `id` als Parameter übergeben. Dies ist wunderbar, falls Sie sich entscheiden, sie später in `/admin/benutzer/1234` zu ändern. Sie müssen nicht alle Ihre festcodierten URLs ändern, sondern nur die URL, die der Route zugeordnet ist.
- **Routen-Middleware**: Sie können Middleware zu Ihren Routen hinzufügen. Middleware ist unglaublich mächtig, um spezifisches Verhalten Ihrer Anwendung hinzuzufügen, wie z. B. die Authentifizierung, dass ein bestimmter Benutzer auf eine Route oder Gruppe von Routen zugreifen kann.

Ich bin sicher, dass Ihnen der Skript-für-Skript-Weg, eine Website zu erstellen, vertraut ist. Sie könnten eine Datei namens `index.php` haben, die eine Menge `if`-Anweisungen enthält, um die URL zu überprüfen und dann basierend auf der URL eine bestimmte Funktion auszuführen. Dies ist eine Form des Routings, aber es ist nicht sehr organisiert und kann schnell außer Kontrolle geraten. Das Routing-System von Flight ist eine viel organisiertere und leistungsstärkere Methode, um das Routing zu handhaben.

Das?

```php

// /benutzer/profil_anzeigen.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	BenutzerprofilAnzeigen($id);
}

// /benutzer/profil_bearbeiten.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	BenutzerprofilBearbeiten($id);
}

// usw...
```

Oder das?

```php

// index.php
Flight::route('/benutzer/@id', [ 'BenutzerController', 'BenutzerprofilAnzeigen' ]);
Flight::route('/benutzer/@id/bearbeiten', [ 'BenutzerController', 'BenutzerprofilBearbeiten' ]);

// Vielleicht in Ihrem app/controllers/BenutzerController.php
class BenutzerController {
	public function BenutzerprofilAnzeigen($id) {
		// etwas tun
	}

	public function BenutzerprofilBearbeiten($id) {
		// etwas tun
	}
}
```

Hoffentlich können Sie langsam beginnen, die Vorteile der Verwendung eines zentralisierten Routing-Systems zu erkennen. Es ist viel einfacher zu verwalten und zu verstehen auf lange Sicht!

## Anfragen und Antworten

Flight bietet eine einfache und effektive Möglichkeit, Anfragen und Antworten zu handhaben. Dies ist der Kern dessen, was ein Web-Framework tut. Es nimmt eine Anfrage vom Browser eines Benutzers entgegen, verarbeitet sie und sendet dann eine Antwort zurück. So können Sie Webanwendungen erstellen, die Dinge wie das Anzeigen des Profils eines Benutzers, das Einloggen eines Benutzers oder das Verfassen eines neuen Blog-Beitrags ermöglichen.

### Anfragen

Eine Anfrage ist das, was der Browser eines Benutzers an Ihren Server sendet, wenn er Ihre Website besucht. Diese Anfrage enthält Informationen darüber, was der Benutzer tun möchte. Sie könnte beispielsweise Informationen darüber enthalten, welche URL der Benutzer besuchen möchte, welche Daten der Benutzer an Ihren Server senden möchte oder welche Art von Daten der Benutzer von Ihrem Server erhalten möchte. Es ist wichtig zu wissen, dass eine Anfrage schreibgeschützt ist. Sie können die Anfrage nicht ändern, aber Sie können daraus lesen.

Flight bietet einen einfachen Weg, um Informationen über die Anfrage abzurufen. Sie können Informationen über die Anfrage mithilfe der `Flight::request()`-Methode abrufen. Diese Methode gibt ein `Request`-Objekt zurück, das Informationen über die Anfrage enthält. Sie können dieses Objekt verwenden, um Informationen über die Anfrage abzurufen, wie z. B. die URL, die Methode oder die Daten, die der Benutzer an Ihren Server gesendet hat.

### Antworten

Eine Antwort ist das, was Ihr Server zurück an den Browser des Benutzers sendet, wenn er Ihre Website besucht. Diese Antwort enthält Informationen darüber, was Ihr Server tun möchte. Sie könnte beispielsweise Informationen darüber enthalten, welche Art von Daten Ihr Server an den Benutzer senden möchte, welche Art von Daten Ihr Server vom Benutzer erhalten möchte oder welche Art von Daten Ihr Server auf dem Computer des Benutzers speichern möchte.

Flight bietet einen einfachen Weg, um eine Antwort an den Browser eines Benutzers zu senden. Sie können eine Antwort mit der `Flight::response()`-Methode senden. Diese Methode akzeptiert ein `Response`-Objekt als Argument und sendet die Antwort an den Browser des Benutzers. Sie können dieses Objekt verwenden, um eine Antwort an den Browser des Benutzers zu senden, wie z. B. HTML, JSON oder eine Datei. Flight hilft Ihnen dabei, einige Teile der Antwort automatisch zu generieren, um die Dinge zu vereinfachen, aber letztendlich haben Sie die Kontrolle darüber, was Sie dem Benutzer zurückschicken.