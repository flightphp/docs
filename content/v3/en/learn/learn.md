# Learn About Flight

Flight is a fast, simple, extensible framework for PHP. It is quite versatile and can be used for building any kind of web application. 
It is built with simplicity in mind and is written in a way that is easy to understand and use.

> **Note:** You will see examples that use `Flight::` as a static variable and some that use the `$app->` Engine object. Both work interchangeably with the other. `$app` and `$this->app` in a controller/middleware is the recommended approach from the Flight team.

## Core Components

### [Routing](/learn/routing)

Learn how to manage routes for your web application. This also includes grouping routes, route parameters and middleware.

### [Middleware](/learn/middleware)

Learn how to use middleware to filter requests and responses in your application.

### [Autoloading](/learn/autoloading)

Learn how to autoload your own classes in your application.

### [Requests](/learn/requests)

Learn how to handle requests and responses in your application.

### [Responses](/learn/responses)

Learn how to send responses to your users.

### [HTML Templates](/learn/templates)

Learn how to use the built-in view engine to render your HTML templates.

### [Security](/learn/security)

Learn how to secure your application from common security threats.

### [Configuration](/learn/configuration)

Learn how to configure the framework for your application.

### [Event Manager](/learn/events)

Learn how to use the event system to add custom events to your application.

### [Extending Flight](/learn/extending)

Learn how to extend the framework to with adding your own methods and classes.

### [Method Hooks and Filtering](/learn/filtering)

Learn how to add event hooks to your methods and internal framework methods.

### [Dependency Injection Container (DIC)](/learn/dependency-injection-container)

Learn how to use dependency injection containers (DIC) to manage your application's dependencies.

## Utility Classes

### [Collections](/learn/collections)

Collections are used to hold data and be accessible as an array or as an object for ease of use.

### [JSON Wrapper](/learn/json)

This has a few simple functions to make encoding and decoding your JSON consistent.

### [SimplePdo](/learn/simple-pdo)

PDO at times can add more headache than necessary. SimplePdo is a modern PDO helper class with convenient methods like `insert()`, `update()`, `delete()`, and `transaction()` to make database operations much easier.

### [PdoWrapper](/learn/pdo-wrapper) (Deprecated)

The original PDO wrapper is deprecated as of v3.18.0. Please use [SimplePdo](/learn/simple-pdo) instead.

### [Uploaded File Handler](/learn/uploaded-file)

A simple class to help manage uploaded files and move them to a permanent location.

## Important Concepts

### [Why a Framework?](/learn/why-frameworks)

Here's a short article on why you should use a framework. It's a good idea to understand the benefits of using a framework before you start using one.

Additionally an excellent tutorial has been created by [@lubiana](https://git.php.fail/lubiana). While it doesn't go into great detail about Flight specifically, 
this guide will help you understand some of the major concepts surrounding a framework and why they are beneficial to use. 
You can find the tutorial [here](https://git.php.fail/lubiana/no-framework-tutorial/src/branch/master/README.md).

### [Flight Compared to Other Frameworks](/learn/flight-vs-another-framework)

If you are migrating from another framework such as Laravel, Slim, Fat-Free, or Symfony to Flight, this page will help you understand the differences between the two.

## Other Topics

### [Unit Testing](/learn/unit-testing)

Follow this guide to learn how to unit test your Flight code to be rock solid.

### [AI & Developer Experience](/learn/ai)

Learn how Flight works with AI tools and modern developer workflows to help you code faster and smarter.

### [Migrating v2 -> v3](/learn/migrating-to-v3)

Backwards compatibility has for the most part been maintained, but there are some changes that you should be aware of when migrating from v2 to v3.