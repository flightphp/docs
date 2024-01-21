# JSON

Flight provides support for sending JSON and JSONP responses. To send a JSON response you
pass some data to be JSON encoded:

```php
Flight::json(['id' => 123]);
```

For JSONP requests you, can optionally pass in the query parameter name you are
using to define your callback function:

```php
Flight::jsonp(['id' => 123], 'q');
```

So, when making a GET request using `?q=my_func`, you should receive the output:

```javascript
my_func({"id":123});
```

If you don't pass in a query parameter name it will default to `jsonp`.