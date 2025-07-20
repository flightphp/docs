# Awesome Plugins

Flight is incredibly extensible. There are a number of plugins that can be used to add functionality to your Flight application. Some are officially supported by the Flight Team and others are micro/lite libraries to help you get started.

## API Documentation

API documentation is crucial for any API. It helps developers understand how to interact with your API and what to expect in return. There are a couple tools available to help you generate API documentation for your Flight Projects.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Blog post written by Daniel Schreiber on how to use the OpenAPI Spec with FlightPHP to build out your API using an API first approach.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI is a great tool to help you generate API documentation for your Flight projects. It's very easy to use and can be customized to fit your needs. This is the PHP library to help you generate the Swagger documentation.

## Application Performance Monitoring (APM)

Application Performance Monitoring (APM) is crucial for any application. It helps you understand how your application is performing and where the bottlenecks are. There are a number of APM tools that can be used with Flight.
- <span class="badge bg-primary">official</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM is a simple APM library that can be used to monitor your Flight applications. It can be used to monitor the performance of your application and help you identify bottlenecks.

## Authorization/Permissions

Authorization and Permissions are crucial for any application that requires controls to be in place for who can access what.

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - Official Flight Permissions library. This library is a simple way to add user and application level permissions to your application. 

## Caching

Caching is a great way to speed up your application. There are a number of caching libraries that can be used with Flight.

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Light, simple and standalone PHP in-file caching class

## CLI

CLI applications are a great way to interact with your application. You can use them to generate controllers, display all routes, and more.

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway is a CLI application that helps you manage your Flight applications.

## Cookies

Cookies are a great way to store small bits of data on the client side. They can be used to store user preferences, application settings, and more.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie is a PHP library that provides a simple and effective way to manage cookies.

## Debugging

Debugging is crucial when you are developing in your local environment. There are a few plugins that can elevate your debugging experience.

- [tracy/tracy](/awesome-plugins/tracy) - This is a full featured error handler that can be used with Flight. It has a number of panels that can help you debug your application. It's also very easy to extend and add your own panels.
- <span class="badge bg-primary">official</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Used with the [Tracy](/awesome-plugins/tracy) error handler, this plugin adds a few extra panels to help with debugging specifically for Flight projects.

## Databases

Databases are the core to most applications. This is how you store and retrieve data. Some database libraries are simply wrappers to write queries and some are full fledged ORMs.

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Official Flight PDO Wrapper that's part of the core. This is a simple wrapper to help simplify the process of writing queries and executing them. It is not an ORM.
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - Official Flight ActiveRecord ORM/Mapper. Great little library for easily retrieving and storing data in your database.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin to keep track of all database changes for your project.

## Encryption

Encryption is crucial for any application that stores sensitive data. Encrypting and decrypting the data isn't terribly hard, but properly storing the encryption key [can](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [be](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficult](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). The most important thing is to never store your encryption key in a public directory or to commit it to your code repository.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - This is a library that can be used to encrypt and decrypt data. Getting up and running is fairly simple to start encrypting and decrypting data.

## Job Queue

Job queues are really helpful to asynchronously process tasks. This can be sending emails, processing images, or anything that doesn't need to be done in real time.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue is a library that can be used to process jobs asynchronously. It can be used with beanstalkd, MySQL/MariaDB, SQLite, and PostgreSQL.

## Session

Sessions aren't really useful for API's but for building out a web application, sessions can be crucial for maintaining state and login information.

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - Official Flight Session library. This is a simple session library that can be used to store and retrieve session data. It uses PHP's built in session handling.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (non-blocking, flash, segment, session encryption). Uses PHP open_ssl for optional encrypt/decryption of session data.

## Templating

Templating is core to any web application with a UI. There are a number of templating engines that can be used with Flight.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - This is a very basic templating engine that is part of the core. It's not recommended to be used if you have more than a couple pages in your project.
- [latte/latte](/awesome-plugins/latte) - Latte is a full featured templating engine that is very easy to use and feels closer to a PHP syntax than Twig or Smarty. It's also very easy to extend and add your own filters and functions.

## WordPress Integration

Want to use Flight in your WordPress project? There's a handy plugin for that!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - This WordPress plugin lets you run Flight right alongside WordPress. It's perfect for adding custom APIs, microservices, or even full apps to your WordPress site using the Flight framework. Super useful if you want the best of both worlds!

## Contributing

Got a plugin you'd like to share? Submit a pull request to add it to the list!