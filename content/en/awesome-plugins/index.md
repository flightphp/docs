# Awesome Plugins

Flight is incredibly extensible. There are a number of plugins that can be used to add functionality to your Flight application. Some are officially supported by the Flight Team and others are micro/lite libraries to help you get started.

## Caching

Caching is a great way to speed up your application. There are a number of caching libraries that can be used with Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Light, simple and standalone PHP in-file caching class

## Debugging

Debugging is crucial when you are developing in your local environment. There are a few plugins that can elevate your debugging experience.

- [tracy/tracy](/awesome-plugins/tracy) - This is a full featured error handler that can be used with Flight. It has a number of panels that can help you debug your application. It's also very easy to extend and add your own panels.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Used with the [Tracy](/awesome-plugins/tracy) error handler, this plugin adds a few extra panels to help with debugging specifically for Flight projects.

## Databases

Databases are the core to most applications. This is how you store and retrieve data. Some database libraries are simply wrappers to write queries and some are full fledged ORMs.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Official Flight PDO Wrapper that's part of the core. This is a simple wrapper to help simplify the process of writing queries and executing them. It is not an ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Official Flight ActiveRecord ORM/Mapper. Great little library for easily retrieving and storing data in your database.

## Session

Sessions aren't really useful for API's but for building out a web application, sessions can be crucial for maintaining state and login information.

- [Ghostff/Session](/awesome-plugins/session) - PHP Session Manager (non-blocking, flash, segment, session encryption). Uses PHP open_ssl for optional encrypt/decryption of session data.

## Templating

Templating is core to any web application with a UI. There are a number of templating engines that can be used with Flight.

- [flightphp/core View](/learn#views) - This is a very basic templating engine that is part of the core. It's not recommended to be used if you have more than a couple pages in your project.
- [latte/latte](/awesome-plugins/latte) - Latte is a full featured templating engine that is very easy to use and feels closer to a PHP syntax than Twig or Smarty. It's also very easy to extend and add your own filters and functions.

## Contributing

Got a plugin you'd like to share? Submit a pull request to add it to the list!