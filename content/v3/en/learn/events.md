# Events System in Flight PHP (v3.15.0+)

Flight PHP introduces a lightweight and intuitive event system that lets you register and trigger custom events in your application. With the addition of `Flight::onEvent()` and `Flight::triggerEvent()`, you can now hook into key moments of your app’s lifecycle or define your own events to make your code more modular and extensible. These methods are part of Flight’s **mappable methods**, meaning you can override their behavior to suit your needs.

This guide covers everything you need to know to get started with events, including why they’re valuable, how to use them, and practical examples to help beginners understand their power.

## Why Use Events?

Events allow you to separate different parts of your application so they don’t depend too heavily on each other. This separation—often called **decoupling**—makes your code easier to update, extend, or debug. Instead of writing everything in one big chunk, you can split your logic into smaller, independent pieces that respond to specific actions (events).

Imagine you’re building a blog app:
- When a user posts a comment, you might want to:
  - Save the comment to the database.
  - Send an email to the blog owner.
  - Log the action for security.

Without events, you’d cram all this into one function. With events, you can split it up: one part saves the comment, another triggers an event like `'comment.posted'`, and separate listeners handle the email and logging. This keeps your code cleaner and lets you add or remove features (like notifications) without touching the core logic.

### Common Uses
- **Logging**: Record actions like logins or errors without cluttering your main code.
- **Notifications**: Send emails or alerts when something happens.
- **Updates**: Refresh caches or notify other systems about changes.

## Registering Event Listeners

To listen for an event, use `Flight::onEvent()`. This method lets you define what should happen when an event occurs.

### Syntax
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: A name for your event (e.g., `'user.login'`).
- `$callback`: The function to run when the event is triggered.

### How It Works
You “subscribe” to an event by telling Flight what to do when it happens. The callback can accept arguments passed from the event trigger.

### Simple Example
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";
});
```
Here, when the `'user.login'` event is triggered, it’ll greet the user by name.

### Key Points
- You can add multiple listeners to the same event—they’ll run in the order you registered them.
- The callback can be a function, an anonymous function, or a method from a class.

## Triggering Events

To make an event happen, use `Flight::triggerEvent()`. This tells Flight to run all the listeners registered for that event, passing along any data you provide.

### Syntax
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: The event name you’re triggering (must match a registered event).
- `...$args`: Optional arguments to send to the listeners (can be any number of arguments).

### Simple Example
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
This triggers the `'user.login'` event and sends `'alice'` to the listener we defined earlier, which will output: `Welcome back, alice!`.

### Key Points
- If no listeners are registered, nothing happens—your app won’t break.
- Use the spread operator (`...`) to pass multiple arguments flexibly.

## Overriding Event Methods

`Flight::onEvent()` and `Flight::triggerEvent()` are available to be [extended](/learn/extending), meaning you can redefine how they work. This is great for advanced users who want to customize the event system, like adding logging or changing how events are dispatched.

### Example: Customizing `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Log every event registration
    error_log("New event listener added for: $event");
    // Call the default behavior (assuming an internal event system)
    Flight::event()->on($event, $callback);
});
```
Now, every time you register an event, it logs it before proceeding.

### Why Override?
- Add debugging or monitoring.
- Restrict events in certain environments (e.g., disable in testing).
- Integrate with a different event library.

## Examples for Beginners

Let’s walk through some real-world scenarios to show how events work and why they’re helpful.

### Example 1: Logging a User Login
```php
// Step 1: Register a listener
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Step 2: Trigger it in your app
Flight::route('/login', function () {
    $username = 'bob'; // Pretend this comes from a form
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Why It’s Useful**: The login code doesn’t need to know about logging—it just triggers the event. You can later add more listeners (e.g., send a welcome email) without changing the route.

### Example 2: Notifying About New Users
```php
// Listener for new registrations
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulate sending an email
    echo "Email sent to $email: Welcome, $name!";
});

// Trigger it when someone signs up
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Why It’s Useful**: The signup logic focuses on creating the user, while the event handles notifications. You could add more listeners (e.g., log the signup) later.

### Example 3: Clearing a Cache
```php
// Listener to clear a cache
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Clear session cache if applicable
    echo "Cache cleared for page $pageId.";
});

// Trigger when a page is edited
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Pretend we updated the page
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Why It’s Useful**: The editing code doesn’t care about caching—it just signals the update. Other parts of the app can react as needed.

## Best Practices

- **Name Events Clearly**: Use specific names like `'user.login'` or `'page.updated'` so it’s obvious what they do.
- **Keep Listeners Simple**: Don’t put slow or complex tasks in listeners—keep your app fast.
- **Test Your Events**: Trigger them manually to ensure listeners work as expected.
- **Use Events Wisely**: They’re great for decoupling, but too many can make your code hard to follow—use them when it makes sense.

The event system in Flight PHP, with `Flight::onEvent()` and `Flight::triggerEvent()`, gives you a simple yet powerful way to build flexible applications. By letting different parts of your app talk to each other through events, you can keep your code organized, reusable, and easy to expand. Whether you’re logging actions, sending notifications, or managing updates, events help you do it without tangling your logic. Plus, with the ability to override these methods, you’ve got the freedom to tailor the system to your needs. Start small with a single event, and watch how it transforms your app’s structure!
