# Troubleshooting

This page will help you troubleshoot common issues that you may run into when using Flight.

## Common Issues

### 404 Not Found or Unexpected Route Behavior

If you are seeing a 404 Not Found error (but you swear on your life that it's really there and it's not a typo) this actually could be a problem with you returning a value in your route endpoint instead of just echoing it. The reason for this is intentional but could sneak up on some developers.

```php

Flight::route('/hello', function(){
	// This might cause a 404 Not Found error
	return 'Hello World';
});

// What you probably want
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

The reason for this is because of a special mechanism built into the router that handles the return output as a single to "go to the next route". You can see the behavior documented in the [Routing](/learn/routing#passing) section.