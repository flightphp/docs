# Fehlerbehebung

Diese Seite wird Ihnen helfen, häufige Probleme zu beheben, auf die Sie stoßen können, wenn Sie Flight verwenden.

## Häufige Probleme

### 404 Nicht gefunden oder unerwartetes Routenverhalten

Wenn Sie einen 404 Nicht gefunden Fehler sehen (aber Sie schwören beim Leben, dass er wirklich da ist und es sich nicht um einen Tippfehler handelt), könnte dies tatsächlich ein Problem damit sein, dass Sie einen Wert in Ihrem Routenendpunkt zurückgeben anstatt ihn einfach auszugeben. Der Grund dafür ist beabsichtigt, könnte aber einige Entwickler überraschen.

```php

Flight::route('/hello', function(){
	// Dies könnte einen 404 Nicht gefunden Fehler verursachen
	return 'Hallo Welt';
});

// Was Sie wahrscheinlich wollen
Flight::route('/hello', function(){
	echo 'Hallo Welt';
});

```

Der Grund dafür liegt an einem speziellen Mechanismus, der in den Router eingebaut ist und die Rückgabenausgabe als Signal zum "Weiter zur nächsten Route" behandelt. Sie können das Verhalten im Abschnitt zur [Routenführung](/learn/routing#passing) nachlesen.