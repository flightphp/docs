# Why a Framework?

Some programmers are vehemently opposed to using frameworks. They argue that frameworks are bloated, slow, and difficult to learn. 
They say that frameworks are unnecessary and that you can write better code without them. 
There are certainly some valid points to be made about the disadvantages of using frameworks. However, there are also many advantages to using frameworks. 

## Reasons to Use a Framework

Here are a few reasons why you might want to consider using a framework:

- **Rapid Development**: Frameworks provide a lot of functionality out of the box. This means that you can build web applications more quickly. You don't have to write as much code because the framework provides a lot of the functionality that you need.
- **Consistency**: Frameworks provide a consistent way of doing things. This makes it easier for you to understand how the code works and makes it easier for other developers to understand your code. If you have it script by script, you might lose consistency between scripts, especially if you are working with a team of developers.
- **Security**: Frameworks provide security features that help protect your web applications from common security threats. This means that you don't have to worry as much about security because the framework takes care of a lot of it for you.
- **Community**: Frameworks have large communities of developers who contribute to the framework. This means that you can get help from other developers when you have questions or problems. It also means that there are a lot of resources available to help you learn how to use the framework.
- **Best Practices**: Frameworks are built using best practices. This means that you can learn from the framework and use the same best practices in your own code. This can help you become a better programmer. Sometimes you don't know what you don't know and that can bite you in the end.
- **Extensibility**: Frameworks are designed to be extended. This means that you can add your own functionality to the framework. This allows you to build web applications that are tailored to your specific needs.

Flight is a micro-framework. This means that it is small and lightweight. It doesn't provide as much functionality as larger frameworks like Laravel or Symfony. 
However, it does provide a lot of the functionality that you need to build web applications. It is also easy to learn and use. 
This makes it a good choice for building web applications quickly and easily. If you are new to frameworks, Flight is a great beginner framework to start with. 
It will help you learn about the advantages of using frameworks without overwhelming you with too much complexity. 
After you have some experience with Flight, it will be easier to move onto more complex frameworks like Laravel or Symfony, 
however Flight can still make a successful robust application.

## What is Routing?

Routing is the core of the Flight framework, but what is it exactly? Routing is the process of taking a URL and matching it to a specific function in your code. 
This is how you can make your website do different things based on the URL that is requested. For example, you might want to show a user's profile when they 
visit `/user/1234`, but show a list of all users when they visit `/users`. This is all done through routing.

It might work something like this:

- A user goes to your browser and types in `http://example.com/user/1234`.
- The server receives the request and looks at the URL and passes it to your Flight application code.
- Let's say in your Flight code you have something like `Flight::route('/user/@id', [ 'UserController', 'viewUserProfile' ]);`. Your Flight application code looks at the URL and sees that it matches a route you've defined, and then runs the code that you've defined for that route.  
- The Flight router will then run and call the `viewUserProfile($id)` method in the `UserController` class, passing in the `1234` as the `$id` arg in the method.
- The code in your `viewUserProfile()` method will then run and do whatever you've told it to do. You might end up echoing out some HTML for the user's profile page, or if this is a RESTful API, you might echo out a JSON response with the user's information.
- Flight wraps this up in a pretty bow, generates the response headers and sends it back to the user's browser.
- The user is filled with joy and gives themselves a warm hug!

### And Why is it Important?

Having a proper centralized router can actually make your life dramatically easier! It just might be hard to see at first. Here are a few reasons why:

- **Centralized Routing**: You can keep all of your routes in one place. This makes it easier to see what routes you have and what they do. It also makes it easier to change them if you need to.
- **Route Parameters**: You can use route parameters to pass in data to your route methods. This is a great way to keep your code clean and organized.
- **Route Groups**: You can group routes together. This is great for keeping your code organized and for applying [middleware](middleware) to a group of routes.
- **Route Aliasing**: You can assign an alias to a route, so that the URL can dynamically be generated later in your code (like a template for instance). Ex: instead of hardcoding `/user/1234` in your code, you could instead reference the alias `user_view` and pass in the `id` as a parameter. This makes it wonderful in case you decide to change it to `/admin/user/1234` later on. You won't have to change all your hard coded urls, just the URL attached to the route.
- **Route Middleware**: You can add middleware to your routes. Middleware is incredibly powerful at adding specific behaviors to your application like authenticating  that a certain user can access a route or group of routes.

I'm sure you're familiar with the script by script way of creating a website. You might have a file called `index.php` that has a bunch of `if` 
statements to check the URL and then run a specific function based on the URL. This is a form of routing, but it's not very organized and it can 
get out of hand quickly. Flight's routing system is a much more organized and powerful way to handle routing.

This?

```php

// /user/view_profile.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	viewUserProfile($id);
}

// /user/edit_profile.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	editUserProfile($id);
}

// etc...
```

Or this?

```php

// index.php
Flight::route('/user/@id', [ 'UserController', 'viewUserProfile' ]);
Flight::route('/user/@id/edit', [ 'UserController', 'editUserProfile' ]);

// In maybe your app/controllers/UserController.php
class UserController {
	public function viewUserProfile($id) {
		// do something
	}

	public function editUserProfile($id) {
		// do something
	}
}
```

Hopefully you can start to see the benefits of using a centralized routing system. It's a lot easier to manage and understand in the long run!

## Requests and Responses

Flight provides a simple and easy way to handle requests and responses. This is the core of what a web framework does. It takes in a request 
from a user's browser, processes it, and then sends back a response. This is how you can build web applications that do things like show a user's 
profile, let a user log in, or let a user post a new blog post.

### Requests

A request is what a user's browser sends to your server when they visit your website. This request contains information about what the user 
wants to do. For example, it might contain information about what URL the user wants to visit, what data the user wants to send to your server, 
or what kind of data the user wants to receive from your server. It's important to know that a request is read-only. You can't change the request, 
but you can read from it.

Flight provides a simple way to access information about the request. You can access information about the request using the `Flight::request()` 
method. This method returns a `Request` object that contains information about the request. You can use this object to access information about 
the request, such as the URL, the method, or the data that the user sent to your server.

### Responses

A response is what your server sends back to a user's browser when they visit your website. This response contains information about what your 
server wants to do. For example, it might contain information about what kind of data your server wants to send to the user, what kind of data 
your server wants to receive from the user, or what kind of data your server wants to store on the user's computer.

Flight provides a simple way to send a response to a user's browser. You can send a response using the `Flight::response()` method. This method 
takes a `Response` object as an argument and sends the response to the user's browser. You can use this object to send a response to the user's 
browser, such as HTML, JSON, or a file. Flight helps you auto generate some parts of the response to make things easy, but ultimately you have 
control over what you send back to the user.

