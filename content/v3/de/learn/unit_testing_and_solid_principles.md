> _Dieser Artikel wurde ursprünglich 2015 auf [Airpair](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) veröffentlicht. Alle Credits gehen an Airpair und Brian Fenton, der den Artikel ursprünglich geschrieben hat, obwohl die Website nicht mehr verfügbar ist und der Artikel nur in der [Wayback Machine](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) existiert. Dieser Artikel wurde der Seite zu Lern- und Bildungszwecken für die PHP-Community hinzugefügt._

1 Einrichtung und Konfiguration
-----------------------------

### 1.1 Aktuell bleiben

Lassen Sie uns das von Anfang an klären – eine deprimierend kleine Anzahl von PHP-Installationen in der Praxis ist aktuell oder wird aktuell gehalten. Ob das auf Einschränkungen bei Shared-Hosting, Standardeinstellungen, die niemand ändert, oder auf fehlender Zeit/Budget für Upgradetests zurückzuführen ist, die bescheidenen PHP-Binaries werden oft zurückgelassen. Eine klare Best Practice, die mehr Betonung verdient, ist daher, immer eine aktuelle Version von PHP zu verwenden (5.6.x zum Zeitpunkt dieses Artikels). Darüber hinaus ist es wichtig, regelmäßige Upgrades sowohl von PHP selbst als auch von Erweiterungen oder Vendor-Bibliotheken durchzuführen. Upgrades bringen neue Sprachfunktionen, verbesserte Geschwindigkeit, geringeren Speicherverbrauch und Sicherheitsupdates. Je häufiger Sie upgraden, desto weniger schmerzhaft wird der Prozess.

### 1.2 Sinnvolle Standardeinstellungen

PHP macht einen anständigen Job, gute Standardeinstellungen mit seinen Dateien _php.ini.development_ und _php.ini.production_ vorzunehmen, aber wir können es besser machen. Zum einen legen sie keine Datums-/Zeitzone für uns fest. Das ergibt Sinn aus Sicht der Distribution, aber ohne eine wird PHP einen E_WARNING-Fehler auslösen, wann­ever wir eine datums-/zeitbezogene Funktion aufrufen. Hier sind einige empfohlene Einstellungen:

*   date.timezone – wählen Sie aus der [Liste der unterstützten Zeitzonen](http://php.net/manual/en/timezones.php)
*   session.save_path – wenn wir Dateien für Sessions und nicht einen anderen Save-Handler verwenden, legen Sie das auf etwas außerhalb von _/tmp_ fest. Wenn das als _/tmp_ belassen wird, kann das in einer Shared-Hosting-Umgebung riskant sein, da _/tmp_ in der Regel weit offene Berechtigungen hat. Sogar mit dem Sticky-Bit gesetzt, kann jeder mit Zugriff auf den Inhalt dieses Verzeichnisses alle aktiven Session-IDs erfahren.
*   session.cookie_secure – das ist ein No-Brainer, schalten Sie das ein, wenn Sie Ihren PHP-Code über HTTPS servieren.
*   session.cookie_httponly – stellen Sie das ein, um PHP-Session-Cookies vor dem Zugriff über JavaScript zu schützen
*   Mehr... verwenden Sie ein Tool wie [iniscan](https://github.com/psecio/iniscan), um Ihre Konfiguration auf häufige Schwachstellen zu testen

### 1.3 Erweiterungen

Es ist auch eine gute Idee, Erweiterungen zu deaktivieren (oder zumindest nicht zu aktivieren), die Sie nicht verwenden, wie Datenbank-Treiber. Um zu sehen, was aktiviert ist, führen Sie den `phpinfo()`-Befehl aus oder gehen Sie zur Kommandozeile und führen Sie das aus.

```bash
$ php -i
``` 

Die Informationen sind die gleichen, aber phpinfo() hat HTML-Formatierung hinzugefügt. Die CLI-Version ist einfacher zu pipen und mit grep zu filtern, um spezifische Informationen zu finden. Zum Beispiel:

```bash
$ php -i | grep error_log
```

Ein Haken bei dieser Methode: Es ist möglich, dass unterschiedliche PHP-Einstellungen für die webseitige Version und die CLI-Version gelten.

2 Composer verwenden
--------------

Das könnte überraschen, aber eine der besten Praktiken für modernes PHP-Schreiben ist, weniger davon zu schreiben. Obwohl es wahr ist, dass man, um gut zu programmieren, programmieren muss, gibt es eine große Anzahl von Problemen, die im PHP-Bereich bereits gelöst wurden, wie Routing, grundlegende Input-Validierungsbibliotheken, Einheitenumwandlung, Datenbank-Abstraktionsschichten usw. Schauen Sie einfach auf [Packagist](https://www.packagist.org/) und stöbern Sie herum. Sie werden wahrscheinlich feststellen, dass erhebliche Teile des Problems, das Sie lösen möchten, bereits geschrieben und getestet wurden.

Obwohl es verlockend ist, den gesamten Code selbst zu schreiben (und es ist nichts Falsches daran, Ihren eigenen Framework oder Ihre eigene Bibliothek als Lernerfahrung zu schreiben), sollten Sie gegen diese Gefühle von „Nicht von mir erfunden“ ankämpfen und sich Zeit und Kopfschmerzen sparen. Folgen Sie stattdessen der Doktrin von PIE – Proudly Invented Elsewhere. Und wenn Sie sich entscheiden, Ihr eigenes Etwas zu schreiben, veröffentlichen Sie es nicht, es sei denn, es tut etwas signifikant anderes oder Besseres als bestehende Angebote.

[Composer](https://www.getcomposer.org/) ist ein Paketmanager für PHP, ähnlich wie pip in Python, gem in Ruby und npm in Node. Es ermöglicht Ihnen, eine JSON-Datei zu definieren, die die Abhängigkeiten Ihres Codes auflistet, und es wird versuchen, diese Anforderungen zu erledigen, indem es die notwendigen Code-Bundles herunterlädt und installiert.

### 2.1 Composer installieren

Wir gehen davon aus, dass dies ein lokales Projekt ist, also installieren wir eine Instanz von Composer nur für das aktuelle Projekt. Navigieren Sie zu Ihrem Projektverzeichnis und führen Sie das aus:
```bash
$ curl -sS https://getcomposer.org/installer | php
```

Beachten Sie, dass das Pipen eines Downloads direkt in einen Skript-Interpreter (sh, ruby, php usw.) ein Sicherheitsrisiko darstellt, also lesen Sie den Installationscode und stellen Sie sicher, dass Sie damit einverstanden sind, bevor Sie einen solchen Befehl ausführen.

Aus Gründen der Bequemlichkeit (wenn Sie lieber `composer install` tippen als `php composer.phar install`), können Sie diesen Befehl verwenden, um eine einzelne Kopie von Composer global zu installieren:

```bash
$ mv composer.phar /usr/local/bin/composer
$ chmod +x composer
```

Sie müssen diese möglicherweise mit `sudo` ausführen, je nach Ihren Dateiberechtigungen.

### 2.2 Composer verwenden

Composer hat zwei Hauptkategorien von Abhängigkeiten, die es verwalten kann: „require“ und „require-dev“. Abhängigkeiten, die als „require“ aufgelistet sind, werden überall installiert, aber „require-dev“-Abhängigkeiten werden nur installiert, wenn sie explizit angefordert werden. Normalerweise handelt es sich dabei um Tools für die aktive Entwicklung, wie [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer). Die Zeile unten zeigt ein Beispiel, wie man [Guzzle](http://docs.guzzlephp.org/en/latest/) installiert, eine beliebte HTTP-Bibliothek.

```bash
$ php composer.phar require guzzle/guzzle
```

Um ein Tool nur für Entwicklungszwecke zu installieren, fügen Sie die `--dev`-Flag hinzu:

```bash
$ php composer.phar require --dev 'sebastian/phpcpd'
```

Das installiert [PHP Copy-Paste Detector](https://github.com/sebastianbergmann/phpcpd), ein weiteres Code-Qualitäts-Tool als Entwicklungs-abhängigkeit.

### 2.3 Install vs. Update

Wenn wir `composer install` das erste Mal ausführen, installiert es alle Bibliotheken und ihre Abhängigkeiten, basierend auf der _composer.json_-Datei. Wenn das erledigt ist, erstellt Composer eine Lock-Datei, passend benannt _composer.lock_. Diese Datei enthält eine Liste der Abhängigkeiten, die Composer für uns gefunden hat, und ihre genauen Versionen mit Hashes. Jedes Mal, wenn wir `composer install` in Zukunft ausführen, schaut es in die Lock-Datei und installiert genau diese Versionen.

`composer update` ist ein bisschen anders. Es ignoriert die _composer.lock_-Datei (falls vorhanden) und versucht, die neuesten Versionen jeder Abhängigkeit zu finden, die immer noch den Einschränkungen in _composer.json_ entsprechen. Es schreibt dann eine neue _composer.lock_-Datei, wenn es fertig ist.

### 2.4 Autoloading

Sowohl `composer install` als auch `composer update` generieren einen [Autoloader](https://getcomposer.org/doc/04-schema.md#autoload) für uns, der PHP sagt, wo es alle notwendigen Dateien für die Bibliotheken findet, die wir gerade installiert haben. Um ihn zu verwenden, fügen Sie einfach diese Zeile hinzu (normalerweise zu einer Bootstrap-Datei, die bei jeder Anfrage ausgeführt wird):
```php
require 'vendor/autoload.php';
```

3 Gute Designprinzipien befolgen
-------------------------------

### 3.1 SOLID

SOLID ist ein Akronym, das uns an fünf Schlüsselprinzipien im guten objektorientierten Software-Design erinnert.

#### 3.1.1 S - Single Responsibility Principle

Das besagt, dass Klassen nur eine Verantwortung haben sollten, oder anders ausgedrückt, sie sollten nur einen Grund zum Ändern haben. Das passt gut zur Unix-Philosophie von vielen kleinen Tools, die eine Sache gut machen. Klassen, die nur eine Sache tun, sind viel einfacher zu testen und zu debuggen und überraschen Sie weniger. Sie wollen nicht, dass ein Methodenaufruf zu einer Validator-Klasse DB-Datensätze aktualisiert. Hier ist ein Beispiel für eine Verletzung des SRP, wie man es in einer Anwendung basierend auf dem [ActiveRecord-Pattern](http://en.wikipedia.org/wiki/Active_record_pattern) häufig sieht.

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
    public function save() {}
}
```

Das ist ein ziemlich grundlegendes [Entity](http://lostechies.com/jimmybogard/2008/05/21/entities-value-objects-aggregates-and-roots/)-Modell. Eines dieser Dinge gehört hier nicht hin. Die einzige Verantwortung eines Entity-Modells sollte das Verhalten sein, das mit der Entität zusammenhängt, die es repräsentiert, es sollte nicht für seine eigene Persistenz verantwortlich sein.

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
}
class DataStore
{
    public function save(Model $model) {}
}
```

Das ist besser. Das Person-Modell ist wieder nur eine Sache, und das Save-Verhalten wurde zu einem Persistenz-Objekt verschoben. Beachten Sie auch, dass ich nur auf Model getippt habe, nicht auf Person. Wir kommen darauf zurück, wenn wir zu den L- und D-Teilen von SOLID kommen.

#### 3.1.2 O - Open Closed Principle

Es gibt einen tollen Test dafür, der ziemlich genau zusammenfasst, worum es bei diesem Prinzip geht: Denken Sie an eine Funktion, die Sie implementieren sollen, wahrscheinlich die neueste, an der Sie gearbeitet haben oder arbeiten. Können Sie diese Funktion in Ihrem bestehenden Codebasis SOLELY implementieren, indem Sie neue Klassen hinzufügen und keine bestehenden Klassen in Ihrem System ändern? Ihre Konfiguration und Verkabelungscode bekommt ein bisschen Nachsicht, aber in den meisten Systemen ist das überraschend schwierig. Sie müssen sich stark auf polymorphe Dispatch verlassen und die meisten Codebasen sind nicht dafür eingerichtet. Wenn Sie daran interessiert sind, gibt es einen guten Google-Talk auf YouTube über [Polymorphismus und Code-Schreiben ohne Ifs](https://www.youtube.com/watch?v=4F72VULWFvc), der das weiter ausführt. Als Bonus wird der Talk von [Miško Hevery](http://misko.hevery.com/) gehalten, den viele als den Erfinder von [AngularJs](https://angularjs.org/) kennen.

#### 3.1.3 L - Liskov Substitution Principle

Dieses Prinzip ist nach [Barbara Liskov](http://en.wikipedia.org/wiki/Barbara_Liskov) benannt und lautet wie folgt:

> „Objekte in einem Programm sollten durch Instanzen ihrer Untertypen ersetzbar sein, ohne die Korrektheit dieses Programms zu ändern.“

Das klingt alles gut und schön, aber es wird klarer illustriert mit einem Beispiel.

```php
abstract class Shape
{
    public function getHeight();
    public function setHeight($height);
    public function getLength();
    public function setLength($length);
}
```

Das wird unsere grundlegende vierseitige Form darstellen. Nichts Ausgefallenes hier.

```php
class Square extends Shape
{
    protected $size;
    public function getHeight() {
        return $this->size;
    }
    public function setHeight($height) {
        $this->size = $height;
    }
    public function getLength() {
        return $this->size;
    }
    public function setLength($length) {
        $this->size = $length;
    }
}
```

Hier ist unsere erste Form, das Quadrat. Eine ziemlich unkomplizierte Form, oder? Sie können annehmen, dass es einen Konstruktor gibt, in dem wir die Dimensionen festlegen, aber Sie sehen hier aus dieser Implementierung, dass Länge und Höhe immer gleich sein werden. Quadrate sind einfach so.

```php
class Rectangle extends Shape
{
    protected $height;
    protected $length;
    public function getHeight() {
        return $this->height;
    }
    public function setHeight($height) {
        $this->height = $height;
    }
    public function getLength() {
        return $this->length;
    }
    public function setLength($length) {
        $this->length = $length;
    }
}
```

Also haben wir hier eine andere Form. Sie hat immer noch die gleichen Methodensignaturen, es ist immer noch eine vierseitige Form, aber was, wenn wir anfangen, sie gegeneinander zu verwenden? Plötzlich, wenn wir die Höhe unserer Shape ändern, können wir nicht mehr annehmen, dass die Länge unserer Shape übereinstimmt. Wir haben den Vertrag verletzt, den wir mit dem Benutzer hatten, als wir ihm unsere Square-Form gaben.

Das ist ein Lehrbuchbeispiel für eine Verletzung des LSP, und wir brauchen ein solches Prinzip, um das Beste aus einem Typsystem zu machen. Sogar [Duck Typing](http://en.wikipedia.org/wiki/Duck_typing) wird uns nicht sagen, ob das zugrunde liegende Verhalten anders ist, und da wir das nicht wissen können, ohne dass es bricht, ist es am besten, sicherzustellen, dass es nicht anders ist.

#### 3.1.3 I - Interface Segregation Principle

Dieses Prinzip sagt, dass man vielen kleinen, feingliedrigen Interfaces den Vorzug geben sollte, im Vergleich zu einem großen. Interfaces sollten auf Verhalten basieren und nicht auf „es ist eine dieser Klassen“. Denken Sie an Interfaces, die mit PHP kommen. Traversable, Countable, Serializable, Dinge wie das. Sie werben für Fähigkeiten, die das Objekt besitzt, nicht für das, wovon es erbt. Halten Sie Ihre Interfaces also klein. Sie wollen kein Interface mit 30 Methoden darauf, 3 ist ein viel besseres Ziel.

#### 3.1.4 D - Dependency Inversion Principle

Sie haben das wahrscheinlich an anderen Stellen gehört, die über [Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection) gesprochen haben, aber Dependency Inversion und Dependency Injection sind nicht ganz dasselbe. Dependency Inversion ist wirklich nur eine Möglichkeit zu sagen, dass Sie auf Abstraktionen in Ihrem System und nicht auf seine Details angewiesen sein sollten. Was bedeutet das für Sie im Alltag?

> Verwenden Sie nicht direkt mysqli_query() überall in Ihrem Code, verwenden Sie stattdessen etwas wie DataStore->query().

Der Kern dieses Prinzips geht eigentlich um Abstraktionen. Es geht mehr darum zu sagen „verwenden Sie einen Datenbank-Adapter“, anstatt auf direkte Aufrufe wie mysqli_query zu vertrauen. Wenn Sie mysqli_query direkt in der Hälfte Ihrer Klassen verwenden, binden Sie alles direkt an Ihre Datenbank. Nichts für oder gegen MySQL hier, aber wenn Sie mysqli_query verwenden, sollte diese Art von niedrigstufigem Detail in nur einem Ort versteckt werden und dann diese Funktionalität über eine generische Wrapper freigegeben werden.

Ich weiß, das ist ein bisschen ein abgedroschener Beispiel, wenn man drüber nachdenkt, weil die Anzahl der Male, in denen Sie Ihren Datenbank-Engine vollständig ändern werden, nachdem Ihr Produkt in Produktion ist, sehr, sehr niedrig ist. Ich habe es gewählt, weil ich dachte, die Leute wären mit der Idee aus ihrem eigenen Code vertraut. Auch, selbst wenn Sie eine Datenbank haben, bei der Sie bleiben, ermöglicht Ihnen dieses abstrakte Wrapper-Objekt, Fehler zu beheben, Verhalten zu ändern oder Funktionen zu implementieren, die Sie sich von Ihrer gewählten Datenbank wünschen. Es macht auch Unit-Testing möglich, wo niedrigstufige Aufrufe das nicht tun würden.

4 Object Calisthenics
---------------------

Das ist kein voller Einstieg in diese Prinzipien, aber die ersten zwei sind leicht zu merken, bieten guten Wert und können sofort auf fast jeden Codebase angewendet werden.

### 4.1 Nicht mehr als eine Ebene der Einrückung pro Methode

Das ist eine hilfreiche Möglichkeit, Methoden in kleinere Chunks zu zerlegen, was zu Code führt, der klarer und selbstdokumentierender ist. Je mehr Ebenen der Einrückung Sie haben, desto mehr tut die Methode und desto mehr Zustand müssen Sie im Kopf behalten, während Sie damit arbeiten.

Sofort weiß ich, dass Leute dagegen einwenden werden, aber das ist nur eine Richtlinie/Heuristik, keine harte und schnelle Regel. Ich erwarte nicht, dass jemand PHP_CodeSniffer-Regeln dafür durchsetzt (obwohl [Leute das getan haben](https://github.com/object-calisthenics/phpcs-calisthenics-rules)).

Lassen Sie uns ein schnelles Beispiel durchgehen, wie das aussehen könnte:

```php
public function transformToCsv($data)
{
    $csvLines = array();
    $csvLines[] = implode(',', array_keys($data[0]));
    foreach ($data as $row) {
        if (!$row) {
            continue;
        }
        $csvLines[] = implode(',', $row);
    }
    return $csvLines;
}
```

Obwohl das technisch korrekter, testbarer usw. Code ist, können wir viel mehr tun, um das klarer zu machen. Wie reduzieren wir die Ebenen der Verschachtelung hier?

Wir wissen, dass wir den Inhalt der foreach-Schleife stark vereinfachen müssen (oder sie ganz entfernen), also beginnen wir da.

```php
if (!$row) {
    continue;
}
```

Das erste Bit ist einfach. Das ignoriert nur leere Zeilen. Wir können diesen gesamten Prozess abkürzen, indem wir eine eingebaute PHP-Funktion verwenden, bevor wir überhaupt zur Schleife kommen.

```php
$data = array_filter($data);
foreach ($data as $row) {
    $csvLines[] = implode(',', $row);
}
```

Jetzt haben wir unsere einzelne Ebene der Verschachtelung. Aber wenn man sich das ansieht, tun wir nichts anderes, als eine Funktion auf jedes Element in einem Array anzuwenden. Wir brauchen nicht einmal die foreach-Schleife dafür.

```php
$data = array_filter($data);
$csvLines = array_map(function($row) {
    return implode(',', $row);
}, $data);
```

Jetzt haben wir gar keine Verschachtelung mehr, und der Code wird wahrscheinlich schneller sein, da wir alle Schleifen mit nativen C-Funktionen anstelle von PHP machen. Wir müssen ein bisschen Trickserei betreiben, um das Komma an `implode` zu übergeben, also könnte man argumentieren, dass der Stopp beim vorherigen Schritt viel verständlicher ist.

### 4.2 Versuchen Sie, `else` nicht zu verwenden

Das behandelt wirklich zwei Hauptideen. Die erste ist mehrere Return-Anweisungen aus einer Methode. Wenn Sie genug Informationen haben, um eine Entscheidung über das Ergebnis der Methode zu treffen, treffen Sie diese Entscheidung und returnen Sie. Die zweite ist eine Idee, die als [Guard Clauses](http://c2.com/cgi/wiki?GuardClause) bekannt ist. Das sind im Wesentlichen Validierungsprüfungen kombiniert mit frühen Returns, normalerweise ganz oben in einer Methode. Lassen Sie mich Ihnen zeigen, was ich meine.

```php
public function addThreeInts($first, $second, $third) {
    if (is_int($first)) {
        if (is_int($second)) {
            if (is_int($third)) {
                $sum = $first + $second + $third;
            } else {
                return null;
            }
        } else {
            return null;
        }
    } else {
        return null;
    }
    return $sum;
}
```

Das ist wieder ziemlich unkompliziert, es addiert 3 Integers und gibt das Ergebnis zurück oder `null`, wenn irgendeiner der Parameter kein Integer ist. Wenn man davon absieht, dass wir all diese Prüfungen in eine einzelne Zeile mit AND-Operatoren kombinieren könnten, denke ich, Sie können sehen, wie die verschachtelte if/else-Struktur den Code schwerer zu folgen macht. Schauen Sie sich stattdessen dieses Beispiel an.

```php
public function addThreeInts($first, $second, $third) {
    if (!is_int($first)) {
        return null;
    }
    if (!is_int($second)) {
        return null;
    }
    if (!is_int($third)) {
        return null;
    }
    return $first + $second + $third;
}
```

Für mich ist dieses Beispiel viel einfacher zu folgen. Hier verwenden wir Guard Clauses, um unsere anfänglichen Annahmen über die Parameter zu überprüfen und die Methode sofort zu verlassen, wenn sie nicht bestehen. Wir haben auch keine Zwischenvariable mehr, um die Summe durch die Methode zu verfolgen. In diesem Fall haben wir überprüft, dass wir bereits auf dem happy path sind, und können einfach tun, wofür wir hier sind. Wieder könnten wir all diese Prüfungen in einem `if` machen, aber das Prinzip sollte klar sein.

5 Unit-Testing
--------------

Unit-Testing ist die Praxis, kleine Tests zu schreiben, die Verhalten in Ihrem Code überprüfen. Sie werden fast immer in derselben Sprache wie der Code (in diesem Fall PHP) geschrieben und sind so schnell gedacht, dass sie jederzeit ausgeführt werden können. Sie sind extrem wertvoll als Tool, um Ihren Code zu verbessern. Neben den offensichtlichen Vorteilen, sicherzustellen, dass Ihr Code tut, was Sie denken, dass er tut, kann Unit-Testing auch sehr nützliches Design-Feedback geben. Wenn ein Stück Code schwer zu testen ist, zeigt das oft Designprobleme auf. Sie geben Ihnen auch ein Sicherheitsnetz gegen Regressionen und ermöglichen es Ihnen, viel öfter zu refactorisieren und Ihren Code zu einer saubereren Design zu entwickeln.

### 5.1 Tools

Es gibt mehrere Unit-Testing-Tools in PHP, aber mit Abstand das häufigste ist [PHPUnit](https://phpunit.de/). Sie können es installieren, indem Sie eine [PHAR](http://php.net/manual/en/intro.phar.php)-Datei [direkt](https://phar.phpunit.de/phpunit.phar) herunterladen oder es mit Composer installieren. Da wir Composer für alles andere verwenden, zeigen wir diese Methode. Da PHPUnit wahrscheinlich nicht in Produktion deployed wird, können wir es als Dev-Abhängigkeit mit dem folgenden Befehl installieren:

```bash
composer require --dev phpunit/phpunit
```

### 5.2 Tests sind eine Spezifikation

Die wichtigste Rolle von Unit-Tests in Ihrem Code ist es, eine ausführbare Spezifikation zu bieten, was der Code tun soll. Selbst wenn der Testcode falsch ist oder der Code Fehler hat, ist das Wissen, was das System _soll_ tun, unbezahlbar.

### 5.3 Schreiben Sie Ihre Tests zuerst

Wenn Sie die Chance hatten, einen Satz Tests zu sehen, der vor dem Code geschrieben wurde, und einen, der nach dem Code geschrieben wurde, sind sie auffallend unterschiedlich. Die „nach“-Tests sind viel mehr mit den Implementierungsdetails der Klasse beschäftigt und stellen sicher, dass sie gute Zeilenumfänge haben, während die „vor“-Tests mehr darum gehen, das gewünschte externe Verhalten zu überprüfen. Das ist wirklich das, was uns mit Unit-Tests interessiert, nämlich sicherzustellen, dass die Klasse das richtige Verhalten zeigt. Auf Implementierung fokussierte Tests machen Refactoring tatsächlich schwieriger, weil sie brechen, wenn die Interna der Klassen ändern, und Sie haben sich gerade die Vorteile der Informationsversteckung in OOP gekostet.

### 5.4 Was ein guter Unit-Test ausmacht

Gute Unit-Tests teilen viele der folgenden Merkmale:

*   Schnell – sollte in Millisekunden laufen.
*   Kein Netzwerkzugriff – sollte in der Lage sein, Wireless auszuschalten/unstecken und alle Tests bestehen lassen.
*   Begrenzter Dateisystemzugriff – das trägt zur Geschwindigkeit und Flexibilität bei, wenn Code in andere Umgebungen deployed wird.
*   Kein Datenbankzugriff – vermeidet kostspielige Setup- und Teardown-Aktivitäten.
*   Testen Sie nur eine Sache auf einmal – ein Unit-Test sollte nur einen Grund zum Scheitern haben.
*   Gut benannt – siehe 5.2 oben.
*   Meist Fake-Objekte – die einzigen „realen“ Objekte in Unit-Tests sollten das Objekt sein, das wir testen, und einfache Value-Objekte. Der Rest sollte eine Form von [Test Double](https://phpunit.de/manual/current/en/test-doubles.html) sein.

Es gibt Gründe, gegen einige davon zu gehen, aber als allgemeine Richtlinien werden sie Ihnen gut dienen.

### 5.5 Wenn Testing schmerzhaft ist

> Unit-Testing zwingt Sie, den Schmerz eines schlechten Designs vorneweg zu spüren – Michael Feathers

Wenn Sie Unit-Tests schreiben, zwingen Sie sich, die Klasse tatsächlich zu verwenden, um Dinge zu erledigen. Wenn Sie Tests am Ende schreiben oder, schlimmer noch, den Code einfach über die Wand für QA oder wen auch immer werfen, um Tests zu schreiben, bekommen Sie kein Feedback darüber, wie sich die Klasse tatsächlich verhält. Wenn wir Tests schreiben und die Klasse ein echtes Problem ist, finden wir das heraus, während wir sie schreiben, was fast die günstigste Zeit ist, es zu beheben.

Wenn eine Klasse schwer zu testen ist, ist das ein Designfehler. Verschiedene Fehler manifestieren sich auf unterschiedliche Weisen. Wenn Sie eine Menge Mocking machen müssen, hat Ihre Klasse wahrscheinlich zu viele Abhängigkeiten oder Ihre Methoden tun zu viel. Je mehr Setup Sie für jeden Test machen müssen, desto wahrscheinlicher ist es, dass Ihre Methoden zu viel tun. Wenn Sie wirklich komplizierte Test-Szenarien schreiben müssen, um Verhalten auszuführen, tun die Methoden der Klasse wahrscheinlich zu viel. Wenn Sie in eine Menge privater Methoden und Zustände eintauchen müssen, um Dinge zu testen, versucht vielleicht eine andere Klasse herauszukommen. Unit-Testing ist sehr gut darin, „Eisberg-Klassen“ aufzudecken, bei denen 80% dessen, was die Klasse tut, in geschütztem oder privatem Code versteckt ist. Ich war früher ein großer Fan davon, so viel wie möglich geschützt zu machen, aber jetzt habe ich erkannt, dass ich nur meine individuellen Klassen für zu viel verantwortlich gemacht habe, und die echte Lösung war, die Klasse in kleinere Stücke zu zerlegen.

> **Geschrieben von Brian Fenton** – Brian Fenton ist seit 8 Jahren PHP-Entwickler im Mittleren Westen und in der Bay Area, derzeit bei Thismoment. Er konzentriert sich auf Code-Craftsmanship und Designprinzipien. Blog auf www.brianfenton.us, Twitter unter @brianfenton. Wenn er nicht beschäftigt ist, Vater zu sein, genießt er Essen, Bier, Gaming und Lernen.