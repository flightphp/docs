## Warum ein Framework?

Einige Programmierer sind vehement gegen die Verwendung von Frameworks. Sie argumentieren, dass Frameworks aufgebläht, langsam und schwer zu erlernen sind. Sie sagen, dass Frameworks unnötig sind und dass man besseren Code ohne sie schreiben kann. Es gibt sicherlich einige gültige Punkte, die über die Nachteile der Verwendung von Frameworks gemacht werden können. Es gibt jedoch auch viele Vorteile bei der Verwendung von Frameworks.

## Gründe für die Verwendung eines Frameworks

Hier sind ein paar Gründe, warum Sie in Betracht ziehen sollten, ein Framework zu verwenden:

- **Schnelle Entwicklung**: Frameworks bieten von Anfang an viele Funktionen. Das bedeutet, dass Sie Webanwendungen schneller erstellen können. Sie müssen nicht so viel Code schreiben, da das Framework einen Großteil der Funktionen bereitstellt, die Sie benötigen.
- **Konsistenz**: Frameworks bieten eine konsistente Möglichkeit, Dinge zu erledigen. Dies erleichtert es Ihnen zu verstehen, wie der Code funktioniert, und erleichtert es anderen Entwicklern, Ihren Code zu verstehen. Wenn Sie es script für script haben, könnten Sie die Konsistenz zwischen den Skripts verlieren, insbesondere wenn Sie mit einem Team von Entwicklern zusammenarbeiten.
- **Sicherheit**: Frameworks bieten Sicherheitsfunktionen, die Ihre Webanwendungen vor häufigen Sicherheitsbedrohungen schützen. Das bedeutet, dass Sie sich nicht so sehr um die Sicherheit kümmern müssen, da das Framework einen Großteil davon für Sie erledigt.
- **Community**: Frameworks haben große Entwicklergemeinschaften, die zum Framework beitragen. Das bedeutet, dass Sie Hilfe von anderen Entwicklern bekommen, wenn Sie Fragen oder Probleme haben. Es bedeutet auch, dass es viele Ressourcen gibt, die Ihnen helfen zu lernen, wie man das Framework benutzt.
- **Beste Praktiken**: Frameworks werden nach bewährten Praktiken erstellt. Das bedeutet, dass Sie vom Framework lernen und dieselben bewährten Praktiken in Ihrem eigenen Code anwenden können. Dies kann Ihnen helfen, ein besserer Programmierer zu werden. Manchmal wissen Sie nicht, was Sie nicht wissen, und das kann Ihnen am Ende schaden.
- **Erweiterbarkeit**: Frameworks sind darauf ausgelegt, erweitert zu werden. Das bedeutet, dass Sie Ihre eigene Funktionalität zum Framework hinzufügen können. Dies ermöglicht es Ihnen, Webanwendungen zu erstellen, die auf Ihre spezifischen Bedürfnisse zugeschnitten sind.

Flight ist ein Micro-Framework. Das bedeutet, dass es klein und leichtgewichtig ist. Es bietet nicht so viele Funktionen wie größere Frameworks wie Laravel oder Symfony. Es bietet jedoch viele der Funktionen, die Sie benötigen, um Webanwendungen zu erstellen. Es ist auch einfach zu erlernen und zu verwenden. Dies macht es zu einer guten Wahl für den schnellen und einfachen Aufbau von Webanwendungen. Wenn Sie neu in der Verwendung von Frameworks sind, ist Flight ein großartiges Einsteiger-Framework, um damit zu beginnen. Es wird Ihnen helfen, die Vorteile der Verwendung von Frameworks kennenzulernen, ohne Sie mit zu viel Komplexität zu überfordern. Nachdem Sie etwas Erfahrung mit Flight gesammelt haben, wird es einfacher sein, auf komplexere Frameworks wie Laravel oder Symfony umzusteigen. Allerdings kann Flight immer noch eine erfolgreiche robuste Anwendung erstellen.

## Was ist Routenführung?

Die Routenführung ist der Kern des Flight-Frameworks, aber was genau ist das? Routenführung ist der Prozess, bei dem eine URL genommen und einer spezifischen Funktion in Ihrem Code zugeordnet wird. So können Sie Ihre Website basierend auf der angeforderten URL verschiedene Dinge tun lassen. Zum Beispiel möchten Sie eventuell das Profil eines Benutzers anzeigen, wenn er `/user/1234` besucht, aber eine Liste aller Benutzer anzeigen, wenn er `/users` besucht. All das geschieht durch Routenführung.

Es könnte so funktionieren:

- Ein Benutzer geht zu Ihrem Browser und gibt `http://example.com/user/1234` ein.
- Der Server empfängt die Anfrage, betrachtet die URL und leitet sie an Ihren Flight-Anwendungscode weiter.
- Angenommen, in Ihrem Flight-Code haben Sie etwas wie `Flight::route('/user/@id', [ 'UserController', 'viewUserProfile' ]);`. Ihr Flight-Anwendungscode betrachtet die URL, sieht, dass sie mit einer von Ihnen definierten Route übereinstimmt, und führt dann den für diese Route definierten Code aus.
- Der Flight-Router ruft dann die Methode `viewUserProfile($id)` in der Klasse `UserController` auf und übergibt die `1234` als Argument an `$id`.
- Der Code in Ihrer Methode `viewUserProfile()` wird dann ausgeführt und macht, was auch immer Sie ihm gesagt haben. Sie könnten z. B. etwas HTML für die Benutzerprofilseite ausgeben oder wenn es sich um eine RESTful-API handelt, könnten Sie eine JSON-Antwort mit den Benutzerinformationen ausgeben.
- Flight verpackt dies in ein hübsches Paket, generiert die Antwortheader und sendet sie zurück an den Browser des Benutzers.
- Der Benutzer ist voller Freude und gibt sich selbst eine warme Umarmung!

### Und warum ist das wichtig?

Ein ordnungsgemäß zentralisiertes Routenführungssystem kann tatsächlich Ihr Leben dramatisch erleichtern! Es könnte am Anfang schwer zu erkennen sein. Hier sind ein paar Gründe warum:

- **Zentrale Routen**: Sie können alle Ihre Routen an einem Ort halten. Das macht es einfacher zu sehen, welche Routen Sie haben und was sie tun. Es erleichtert auch deren Änderung, wenn nötig.
- **Routenparameter**: Sie können Routenparameter verwenden, um Daten an Ihre Routenmethoden zu übergeben. Dies ist eine großartige Möglichkeit, Ihren Code sauber und organisiert zu halten.
- **Routengruppierung**: Sie können Routen zusammen gruppieren. Das ist großartig, um Ihren Code zu organisieren und [Middleware](middleware) auf eine Gruppe von Routen anzuwenden.
- **Routenaliasing**: Sie können einer Route einen Alias zuweisen, sodass die URL später in Ihrem Code dynamisch generiert werden kann (zum Beispiel in einer Vorlage). Zum Beispiel könnten Sie statt `/user/1234` im Code festcodiert den Alias `user_view` referenzieren und die `id` als Parameter übergeben. Dies ist wunderbar, falls Sie später entscheiden, sie in `/admin/user/1234` zu ändern. Sie müssen nicht alle Ihre festcodierten URLs ändern, nur die URL, die der Route zugeordnet ist.
- **Routen-Middleware**: Sie können Middleware zu Ihren Routen hinzufügen. Middleware ist unglaublich leistungsfähig, um bestimmte Verhaltensweisen Ihrer Anwendung hinzuzufügen, wie zum Beispiel die Authentifizierung, dass ein bestimmter Benutzer auf eine Route oder eine Gruppe von Routen zugreifen kann.

Sicherlich kennen Sie die methode-artige Art und Weise, eine Website zu erstellen. Sie könnten eine Datei namens `index.php` haben, die eine Menge von `if`-Anweisungen hat, um die URL zu überprüfen und dann basierend auf der URL eine spezifische Funktion auszuführen. Dies ist eine Form von Routenführung, aber sie ist nicht sehr organisiert und kann schnell außer Kontrolle geraten. Das Routenführungssystem von Flight ist eine viel organisierte und leistungsstarke Methode, um die Routen zu handhaben.

Dies?

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

// usw...
```

Oder dies?

```php

// index.php
Flight::route('/user/@id', [ 'UserController', 'viewUserProfile' ]);
Flight::route('/user/@id/edit', [ 'UserController', 'editUserProfile' ]);

// Vielleicht in Ihrer app/controllers/UserController.php
class UserController {
	public function viewUserProfile($id) {
		// Tu etwas
	}

	public function editUserProfile($id) {
		// Tu etwas
	}
}
```

Hoffentlich können Sie langsam die Vorteile der Verwendung eines zentralisierten Routenführungssystems erkennen. Es ist viel einfacher zu verwalten und zu verstehen auf lange Sicht!

## Anfragen und Antworten

Flight bietet eine einfache und bequeme Möglichkeit, Anfragen und Antworten zu handhaben. Dies ist der Kern dessen, was ein Web-Framework tut. Es nimmt eine Anfrage von einem Benutzerbrowser entgegen, verarbeitet sie und sendet dann eine Antwort zurück. Auf diese Weise können Sie Webanwendungen erstellen, die Dinge wie das Anzeigen eines Benutzerprofils, das Anmelden eines Benutzers oder das Posten eines neuen Blogbeitrags ermöglichen.

### Anfragen

Eine Anfrage ist das, was der Browser eines Benutzers an Ihren Server sendet, wenn er Ihre Website besucht. Diese Anfrage enthält Informationen darüber, was der Benutzer tun möchte. Zum Beispiel könnte sie Informationen darüber enthalten, welche URL der Benutzer besuchen möchte, welche Daten der Benutzer an Ihren Server senden möchte oder welche Art von Daten der Benutzer von Ihrem Server erhalten möchte. Es ist wichtig zu wissen, dass eine Anfrage schreibgeschützt ist. Sie können die Anfrage nicht ändern, aber Sie können daraus lesen.

Flight bietet einen einfachen Weg, um Informationen über die Anfrage abzurufen. Sie können Informationen über die Anfrage mit der Methode `Flight::request()` abrufen. Diese Methode gibt ein `Request`-Objekt zurück, das Informationen über die Anfrage enthält. Sie können dieses Objekt verwenden, um Informationen über die Anfrage abzurufen, wie die URL, die Methode oder die Daten, die der Benutzer an Ihren Server gesendet hat.

### Antworten

Eine Antwort ist das, was Ihr Server dem Browser eines Benutzers zurücksendet, wenn er Ihre Website besucht. Diese Antwort enthält Informationen darüber, was Ihr Server tun möchte. Zum Beispiel könnte sie Informationen darüber enthalten, welche Art von Daten Ihr Server dem Benutzer senden möchte, welche Art von Daten Ihr Server vom Benutzer erhalten möchte oder welche Art von Daten Ihr Server auf dem Computer des Benutzers speichern möchte.

Flight bietet eine einfache Möglichkeit, eine Antwort an den Browser eines Benutzers zu senden. Sie können eine Antwort mit der Methode `Flight::response()` senden. Diese Methode nimmt ein `Response`-Objekt als Argument und sendet die Antwort an den Browser des Benutzers. Sie können dieses Objekt verwenden, um eine Antwort an den Browser des Benutzers zu senden, wie HTML, JSON oder eine Datei. Flight hilft Ihnen, einige Teile der Antwort automatisch zu generieren, um die Dinge einfach zu machen, aber letztendlich haben Sie die Kontrolle darüber, was Sie dem Benutzer zurücksenden.