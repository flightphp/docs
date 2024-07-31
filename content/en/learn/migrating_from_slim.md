# Migrating From Slim

## What is Slim?
[Slim](https://slimframework.com) is a PHP micro framework that helps you quickly write simple yet powerful web applications and APIs.

A lot of the inspiration for some of the v3 features of Flight actually came from Slim. Grouping routes, and executing middleware in a 
specific order are two features that were inspired by Slim. Slim v3 came out geared towards simplicity, but there has been 
[mixed reviews](https://github.com/slimphp/Slim/issues/2770) regarding v4.

## Pros compared to Flight

- Slim has a larger community of developers, who in turn make handy modules to help you not reinvent the wheel.
- Slim follows a lot of interfaces and standards that are common in the PHP community, which increases interoperability.
- Slim has decent documentation and tutorials that can be used to learn the framework (nothing compared to Laravel or Symfony though).
- Slim has some various resources like YouTube tutorials and online articles that can be used to learn the framework.
- Slim let's you use whatever components you want to handle the core routing features as it is PSR-7 compliant.

## Cons compared to Flight

- Surprisingly, Slim isn't as fast as you think it would be for a micro-framework. See the 
  [TechEmpower benchmarks](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  for more information.
- Flight is geared towards a developer who is looking to build a lightweight, fast, and easy to use web application.
- Flight has no dependencies, whereas [Slim has a few dependencies](https://github.com/slimphp/Slim/blob/4.x/composer.json) that you must install.
- Flight is geared towards simplicity and ease of use.
- One of Flight's core features is that it does it's best to maintain backwards compatibility. Slim v3 to v4 was a breaking change.
- Flight is meant for developers who are venturing into the land of frameworks for the first time.
- Flight can also do enterprise level applications, but it does not have as many examples and tutorials as Slim does.
  It will also require more discipline on the part of the developer to keep things organized and well-structured.
- Flight gives the developer more control over the application, whereas Slim can sneak in some magic behind the scenes.
- Flight has a simple [PdoWrapper](/awesome-plugins/pdo-wrapper) that can be used to interact with your database. Slim requires you to use 
  a third-party library.
- Flight has a [permissions plugin](/awesome-plugins/permissions) that can be used to secure your application. Slim requires you to use 
  a third-party library.
- Flight has an ORM called [active-record](/awesome-plugins/active-record) that can be used to interact with your database. Slim requires you to use 
  a third-party library.
- Flight has a CLI application called [runway](/awesome-plugins/runway) that can be used to run your application from the command line. Slim does not.