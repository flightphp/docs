## 为什么使用框架？

一些程序员强烈反对使用框架。他们认为框架臃肿、缓慢且难以学习。他们说框架是不必要的，而且没有框架你也可以编写更好的代码。关于使用框架的劣势确实存在一些合理的观点。然而，使用框架也有许多优势。

## 使用框架的原因

以下是您可能考虑使用框架的几个原因：

- **快速开发**：框架提供了很多开箱即用的功能。这意味着您可以更快地构建web应用程序。您不必编写太多代码，因为框架提供了许多您需要的功能。
- **一致性**：框架提供了一种一致的做事方式。这使您更容易理解代码的运作方式，也使其他开发人员更容易理解您的代码。如果您逐个脚本地工作，特别是与一组开发人员合作时，您可能会在脚本之间失去一致性。
- **安全性**：框架提供安全功能，帮助保护您的web应用程序免受常见安全威胁。这意味着您不必过多担心安全性，因为框架已经为您处理了很多内容。
- **社区**：框架拥有庞大的开发人员社区，他们为框架做出贡献。这意味着在遇到问题或困难时，您可以从其他开发人员那里获得帮助。这也意味着有很多资源可供您学习如何使用框架。
- **最佳实践**：框架使用最佳实践构建。这意味着您可以从框架中学习，并在自己的代码中使用相同的最佳实践。这可以帮助您成为更好的程序员。有时候您不知道自己不知道的事情会伤害到您。
- **可扩展性**：框架被设计为可扩展的。这意味着您可以将自己的功能添加到框架中。这使您能够构建适合您特定需求的web应用程序。

Flight是一个微型框架。这意味着它小巧轻便。它提供的功能不如像Laravel或Symfony这样的大型框架多。但是，它确实提供了构建web应用程序所需的许多功能。它也易于学习和使用。这使它成为快速轻松构建web应用程序的好选择。如果您是框架新手，Flight是一个很好的初学者框架。它将帮助您了解使用框架的优势，而不会因为复杂性过多而让您感到不知所措。在您对Flight有了一些经验之后，将更容易过渡到像Laravel或Symfony这样的更复杂的框架，但Flight仍然可以创建成功的强大应用程序。

## 什么是路由？

路由是Flight框架的核心，但它究竟是什么？路由是将URL与代码中特定函数进行匹配的过程。这是您可以根据请求的URL使您的网站执行不同操作的方式。例如，您可能希望当用户访问`/user/1234`时显示用户个人资料，当他们访问`/users`时显示所有用户的列表。这一切都是通过路由完成的。

可能是这样运行的：

- 用户打开浏览器，输入`http://example.com/user/1234`。
- 服务器接收请求并查看URL，将其传递给您的Flight应用程序代码。
- 假设在您的Flight代码中有类似`Flight::route('/user/@id', ['UserController', 'viewUserProfile'])`这样的代码。您的Flight应用程序代码查看URL并发现它与您定义的路由匹配，然后运行您为该路由定义的代码。
- Flight路由器然后会调用`UserController`类中的`viewUserProfile($id)`方法，并将`1234`作为`$id`参数传递给该方法。
- 您的`viewUserProfile()`方法中的代码将运行并执行您告诉它要执行的操作。您可能最终会输出一些HTML，显示用户的个人资料页面，或者如果这是一个RESTful API，您可能会输出一个包含用户信息的JSON响应。
- Flight将这一切打包得漂亮优雅，生成响应标头并将其发送回用户的浏览器。
- 用户充满喜悦，给自己一个温暖的拥抱！

## 为什么它重要？

拥有一个适当的集中式路由器实际上可以极大地简化您的生活！一开始可能有点难以理解。以下是一些原因：

- **集中式路由**：您可以将所有路由保存在一个地方。这使得更容易查看您拥有的路由以及它们的功能。如果需要，更改它们也更容易。
- **路由参数**：您可以使用路由参数向路由方法传递数据。这是保持代码整洁和有组织的好方法。
- **路由组**：您可以将路由分组。这对于保持代码组织良好以及为路由组应用[中间件](middleware)都很有用。
- **路由别名**：您可以为路由分配一个别名，以便稍后在您的代码中动态生成URL（例如模板）。例如：在代码中直接编写`/user/1234`比较刚硬，而通过引用别名`user_view`并将`id`作为参数传递，可以让您稍后轻松生成URL。这在以后决定将其更改为`/admin/user/1234`时非常方便。您不必更改所有硬编码的URL，只需更改与路由关联的URL。
- **路由中间件**：您可以向路由添加中间件。中间件非常有力，可以向您的应用程序添加特定行为，比如验证某个用户能否访问路由或一组路由。

我相信您对逐个脚本创建网站的方式应该很熟悉。您可能有一个名为`index.php`的文件，其中包含许多`if`语句，用于检查URL，然后基于URL运行特定函数。这是一种路由方式，但不是很有组织，而且很快就会变得混乱。Flight的路由系统是处理路由的更有组织也更强大的方式。

这样？

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

// 等等...
```

还是这样？

```php

// index.php
Flight::route('/user/@id', ['UserController', 'viewUserProfile']);
Flight::route('/user/@id/edit', ['UserController', 'editUserProfile']);

// 也许在您的app/controllers/UserController.php中
class UserController {
    public function viewUserProfile($id) {
        // 做些什么
    }

    public function editUserProfile($id) {
        // 做些什么
    }
}
```

希望您能开始看到使用集中式路由系统的好处。长期来看，它更易于管理和理解！

## 请求和响应

Flight提供了一种简单且轻松的处理请求和响应的方式。这是Web框架的核心功能。它接收来自用户浏览器的请求，处理它，然后发送回一个响应。这是您可以构建web应用程序并执行诸如显示用户个人资料、允许用户登录或让用户发布新博客文章之类操作的方式。

### 请求

当用户访问您的网站时，浏览器发送给服务器的内容称为请求。此请求包含关于用户要执行的操作的信息。例如，它可能包含有关用户要访问的URL、用户要发送到您的服务器的数据或用户希望从服务器接收的数据的信息。重要的是要知道请求是只读的。您不能更改请求，但可以从中读取信息。

Flight提供了一种简单的访问请求信息的方式。您可以使用`Flight::request()`方法访问有关请求的信息。该方法返回一个包含有关请求信息的`Request`对象。您可以使用此对象访问有关请求的信息，如URL、方法或用户发送到您的服务器的数据。

### 响应

当用户访问您的网站时，服务器发送回用户浏览器的内容称为响应。此响应包含关于服务器要执行的操作的信息。例如，它可能包含有关服务器希望发送给用户的数据、服务器希望从用户接收的数据或服务器希望存储在用户计算机上的数据的信息。

Flight提供了一种向用户浏览器发送响应的简单方式。您可以使用`Flight::response()`方法发送响应。该方法将一个`Response`对象作为参数，并将响应发送回用户的浏览器。您可以使用此对象向用户的浏览器发送响应，例如HTML、JSON或文件。Flight帮助您自动生成响应的一些部分以使事情变得简单，但最终您可以控制发送给用户的内容。