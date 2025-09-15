> _This article was originally published on [Airpair](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) in 2015. All credit is given to Airpair and Brian Fenton who originally wrote this article, though the website is no longer available and the article only exists within the [Wayback Machine](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing). This article has been added to the site for learning and educational purposes for the PHP community at large._

1 Setup and configuration
-------------------------

### 1.1 Keep Current

Let's call this out from the very beginning - a depressingly small number of PHP installs in the wild are current, or kept current. Whether that is due to shared hosting restrictions, defaults that no one thinks to change, or no time/budget for upgrade testing, the humble PHP binaries tend to get left behind. So one clear best practice that needs more emphasis is to always use a current version of PHP (5.6.x as of this article). Furthermore, it's also important to schedule regular upgrades of both PHP itself and any extensions or vendor libraries you may be using. Upgrades get you new language features, improved speed, lower memory usage, and security updates. The more frequently you upgrade, the less painful the process becomes.

### 1.2 Set sensible defaults

PHP does a decent job of setting good defaults out of the box with its _php.ini.development_ and _php.ini.production_ files, but we can do better. For one, they don't set a date/timezone for us. That makes sense from a distribution perspective, but without one, PHP will throw an E\_WARNING error any time we call a date/time related function. Here are some recommended settings:

*   date.timezone - pick from the [list of supported timezones](http://php.net/manual/en/timezones.php)
*   session.save\_path - if we're using files for sessions and not some other save handler, set this to something outside of _/tmp_. Leaving this as _/tmp_ can be risky on a shared hosting environment since _/tmp_ is typically wide open permissions-wise. Even with the sticky-bit set, anyone with access to list the contents of this directory can learn all of your active session IDs.
*   session.cookie\_secure - no brainer, turn this on if you are serving your PHP code over HTTPS.
*   session.cookie\_httponly - set this to prevent PHP session cookies from being accessible via JavaScript
*   More... use a tool like [iniscan](https://github.com/psecio/iniscan) to test your configuration for common vulnerabilities

### 1.3 Extensions

It's also a good idea to disable (or at least not enable) extensions that you won't use, like database drivers. To see what's enabled, run the `phpinfo()` command or go to a command line and run this.

```bash
$ php -i
``` 

The information is the same, but phpinfo() has HTML formatting added. The CLI version is easier to pipe to grep to find specific information though. Ex.

```bash
$ php -i | grep error_log
```

One caveat of this method though: it's possible to have different PHP settings apply to the web-facing version and the CLI version.

2 Use Composer
--------------

This may come as a surprise but one of the best practices for writing modern PHP is to write less of it. While it is true that one of the best ways to get good at programming is to do it, there are a large number of problems that have already been solved in the PHP space, like routing, basic input validation libraries, unit conversion, database abstraction layers, etc... Just go to [Packagist](https://www.packagist.org/) and browse around. You'll likely find that significant portions of the problem you're trying to solve have already been written and tested.

While it's tempting to write all the code yourself (and there's nothing wrong with writing your own framework or library as a learning experience) you should fight against those feelings of Not Invented Here and save yourself a lot of time and headache. Follow the doctrine of PIE instead - Proudly Invented Elsewhere. Also, if you do choose to write your own whatever, don't release it unless it does something significantly different or better than existing offerings.

[Composer](https://www.getcomposer.org/) is a package manager for PHP, similar to pip in Python, gem in Ruby, and npm in Node. It lets you define a JSON file that lists your code's dependencies, and it will attempt to resolve those requirements for you by downloading and installing the necessary code bundles.

### 2.1 Installing Composer

We're assuming that this is a local project, so let's install an instance of Composer just for the current project. Navigate to your project directory and run this:
```bash
$ curl -sS https://getcomposer.org/installer | php
```

Keep in mind that piping any download directly to a script interpreter (sh, ruby, php, etc...) is a security risk, so do read the install code and ensure you're comfortable with it before running any command like this.

For convenience sake (if you prefer typing `composer install` over `php composer.phar install`, you can use this command to install a single copy of composer globally:

```bash
$ mv composer.phar /usr/local/bin/composer
$ chmod +x composer
```

You may need to run those with `sudo` depending on your file permissions.

### 2.2 Using Composer

Composer has two main categories of dependencies that it can manage: "require" and "require-dev". Dependencies listed as "require" are installed everywhere, but "require-dev" dependencies are only installed when specifically requested. Usually these are tools for when the code is under active development, such as [PHP\_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer). The line below shows an example of how to install [Guzzle](http://docs.guzzlephp.org/en/latest/), a popular HTTP library.

```bash
$ php composer.phar require guzzle/guzzle
```

To install a tool just for development purposes, add the `--dev` flag:

```bash
$ php composer.phar require --dev 'sebastian/phpcpd'
```

This installs [PHP Copy-Paste Detector](https://github.com/sebastianbergmann/phpcpd), another code quality tool as a development-only dependency.

### 2.3 Install vs update

When we first run `composer install` it will install any libraries and their dependencies we need, based on the _composer.json_ file. When that is done, composer creates a lock file, predictably called _composer.lock_. This file contains a list of the dependencies composer found for us and their exact versions, with hashes. Then any future time we run `composer install`, it will look in the lock file and install those exact versions.

`composer update` is a bit of a different beast. It will ignore the _composer.lock_ file (if present) and try to find the most up to date versions of each of the dependencies that still satisfies the constraints in _composer.json_. It then writes a new _composer.lock_ file when it's finished.

### 2.4 Autoloading

Both composer install and composer update will generate an [autoloader](https://getcomposer.org/doc/04-schema.md#autoload) for us that tells PHP where to find all the necessary files to use the libraries we've just installed. To use it, just add this line (usually to a bootstrap file that gets executed on every request):
```php
require 'vendor/autoload.php';
```

3 Follow good design principles
-------------------------------

### 3.1 SOLID

SOLID is a mnemonic to remind us of five key principles in good object-oriented software design.

#### 3.1.1 S - Single Responsibility Principle

This states that classes should only have one responsibility, or put another way, they should only have a single reason to change. This fits nicely with the Unix philosophy of lots of small tools, doing one thing well. Classes that only do one thing are much easier to test and debug, and they are less likely to surprise you. You don't want a method call to a Validator class updating db records. Here's an example of an SRP violation, the likes of which you'd commonly see in an application based on the [ActiveRecord pattern](http://en.wikipedia.org/wiki/Active_record_pattern).

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
    public function save() {}
}
```
    

So this is a pretty basic [entity](http://lostechies.com/jimmybogard/2008/05/21/entities-value-objects-aggregates-and-roots/) model. One of these things doesn't belong here though. An entity model's only responsibility should be behavior related to the entity it's representing, it shouldn't be responsible for persisting itself.

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
}
class DataStore
{
    public function save(Model $model) {}
}
```

This is better. The Person model is back to only doing one thing, and the save behavior has been moved to a persistence object instead. Note also that I only type hinted on Model, not Person. We'll come back to that when we get to the L and D parts of SOLID.

#### 3.1.2 O - Open Closed Principle

There's an awesome test for this that pretty well sums up what this principle is about: think of a feature to implement, probably the most recent one you worked on or are working on. Can you implement that feature in your existing codebase SOLELY by adding new classes and not changing any existing classes in your system? Your configuration and wiring code gets a bit of a pass, but in most systems this is surprisingly difficult. You have to rely a lot on polymorphic dispatch and most codebases just aren't set up for that. If you're interested in that there's a good Google talk up on YouTube about [polymorphism and writing code without Ifs](https://www.youtube.com/watch?v=4F72VULWFvc) that digs into it further. As a bonus, the talk is given by [Miško Hevery](http://misko.hevery.com/), whom many may know as the creator of [AngularJs](https://angularjs.org/).

#### 3.1.3 L - Liskov Substitution Principle

This principle is named for [Barbara Liskov](http://en.wikipedia.org/wiki/Barbara_Liskov), and is printed below:

> "Objects in a program should be replaceable with instances of their subtypes without altering the correctness of that program."

That all sounds well and good, but it's more clearly illustrated with an example.

```php
abstract class Shape
{
    public function getHeight();
    public function setHeight($height);
    public function getLength();
    public function setLength($length);
}
```   

This is going to represent our basic four-sided shape. Nothing fancy here.

```php
class Square extends Shape
{
    protected $size;
    public function getHeight() {
        return $this->size;
    }
    public function setHeight($height) {
        $this->size = $height;
    }
    public function getLength() {
        return $this->size;
    }
    public function setLength($length) {
        $this->size = $length;
    }
}
```

Here's our first shape, the Square. Pretty straightforward shape, right? You can assume that there's a constructor where we set the dimensions, but you see here from this implementation that the length and height are always going to be the same. Squares are just like that.

```php
class Rectangle extends Shape
{
    protected $height;
    protected $length;
    public function getHeight() {
        return $this->height;
    }
    public function setHeight($height) {
        $this->height = $height;
    }
    public function getLength() {
        return $this->length;
    }
    public function setLength($length) {
        $this->length = $length;
    }
}
```

So here we have a different shape. Still has the same method signatures, it's still a four sided shape, but what if we start trying to use them in place of one another? Now all of a sudden if we change the height of our Shape, we can no longer assume that the length of our shape will match. We've violated the contract that we had with the user when we gave them our Square shape.

This is a textbook example of a violation of the LSP and we need this type of a principle in place to make the best use of a type system. Even [duck typing](http://en.wikipedia.org/wiki/Duck_typing) won't tell us if the underlying behavior is different, and since we can't know that without seeing it break, it's best to make sure that it isn't different in the first place.

#### 3.1.3 I - Interface Segregation Principle

This principle says to favor many small, fine grained interfaces vs. one large one. Interfaces should be based on behavior rather than "it's one of these classes". Think of interfaces that come with PHP. Traversable, Countable, Serializable, things like that. They advertise capabilities that the object possesses, not what it inherits from. So keep your interfaces small. You don't want an interface to have 30 methods on it, 3 is a much better goal.

#### 3.1.4 D - Dependency Inversion Principle

You've probably heard about this in other places that talked about [Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection), but Dependency Inversion and Dependency Injection aren't quite the same thing. Dependency inversion is really just a way of saying that you should depend on abstractions in your system and not on its details. Now what does that mean to you on a day to day basis?

> Don't directly use mysqli\_query() all over your code, use something like DataStore->query() instead.

The core of this principle is actually about abstractions. It's more about saying "use a database adapter" instead of depending on direct calls to things like mysqli\_query. If you're directly using mysqli\_query in half your classes then you're tying everything directly to your database. Nothing for or against MySQL here, but if you are using mysqli\_query, that type of low level detail should be hidden away in only one place and then that functionality should be exposed via a generic wrapper.

Now I know this is kind of a hackneyed example if you think about it, because the number of times you're going to actually completely change your database engine after your product is in production are very, very low. I picked it because I figured people would be familiar with the idea from their own code. Also, even if you have a database that you know you're sticking with, that abstract wrapper object allows you to fix bugs, change behavior, or implement features that you wish your chosen database had. It also makes unit testing possible where low level calls wouldn't.

4 Object calisthenics
---------------------

This isn't a full dive into these principles, but the first two are easy to remember, provide good value, and can be immediately applied to just about any codebase.

### 4.1 No more than one level of indentation per method

This is a helpful way to think about decomposing methods into smaller chunks, leaving you with code that's clearer and more self-documenting. The more levels of indentation you have, the more the method is doing and the more state you have to keep track of in your head while you're working with it.

Right away I know people will object to this, but this is just a guideline/heuristic, not a hard and fast rule. I'm not expecting anyone to enforce PHP\_CodeSniffer rules for this (although [people have](https://github.com/object-calisthenics/phpcs-calisthenics-rules)).

Let's run through a quick sample of what this might look like:

```php
public function transformToCsv($data)
{
    $csvLines = array();
    $csvLines[] = implode(',', array_keys($data[0]));
    foreach ($data as $row) {
        if (!$row) {
            continue;
        }
        $csvLines[] = implode(',', $row);
    }
    return $csvLines;
}
```

While this isn't terrible code (it's technically correct, testable, etc...) we can do a lot more to make this clear. How would we reduce the levels of nesting here?

We know we need to vastly simplify the contents of the foreach loop (or remove it entirely) so let's start there.

```php
if (!$row) {
    continue;
}
```   

This first bit is easy. All this is doing is ignoring empty rows. We can shortcut this entire process by using a built-in PHP function before we even get to the loop.

```php
$data = array_filter($data);
foreach ($data as $row) {
    $csvLines[] = implode(',', $row);
}
```

We now have our single level of nesting. But looking at this, all we are doing is applying a function to each item in an array. We don't even need the foreach loop to do that.

```php
$data = array_filter($data);
$csvLines = array_map(function($row) {
    return implode(',', $row);
}, $data);
```

Now we have no nesting at all, and the code will likely be faster since we're doing all the looping with native C functions instead of PHP. We do have to engage in a bit of trickery to pass the comma to `implode` though, so you could make the argument that stopping at the previous step is much more understandable.

### 4.2 Try not to use `else`

This really deals with two main ideas. The first one is multiple return statements from a method. If you have enough information do make a decision about the method's result, go ahead make that decision and return. The second is an idea known as [Guard Clauses](http://c2.com/cgi/wiki?GuardClause). These are basically validation checks combined with early returns, usually near the top of a method. Let me show you what I mean.

```php
public function addThreeInts($first, $second, $third) {
    if (is_int($first)) {
        if (is_int($second)) {
            if (is_int($third)) {
                $sum = $first + $second + $third;
            } else {
                return null;
            }
        } else {
            return null;
        }
    } else {
        return null;
    }
    return $sum;
}
```

So this is pretty straightforward again, it adds 3 ints together and returns the result, or `null` if any of the parameters are not an integer. Ignoring the fact that we could combine all those checks onto a single line with AND operators, I think you can see how the nested if/else structure makes the code harder to follow. Now look at this example instead.

```php
public function addThreeInts($first, $second, $third) {
    if (!is_int($first)) {
        return null;
    }
    if (!is_int($second)) {
        return null;
    }
    if (!is_int($third)) {
        return null;
    }
    return $first + $second + $third;
}
```   

To me this example is much easier to follow. Here we're using guard clauses to verify our initial assertions about the parameters we're passing and immediately exiting the method if they don't pass. We also no longer have the intermediate variable to track the sum all the way through the method. In this case we've verified that we're already on the happy path and we can just do what we came here to do. Again we could just do all those checks in one `if` but the principle should be clear.

5 Unit testing
--------------

Unit testing is the practice of writing small tests that verify behavior in your code. They are almost always written in the same language as the code (in this case PHP) and are intended to be fast enough to run at any time. They are extremely valuable as a tool to improve your code. Other than the obvious benefits of ensuring that your code is doing what you think it is, unit testing can provide very useful design feedback as well. If a piece of code is difficult to test, it often showcases design problems. They also give you a safety net against regressions, and that allows you to refactor much more often and evolve your code to a cleaner design.

### 5.1 Tools

There are several unit testing tools out there in PHP, but far and away the most common is [PHPUnit](https://phpunit.de/). You can install it by downloading a [PHAR](http://php.net/manual/en/intro.phar.php) file [directly](https://phar.phpunit.de/phpunit.phar), or install it with composer. Since we are using composer for everything else, we'll show that method. Also, since PHPUnit is not likely going to be deployed to production, we can install it as a dev dependency with the following command:

```bash
composer require --dev phpunit/phpunit
```

### 5.2 Tests are a specification

The most important role of unit tests in your code is to provide an executable specification of what the code is supposed to do. Even if the test code is wrong, or the code has bugs, the knowledge of what the system is _supposed_ to do is priceless.

### 5.3 Write your tests first

If you've had the chance to see a set of tests written before the code and one written after the code was finished, they're strikingly different. The "after" tests are much more concerned with the implementation details of the class and making sure they have good line coverage, whereas the "before" tests are more about verifying the desired external behavior. That's really what we care about with unit tests anyway, is making sure the class exhibits the right behavior. Implementation-focused tests actually make refactoring harder because they break if the internals of the classes change, and you've just cost yourself the information hiding benefits of OOP.

### 5.4 What makes a good unit test

Good unit tests share a lot of the following characteristics:

*   Fast - should run in milliseconds.
*   No network access - should be able to turn off wireless/unplug and all the tests still pass.
*   Limited file system access - this adds to speed and flexibility if deploying code to other environments.
*   No database access - avoids costly setup and teardown activities.
*   Test only one thing at a time - a unit test should have only one reason to fail.
*   Well-named - see 5.2 above.
*   Mostly fake objects - the only "real" objects in unit tests should be the object we're testing and simple value objects. The rest should be some form of [test double](https://phpunit.de/manual/current/en/test-doubles.html)

There are reasons to go against some of these but as general guidelines they will serve you well.

### 5.5 When testing is painful

> Unit testing forces you to feel the pain of bad design up front - Michael Feathers

When you're writing unit tests, you're forcing yourself to actually use the class to accomplish things. If you write tests at the end, or worse yet, just chuck the code over the wall for QA or whoever to write tests, you don't get any feedback about how the class actually behaves. If we're writing tests, and the class is a real pain to use, we'll find out while we're writing it, which is nearly the cheapest time to fix it.

If a class is hard to test, it's a design flaw. Different flaws manifest themselves in different ways, though. If you have to do a ton of mocking, your class probably has too many dependencies, or your methods are doing too much. The more setup you have to do for each test, the more likely it is that your methods are doing too much. If you have to write really convoluted test scenarios in order to exercise behavior, the class's methods are probably doing too much. If you have to dig inside a bunch of private methods and state to test things, maybe there's another class trying to get out. Unit testing is very good at exposing "iceberg classes" where 80% of what the class does is hidden away in protected or private code. I used to be a big fan of making as much as possible protected, but now I realized I was just making my individual classes responsible for too much, and the real solution was to break the class up into smaller pieces.

> **Written by Brian Fenton** - Brian Fenton has been a PHP developer for 8 years in the Midwest and the Bay Area, currently at Thismoment. He focuses on code craftsmanship and design principles. Blog at www.brianfenton.us, Twitter at @brianfenton. When he's not busy being a dad, he enjoys food, beer, gaming, and learning.