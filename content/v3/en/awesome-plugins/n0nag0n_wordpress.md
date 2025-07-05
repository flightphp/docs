# WordPress Integration: n0nag0n/wordpress-integration-for-flight-framework

Want to use Flight PHP inside your WordPress site? This plugin makes it a breeze! With `n0nag0n/wordpress-integration-for-flight-framework`, you can run a full Flight app right alongside your WordPress install—perfect for building custom APIs, microservices, or even full-featured apps without leaving the comfort of WordPress.

---

## What Does It Do?

- **Seamlessly integrates Flight PHP with WordPress**
- Route requests to either Flight or WordPress based on URL patterns
- Organize your code with controllers, models, and views (MVC)
- Easily set up the recommended Flight folder structure
- Use WordPress's database connection or your own
- Fine-tune how Flight and WordPress interact
- Simple admin interface for configuration

## Installation

1. Upload the `flight-integration` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin in the WordPress admin (Plugins menu).
3. Go to **Settings > Flight Framework** to configure the plugin.
4. Set the vendor path to your Flight installation (or use Composer to install Flight).
5. Configure your app folder path and create the folder structure (the plugin can help with this!).
6. Start building your Flight application!

## Usage Examples

### Basic Route Example
In your `app/config/routes.php` file:

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### Controller Example

Create a controller in `app/controllers/ApiController.php`:

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // You can use WordPress functions inside Flight!
        $users = get_users();
        $result = [];
        foreach($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email
            ];
        }
        Flight::json($result);
    }
}
```

Then, in your `routes.php`:

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## FAQ

**Q: Do I need to know Flight to use this plugin?**  
A: Yes, this is for developers who want to use Flight within WordPress. Basic knowledge of Flight's routing and request handling is recommended.

**Q: Will this slow down my WordPress site?**  
A: Nope! The plugin only processes requests that match your Flight routes. All other requests go to WordPress as usual.

**Q: Can I use WordPress functions in my Flight app?**  
A: Absolutely! You have full access to all WordPress functions, hooks, and globals from within your Flight routes and controllers.

**Q: How do I create custom routes?**  
A: Define your routes in the `config/routes.php` file in your app folder. See the sample file created by the folder structure generator for examples.

## Changelog

**1.0.0**  
Initial release.

---

For more info, check out the [GitHub repo](https://github.com/n0nag0n/wordpress-integration-for-flight-framework).
