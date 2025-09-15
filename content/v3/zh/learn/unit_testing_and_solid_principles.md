> _本文最初于 2015 年发布在 [Airpair](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) 上。所有功劳归功于 Airpair 和最初撰写此文章的 Brian Fenton，尽管网站已不再可用，该文章仅存在于 [Wayback Machine](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) 中。本文已添加到站点上，旨在为 PHP 社区提供学习和教育目的。_

1 设置和配置
-------------

### 1.1 保持最新

从一开始就明确这一点——野外中惊人少量的 PHP 安装是当前的或保持更新的。这可能是由于共享托管限制、默认设置无人更改，或者没有时间/预算进行升级测试，简陋的 PHP 二进制文件往往被遗忘。因此，一个需要更多强调的最佳实践是始终使用当前版本的 PHP（本文写作时为 5.6.x）。此外，定期升级 PHP 本身以及任何扩展或供应商库也很重要。升级能带来新语言功能、改进的速度、更低的内存使用和安全更新。升级频率越高，过程就越不痛苦。

### 1.2 设置合理的默认值

PHP 在其 _php.ini.development_ 和 _php.ini.production_ 文件中设置了不错的默认值，但我们可以做得更好。首先，它们没有为我们设置日期/时区。从分发角度来看这是合理的，但没有时区，PHP 在调用任何日期/时间相关函数时会抛出 E_WARNING 错误。下面是一些推荐设置：

*   date.timezone - 从[支持的时区列表](http://php.net/manual/en/timezones.php)中选择
*   session.save_path - 如果我们使用文件进行会话而不是其他保存处理程序，将其设置为 _/tmp_ 外的某个位置。在共享托管环境中，将其保留为 _/tmp_ 可能有风险，因为 _/tmp_ 通常权限宽松。即使设置了 sticky-bit，具有访问权限的人也可以列出该目录的内容，从而获知所有活跃的会话 ID。
*   session.cookie_secure - 如果通过 HTTPS 服务 PHP 代码，请毫不犹豫地启用此选项。
*   session.cookie_httponly - 设置此选项以防止 PHP 会话 cookie 通过 JavaScript 访问
*   更多... 使用工具如 [iniscan](https://github.com/psecio/iniscan) 测试配置中的常见漏洞

### 1.3 扩展

禁用（或至少不启用）您不会使用的扩展（如数据库驱动程序）也是个好主意。要查看哪些已启用，运行 `phpinfo()` 命令或转到命令行并运行以下命令。

```bash
$ php -i
``` 

信息相同，但 phpinfo() 添加了 HTML 格式。CLI 版本更容易通过 grep 管道传输以查找特定信息。例如。

```bash
$ php -i | grep error_log
```

不过，此方法有一个警告：网页版本和 CLI 版本的 PHP 设置可能不同。

2 使用 Composer
--------------

这可能令人惊讶，但编写现代 PHP 的最佳实践之一是编写更少的代码。虽然事实是练习编程是提高技能的最好方法，但 PHP 领域已经解决了大量问题，如路由、基本输入验证库、单位转换、数据库抽象层等... 只是去 [Packagist](https://www.packagist.org/) 浏览一下。您可能会发现您要解决的问题的很大一部分已经编写并经过测试。

虽然自己编写所有代码很诱人（作为学习体验编写自己的框架或库也没问题），但您应该克服“非我发明不使用”的感觉，从而节省大量时间和麻烦。遵循 PIE 原则——自豪地使用他处发明的代码。而且，如果您选择编写自己的东西，除非它与现有产品有显著不同或更好，否则不要发布它。

[Composer](https://www.getcomposer.org/) 是 PHP 的包管理器，类似于 Python 中的 pip、Ruby 中的 gem 和 Node 中的 npm。它允许您定义一个 JSON 文件，列出代码的依赖项，并尝试通过下载和安装必要的代码包来解决这些要求。

### 2.1 安装 Composer

我们假设这是一个本地项目，因此为当前项目安装 Composer 实例。导航到您的项目目录并运行此命令：
```bash
$ curl -sS https://getcomposer.org/installer | php
```

请记住，直接将任何下载管道传输到脚本解释器（sh、ruby、php 等）存在安全风险，因此请阅读安装代码并确保您对其感到舒适后再运行此类命令。

出于方便（如果您更喜欢键入 `composer install` 而不是 `php composer.phar install`），您可以使用此命令全局安装 Composer 的单个副本：

```bash
$ mv composer.phar /usr/local/bin/composer
$ chmod +x composer
```

根据您的文件权限，您可能需要使用 `sudo` 运行这些命令。

### 2.2 使用 Composer

Composer 有两个主要类别依赖项：“require”和“require-dev”。列为“require”的依赖项 everywhere 都会安装，但“require-dev”依赖项仅在特定请求时安装。这些通常是开发时使用的工具，例如 [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)。下面显示了如何安装 [Guzzle](http://docs.guzzlephp.org/en/latest/)，一个流行的 HTTP 库的示例。

```bash
$ php composer.phar require guzzle/guzzle
```

要仅为开发目的安装工具，请添加 --dev 标志：

```bash
$ php composer.phar require --dev 'sebastian/phpcpd'
```

这会安装 [PHP Copy-Paste Detector](https://github.com/sebastianbergmann/phpcpd) 作为开发-only 依赖项，这是另一个代码质量工具。

### 2.3 安装 vs 更新

首次运行 `composer install` 时，它会根据 _composer.json_ 文件安装我们需要的库及其依赖项。完成后，Composer 会创建一个锁文件，预料之中名为 _composer.lock_。此文件包含 Composer 为我们找到的依赖项及其确切版本的列表，包括哈希。然后，未来每次运行 `composer install`，它会查看锁文件并安装那些确切版本。

`composer update` 有点不同。它会忽略 _composer.lock_ 文件（如果存在），并尝试找到每个依赖项的最更新版本，同时仍满足 _composer.json_ 中的约束。然后，它会在完成时写入一个新的 _composer.lock_ 文件。

### 2.4 自动加载

Composer install 和 Composer update 都会为我们生成一个[自动加载器](https://getcomposer.org/doc/04-schema.md#autoload)，告诉 PHP 在哪里找到我们刚刚安装的库所需的所有文件。要使用它，只需添加此行（通常到每个请求执行的引导文件）：
```php
require 'vendor/autoload.php';
```

3 遵循良好设计原则
-------------------------------

### 3.1 SOLID

SOLID 是一个助记符，用于提醒我们良好面向对象软件设计中的五个关键原则。

#### 3.1.1 S - 单一责任原则

这指出类应该只有一个责任，或者换句话说，它们应该只有一个变化原因。这与 Unix 哲学相符，即许多小型工具，每一个都做得很好。只有一个功能的类更容易测试和调试，而且它们不太可能让您感到惊讶。您不希望调用 Validator 类的函数更新数据库记录。这是基于 [ActiveRecord 模式](http://en.wikipedia.org/wiki/Active_record_pattern) 的应用程序中常见 SRP 违规示例。

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
    

这是一个相当基本的[实体](http://lostechies.com/jimmybogard/2008/05/21/entities-value-objects-aggregates-and-roots/) 模型。不过，其中一个东西不属于这里。实体模型的唯一责任应该是与它所代表的实体相关行为，它不应该负责自身持久化。

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

这更好。Person 模型恢复到只做一件事，而保存行为已移动到持久化对象中。请注意，我只在 Model 上进行了类型提示，而不是 Person。我们将在讨论 SOLID 的 L 和 D 部分时返回这一点。

#### 3.1.2 O - 开闭原则

有一个很棒的测试可以很好地总结这个原则：考虑一个要实现的功能，可能是在您最近处理或正在处理的那个。您能在现有代码库中仅通过添加新类而无需更改任何现有类来实现该功能吗？您的配置和 wiring 代码可以稍作例外，但在大多数字系中，这出奇地困难。您必须依赖多态分发，而大多数代码库根本没有为此做好准备。如果您感兴趣，可以在 YouTube 上找到一个关于[多态性和编写无 If 代码](https://www.youtube.com/watch?v=4F72VULWFvc) 的优秀 Google 演讲，进一步深入探讨。作为奖励，演讲由[Miško Hevery](http://misko.hevery.com/) 进行，许多人可能知道他是 [AngularJs](https://angularjs.org/) 的创建者。

#### 3.1.3 L - 里氏替换原则

这个原则以[Barbara Liskov](http://en.wikipedia.org/wiki/Barbara_Liskov) 命名，并如下所述：

> "程序中的对象应该能够用其子类型的实例替换，而不改变程序的正确性。"

这听起来不错，但用示例更清楚地说明。

```php
abstract class Shape
{
    public function getHeight();
    public function setHeight($height);
    public function getLength();
    public function setLength($length);
}
```   

这是我们的基本四边形。没有花哨的东西。

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

这是我们的第一个形状，正方形。相当直观的形状，对吗？您可以假设有一个构造函数设置尺寸，但从这个实现中可以看到，长度和高度总是相同的。正方形就是这样。

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

所以这里我们有一个不同的形状。仍然具有相同的函数签名，它仍然是一个四边形，但如果我们开始尝试相互替换它们呢？现在突然如果我们改变 Shape 的高度，我们不能再假设形状的长度会匹配。我们违反了在使用 Square 形状时与用户达成的约定。

这是 LSP 违规的教科书示例，我们需要这种原则来最好地利用类型系统。即使是[鸭子类型](http://en.wikipedia.org/wiki/Duck_typing) 也不会告诉我们底层行为是否不同，而且既然我们无法在不看到它崩溃的情况下知道，因此最好确保它不是不同的。

#### 3.1.3 I - 接口隔离原则

这个原则建议优先使用许多小型、细粒度的接口，而不是一个大型接口。接口应该基于行为，而不是“它是这些类之一”。考虑 PHP 自带的接口。Traversable、Countable、Serializable 之类的东西。它们广告对象所拥有的功能，而不是它从哪里继承。所以保持接口小。您不希望接口上有 30 个函数，3 个是一个更好的目标。

#### 3.1.4 D - 依赖倒置原则

您可能在其他地方听说过这与[依赖注入](http://en.wikipedia.org/wiki/Dependency_injection) 相关，但依赖倒置和依赖注入并不完全相同。依赖倒置实际上只是说您应该依赖系统的抽象而不是其细节。那么这对您日常工作意味着什么？

> 不要在代码中到处直接使用 mysqli_query()，而是使用像 DataStore->query() 这样的东西。

这个原则的核心实际上是关于抽象的。它更多的是说“使用数据库适配器”而不是依赖于像 mysqli_query 这样的直接调用。如果您在半数类中直接使用 mysqli_query，那么您就把一切直接绑定到数据库。没有针对 MySQL 的内容，但如果您使用 mysqli_query，这种低级细节应该只隐藏在一个地方，然后通过通用包装器公开功能。

我知道这是一个陈词滥调的示例，因为在产品投入生产后，您实际完全更改数据库引擎的次数非常、非常低。我选择它是因为我认为人们会从他们自己的代码中熟悉这个想法。而且，即使您有一个您知道要坚持的数据库，该抽象包装器对象允许您修复错误、更改行为或实现您希望选择的数据库具有的功能。它还使单元测试成为可能，而低级调用不会。

4 对象体操
---------------------

这不是对这些原则的完整深入探讨，但前两个易于记住，提供良好价值，并且可以立即应用于几乎任何代码库。

### 4.1 方法中不超过一层缩进

这是一个帮助将方法分解为更小块的有用方式，从而让代码更清晰和更自文档化。缩进层越多，方法做的就越多，您在处理时必须在脑海中跟踪的状态就越多。

我马上知道人们会反对，但这只是一个指导/启发式规则，不是硬性规定。我不期望任何人强制 PHP_CodeSniffer 规则来执行这个（尽管[有人有](https://github.com/object-calisthenics/phpcs-calisthenics-rules)）。

让我们快速浏览一个样本，看看这可能是什么样子：

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

虽然这不是糟糕的代码（它在技术上是正确的、可测试的等...），我们可以通过更多方式使它更清晰。我们如何减少这里的嵌套层？

我们知道需要大大简化 foreach 循环的内容（或完全删除它），所以让我们从那里开始。

```php
if (!$row) {
    continue;
}
```   

这第一部分很容易。这只是忽略空行。我们可以通过在进入循环前使用内置 PHP 函数来快捷方式这个过程。

```php
$data = array_filter($data);
foreach ($data as $row) {
    $csvLines[] = implode(',', $row);
}
```

我们现在有了单层嵌套。但看这个，我们只是在数组中的每个项目上应用一个函数。我们甚至不需要 foreach 循环来做那个。

```php
$data = array_filter($data);
$csvLines = array_map(function($row) {
    return implode(',', $row);
}, $data);
```

现在我们根本没有嵌套，而且代码可能会更快，因为我们使用的是本机 C 函数而不是 PHP 来循环。我们必须进行一些技巧将逗号传递给 `implode`，所以您可以说停止在上一部更易理解。

### 4.2 尽量不使用 `else`

这主要涉及两个想法。首先是从方法中多个返回语句。如果您有足够信息来决定方法的结果，请继续做出决定并返回。其次是一个称为[Guard Clauses](http://c2.com/cgi/wiki?GuardClause) 的想法。这些基本上是与早期返回结合的验证检查，通常在方法顶部附近。让我向您展示我的意思。

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

所以这是一个相当直观的函数，它将 3 个整数相加并返回结果，或者如果任何参数不是整数则返回 `null`。忽略我们可以使用 AND 运算符将所有这些检查组合到一行的事实，我想您可以看到嵌套 if/else 结构如何让代码更难跟随。现在看看这个示例。

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

对我来说，这个示例更容易跟随。这里我们使用 guard clauses 来验证我们对传递参数的初始断言，并在它们不通过时立即退出方法。我们也不再有中间变量来跟踪总和贯穿整个方法。在这种情况下，我们已经验证了我们已经在快乐路径上，我们可以只做我们来这里要做的事。我们可以用一个 `if` 来做所有这些检查，但原则应该很清楚。

5 单元测试
--------------

单元测试是编写小测试来验证代码行为的实践。这些测试几乎总是用与代码相同的语言编写（在本例中为 PHP），并且旨在足够快以随时运行。它们作为改进代码的工具非常有价值。除了确保代码按预期工作的明显好处外，单元测试还可以提供非常有用的设计反馈。如果一段代码难以测试，通常会突出设计问题。它们还为您提供了一个防止回归的安全网，这让您可以更频繁地重构并将代码演变为更清洁的设计。

### 5.1 工具

PHP 中有几个单元测试工具，但最常见的是 [PHPUnit](https://phpunit.de/)。您可以通过下载 [PHAR](http://php.net/manual/en/intro.phar.php) 文件[直接](https://phar.phpunit.de/phpunit.phar) 安装它，或者用 Composer 安装。由于我们使用 Composer 进行其他所有操作，我们将展示该方法。而且，由于 PHPUnit 不太可能部署到生产环境，我们可以将其作为开发依赖项使用以下命令安装：

```bash
composer require --dev phpunit/phpunit
```

### 5.2 测试是一个规范

单元测试在代码中最重要的作用是提供一个可执行规范，说明代码应该做什么。即使测试代码错误，或者代码有错误，知道系统应该做什么的知识是无价的。

### 5.3 先写测试

如果您有机会看到一组测试在代码之前编写和一个在代码完成后编写，它们会截然不同。“后”测试更关注类的实现细节和确保良好的行覆盖率，而“前”测试更关注验证所需的外部分行为。这确实是我们用单元测试关心的，即确保类表现出正确行为。以实现为中心的测试实际上会使重构更困难，因为如果类的内部发生变化，它们会中断，而您刚刚失去了 OOP 的信息隐藏好处。

### 5.4 什么是好的单元测试

好的单元测试共享以下许多特征：

*   快速 - 应该在毫秒内运行。
*   无网络访问 - 应该能够在关闭无线/拔掉网络的情况下所有测试仍通过。
*   有限的文件系统访问 - 这有助于速度和灵活性，如果将代码部署到其他环境。
*   无数据库访问 - 避免代价高昂的设置和拆卸活动。
*   一次只测试一件事 - 单元测试应该只有一个失败原因。
*   命名良好 - 见 5.2 以上。
*   主要是假对象 - 单元测试中唯一的“真实”对象应该是我们正在测试的对象和简单值对象。其余应该是某种形式的[测试替身](https://phpunit.de/manual/current/en/test-doubles.html)

有理由违反其中一些，但作为一般指南，它们会为您服务。

### 5.5 测试痛苦时

> 单元测试会让您提前感受到糟糕设计带来的痛苦 - Michael Feathers

当您编写单元测试时，您是在强迫自己实际使用类来完成事情。如果您在最后编写测试，或者更糟的是，只是将代码扔给 QA 或其他人编写测试，您不会得到关于类实际行为的任何反馈。如果我们在编写时编写测试，并且类使用起来很痛苦，我们会在编写时发现，这几乎是最便宜的修复时间。

如果一个类难以测试，那就是设计缺陷。不同的缺陷以不同的方式表现出来。如果您必须进行大量模拟，您的类可能有太多依赖项，或者方法做了太多事情。每个测试必须做的设置越多，方法做的就越多。如果您必须编写非常复杂的测试场景来行使行为，类的方法可能做了太多事情。如果您必须挖掘一堆私有方法和状态来测试东西，也许还有另一个类试图出来。单元测试非常擅长暴露“冰山类”，其中 80% 的工作隐藏在受保护或私有的代码中。我曾经是让尽可能多东西受保护的忠实粉丝，但现在我意识到我只是让我的单个类负责太多，真正的解决方案是将类分解成更小的部分。

> **由 Brian Fenton 撰写** - Brian Fenton 在中西部和湾区担任 PHP 开发人员已有 8 年，目前在 Thismoment。他专注于代码工艺和设计原则。博客在 www.brianfenton.us，Twitter 在 @brianfenton。当他不忙着当爸爸时，他喜欢食物、啤酒、游戏和学习。