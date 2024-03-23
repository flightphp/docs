# Sicherheit

Sicherheit ist ein wichtiges Thema, wenn es um Webanwendungen geht. Sie möchten sicherstellen, dass Ihre Anwendung sicher ist und die Daten Ihrer Benutzer geschützt sind. Flight bietet eine Reihe von Funktionen, um Ihnen bei der Sicherung Ihrer Webanwendungen zu helfen.

## Header

HTTP-Header sind eine der einfachsten Möglichkeiten, um Ihre Webanwendungen abzusichern. Sie können Header verwenden, um Clickjacking, XSS und andere Angriffe zu verhindern. Es gibt mehrere Möglichkeiten, wie Sie diese Header zu Ihrer Anwendung hinzufügen können.

Zwei großartige Websites, um die Sicherheit Ihrer Header zu überprüfen, sind [securityheaders.com](https://securityheaders.com/) und [observatory.mozilla.org](https://observatory.mozilla.org/).

### Manuell hinzufügen

Sie können diese Header manuell hinzufügen, indem Sie die `header`-Methode auf dem `Flight\Response`-Objekt verwenden.

Diese können am Anfang Ihrer `bootstrap.php` oder `index.php` Dateien hinzugefügt werden.

### Als Filter hinzufügen

Sie können sie auch in einem Filter/Hook wie folgt hinzufügen:

### Als Middleware hinzufügen

Sie können sie auch als Middleware-Klasse hinzufügen. Dies ist eine gute Möglichkeit, Ihren Code sauber und organisiert zu halten.

## Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) ist eine Art von Angriff, bei dem eine bösartige Website den Browser eines Benutzers dazu bringen kann, eine Anfrage an Ihre Website zu senden. Flight bietet keinen eingebauten CSRF-Schutzmechanismus, aber Sie können leicht Ihren eigenen implementieren, indem Sie Middleware verwenden.

### Einrichtung

Zuerst müssen Sie ein CSRF-Token generieren und es in der Sitzung des Benutzers speichern. Sie können dann dieses Token in Ihren Formularen verwenden und es beim Absenden des Formulars überprüfen.

#### Verwendung von Latte

Sie können auch eine benutzerdefinierte Funktion einstellen, um das CSRF-Token in Ihren Latte-Templates auszugeben.

Und jetzt können Sie in Ihren Latte-Templates die `csrf()`-Funktion verwenden, um das CSRF-Token auszugeben.

### Überprüfen des CSRF-Token

Sie können das CSRF-Token mithilfe von Eventfiltern überprüfen.

Oder Sie können eine Middleware-Klasse verwenden.

## Cross Site Scripting (XSS)

Cross Site Scripting (XSS) ist eine Art von Angriff, bei dem eine bösartige Website Code in Ihre Website einschleusen kann. Die meisten dieser Möglichkeiten stammen aus Formularwerten, die Ihre Endbenutzer ausfüllen. Sie sollten **niemals** Ausgaben Ihrer Benutzer vertrauen! Gehen Sie immer davon aus, dass alle von ihnen die besten Hacker der Welt sind. Sie können bösartiges JavaScript oder HTML in Ihre Seite einschleusen. Dieser Code kann verwendet werden, um Informationen von Ihren Benutzern zu stehlen oder Aktionen auf Ihrer Website auszuführen. Durch die Verwendung der View-Klasse von Flight können Sie Ausgaben einfach escapen, um XSS-Angriffe zu verhindern.

## SQL Injection

SQL Injection ist eine Art von Angriff, bei dem ein bösartiger Benutzer SQL-Code in Ihre Datenbank einschleusen kann. Dies kann verwendet werden, um Informationen aus Ihrer Datenbank zu stehlen oder Aktionen auf Ihrer Datenbank auszuführen. Wieder sollten Sie **niemals** Eingaben Ihrer Benutzer vertrauen! Gehen Sie immer davon aus, dass sie es auf Ihr Blut abgesehen haben. Sie können mit vorbereiteten Anweisungen in Ihren `PDO`-Objekten SQL-Injektionen verhindern.

## CORS

Cross-Origin Resource Sharing (CORS) ist ein Mechanismus, der es vielen Ressourcen (z. B. Schriften, JavaScript usw.) auf einer Webseite ermöglicht, von einer anderen Domain außerhalb der Domain, von der die Ressource stammt, angefordert zu werden. Flight hat keine integrierte Funktionalität, aber dies kann leicht mit Middleware oder Eventfiltern wie bei CSRF gehandhabt werden.

## Fazit

Sicherheit ist wichtig, und es ist wichtig sicherzustellen, dass Ihre Webanwendungen sicher sind. Flight bietet eine Reihe von Funktionen, um Ihnen bei der Sicherung Ihrer Webanwendungen zu helfen, aber es ist wichtig, immer wachsam zu sein und sicherzustellen, dass Sie alles tun, um die Daten Ihrer Benutzer zu schützen. Gehen Sie immer vom Schlimmsten aus und vertrauen Sie niemals den Eingaben Ihrer Benutzer. Escapen Sie immer Ausgaben und verwenden Sie vorbereitete Anweisungen, um SQL-Injektionen zu verhindern. Verwenden Sie immer Middleware, um Ihre Routen vor CSRF- und CORS-Angriffen zu schützen. Wenn Sie all dies tun, sind Sie auf dem besten Weg, sichere Webanwendungen zu erstellen.