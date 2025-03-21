# FlightPHP Session - Lightweight File-Based Session Handler

This is a lightweight, file-based session handler plugin for the [Flight PHP Framework](https://docs.flightphp.com/). It provides a simple yet powerful solution for managing sessions, with features like non-blocking session reads, optional encryption, auto-commit functionality, and a test mode for development. Session data is stored in files, making it ideal for applications that don’t require a database.

If you do want to use a database, check out the [ghostff/session](/awesome-plugins/ghost-session) plugin with many of these same features but with a database backend.

Visit the [Github repository](https://github.com/flightphp/session) for the full source code and details.

## Installation

Install the plugin via Composer:

```bash
composer require flightphp/session
```

## Basic Usage

Here’s a simple example of how to use the `flightphp/session` plugin in your Flight application:

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// Register the session service
$app->register('session', Session::class);

// Example route with session usage
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // Outputs: johndoe
    echo $session->get('preferences', 'default_theme'); // Outputs: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => 'User is logged in!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // Clear all session data
    Flight::json(['message' => 'Logged out successfully']);
});

Flight::start();
```

### Key Points
- **Non-Blocking**: Uses `read_and_close` for session start by default, preventing session locking issues.
- **Auto-Commit**: Enabled by default, so changes are saved automatically on shutdown unless disabled.
- **File Storage**: Sessions are stored in the system temp directory under `/flight_sessions` by default.

## Configuration

You can customize the session handler by passing an array of options when registering:

```php
// Yep, it's a double array :)
$app->register('session', Session::class, [ [
    'save_path' => '/custom/path/to/sessions',         // Directory for session files
    'encryption_key' => 'a-secure-32-byte-key-here',   // Enable encryption (32 bytes recommended for AES-256-CBC)
    'auto_commit' => false,                            // Disable auto-commit for manual control
    'start_session' => true,                           // Start session automatically (default: true)
    'test_mode' => false                               // Enable test mode for development
] ]);
```

### Configuration Options
| Option            | Description                                      | Default Value                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | Directory where session files are stored         | `sys_get_temp_dir() . '/flight_sessions'` |
| `encryption_key`  | Key for AES-256-CBC encryption (optional)        | `null` (no encryption)            |
| `auto_commit`     | Auto-save session data on shutdown               | `true`                            |
| `start_session`   | Start the session automatically                  | `true`                            |
| `test_mode`       | Run in test mode without affecting PHP sessions  | `false`                           |
| `test_session_id` | Custom session ID for test mode (optional)       | Randomly generated if not set     |

## Advanced Usage

### Manual Commit
If you disable auto-commit, you must manually commit changes:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // Explicitly save changes
});
```

### Session Security with Encryption
Enable encryption for sensitive data:

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // Encrypted automatically
    echo $session->get('credit_card'); // Decrypted on retrieval
});
```

### Session Regeneration
Regenerate the session ID for security (e.g., after login):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // New ID, keep data
    // OR
    $session->regenerate(true); // New ID, delete old data
});
```

### Middleware Example
Protect routes with session-based authentication:

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Welcome to the admin panel']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Access denied');
    }
});
```

This is just a simple example of how to use this in middleware. For a more in depth example, see the [middleware](/learn/middleware) documentation.

## Methods

The `Session` class provides these methods:

- `set(string $key, $value)`: Stores a value in the session.
- `get(string $key, $default = null)`: Retrieves a value, with an optional default if the key doesn’t exist.
- `delete(string $key)`: Removes a specific key from the session.
- `clear()`: Deletes all session data, but keeps the same file name for the session.
- `commit()`: Saves the current session data to the file system.
- `id()`: Returns the current session ID.
- `regenerate(bool $deleteOldFile = false)`: Regenerates the session ID including creating a new session file, keeping all the old data and the old file remains on the system. If `$deleteOldFile` is `true`, the old session file is deleted.
- `destroy(string $id)`: Destroys a session by ID and deletes the session file from the system. This is part of the `SessionHandlerInterface` and `$id` is required. Typical usage would be `$session->destroy($session->id())`.
- `getAll()` : Returns all data from current session.

All methods except `get()` and `id()` return the `Session` instance for chaining.

## Why Use This Plugin?

- **Lightweight**: No external dependencies—just files.
- **Non-Blocking**: Avoids session locking with `read_and_close` by default.
- **Secure**: Supports AES-256-CBC encryption for sensitive data.
- **Flexible**: Auto-commit, test mode, and manual control options.
- **Flight-Native**: Built specifically for the Flight framework.

## Technical Details

- **Storage Format**: Session files are prefixed with `sess_` and stored in the configured `save_path`. Encrypted data uses an `E` prefix, plaintext uses `P`.
- **Encryption**: Uses AES-256-CBC with a random IV per session write when an `encryption_key` is provided.
- **Garbage Collection**: Implements PHP’s `SessionHandlerInterface::gc()` to clean up expired sessions.

## Contributing

Contributions are welcome! Fork the [repository](https://github.com/flightphp/session), make your changes, and submit a pull request. Report bugs or suggest features via the Github issue tracker.

## License

This plugin is licensed under the MIT License. See the [Github repository](https://github.com/flightphp/session) for details.
