# Migrating From Fat-Free

## What is Fat-Free?
[Fat-Free](https://fatfreeframework.com) (affectionately known as **F3**) is a powerful yet easy-to-use PHP micro-framework designed to help you build dynamic and robust 
web applications - fast!

Flight compares with Fat-Free in many ways and is probably the closest cousin in terms of features and simplicity. Fat-Free has a
lot of features that Flight does not have, but it also has a lot of features that Flight does have. Fat-Free is starting to show its age
and is not as popular as it once was.

Updates are becoming less frequent and the community is not as active as it once was. The code is simple enough, but sometimes the lack of
syntax discipline can make it difficult to read and understand. It does work for PHP 8.3, but the code itself still looks like it lives in
PHP 5.3.

## Pros compared to Flight

- Fat-Free has a few more stars on GitHub than Flight does.
- Fat-Free has some decent documentation, but it does lack in some areas with clarity.
- Fat-Free has some sparse resources like YouTube tutorials and online articles that can be used to learn the framework.
- Fat-Free has [some helpful plugins](https://fatfreeframework.com/3.8/api-reference) built-in that are sometimes helpful.
- Fat-Free has a built-in ORM called a Mapper that can be used to interact with your database. Flight has [active-record](/awesome-plugins/active-record).
- Fat-Free has Sessions, Caching and localization built-in. Flight requires you to use third-party libraries, but is covered in the [documentation](/awesome-plugins).
- Fat-Free has a small group of [community created plugins](https://fatfreeframework.com/3.8/development#Community) that can be used to extend the framework. Flight has some covered in the [documentation](/awesome-plugins) and [examples](/examples) pages.
- Fat-Free like Flight has no dependencies.
- Fat-Free like Flight is geared towards giving the developer control over their application and a simple developer experience.
- Fat-Free maintains backwards compatibility like Flight does (partially because updates are getting [less frequent](https://github.com/bcosca/fatfree/releases)).
- Fat-Free like Flight is meant for developers who are venturing into the land of frameworks for the first time.
- Fat-Free has a built in template engine that is more robust than Flight's template engine. Flight recommends [Latte](/awesome-plugins/latte) to accomplish this.
- Fat-Free has a unique CLI type "route" command where you can build CLI apps within Fat-Free itself and treat it much like a `GET` request. Flight accomplishes this with [runway](/awesome-plugins/runway).

## Cons compared to Flight

- Fat-Free has some implementation tests and even has it's own [test](https://fatfreeframework.com/3.8/test) class that's very basic. However,
  it is not 100% unit tested like Flight is. 
- You have to use a search engine like Google to actually search the documentation site.
- Flight has dark mode on their documentation site. (mic drop)
- Fat-Free has some modules that are woefully unmaintained.
- Flight has a simple [PdoWrapper](/awesome-plugins/pdo-wrapper) that is a touch more simple than Fat-Free's built in `DB\SQL` class.
- Flight has a [permissions plugin](/awesome-plugins/permissions) that can be used to secure your application. Slim requires you to use 
  a third-party library.
- Flight has an ORM called [active-record](/awesome-plugins/active-record) which feels more like an ORM than Fat-Free's Mapper.
  The added benefit of `active-record` is that you can define relationships between records for automatic joins where Fat-Free's Mapper
  requires you to create [SQL views](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Amazingly enough, Fat-Free does not have a root namespace. Flight is namespaced all the way through to not collide with your own code.
  the `Cache` class is the biggest offender here.
- Fat-Free does not have middleware. Instead there are `beforeroute` and `afterroute` hooks that can be used to filter requests and responses in controllers.
- Fat-Free cannot group routes.
- Fat-Free has a dependency injection container handler, but the documentation is incredibly sparse on how to use it.
- Debugging can get a little tricky since basically everything is stored in what's called the [`HIVE`](https://fatfreeframework.com/3.8/quick-reference)