# WordPress 集成：n0nag0n/wordpress-integration-for-flight-framework

想要在您的 WordPress 站点中使用 Flight PHP？这个插件让一切变得轻而易举！使用 `n0nag0n/wordpress-integration-for-flight-framework`，您可以在 WordPress 安装旁边运行完整的 Flight 应用程序——非常适合构建自定义 API、微服务，甚至是功能齐全的应用程序，而无需离开 WordPress 的舒适环境。

---

## 它能做什么？

- **无缝地将 Flight PHP 与 WordPress 集成**
- 根据 URL 模式将请求路由到 Flight 或 WordPress
- 使用控制器、模型和视图（MVC）组织您的代码
- 轻松设置推荐的 Flight 文件夹结构
- 使用 WordPress 的数据库连接或您自己的连接
- 微调 Flight 和 WordPress 的交互方式
- 简单的管理界面进行配置

## 安装

1. 将 `flight-integration` 文件夹上传到您的 `/wp-content/plugins/` 目录。
2. 在 WordPress 管理后台（插件菜单）中激活该插件。
3. 转到 **设置 > Flight Framework** 来配置插件。
4. 设置供应商路径到您的 Flight 安装（或使用 Composer 安装 Flight）。
5. 配置您的应用程序文件夹路径并创建文件夹结构（插件可以帮助您完成！）。
6. 开始构建您的 Flight 应用程序！

## 使用示例

### 基本路由示例
在您的 `app/config/routes.php` 文件中：

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### 控制器示例

在 `app/controllers/ApiController.php` 中创建一个控制器：

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // 您可以在 Flight 中使用 WordPress 函数！
        $users = get_users();
        $result = [];
        foreach($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email
            ];
        }
        Flight::json($result);
    }
}
```

然后，在您的 `routes.php` 中：

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## 常见问题

**Q: 我需要了解 Flight 才能使用这个插件吗？**  
A: 是的，这适合希望在 WordPress 中使用 Flight 的开发人员。建议了解 Flight 的路由和请求处理的基本知识。

**Q: 这会让我的 WordPress 站点变慢吗？**  
A: 不会！插件只处理匹配 Flight 路由的请求。其他请求像往常一样交给 WordPress 处理。

**Q: 我可以在我的 Flight 应用程序中使用 WordPress 函数吗？**  
A: 当然！您可以从 Flight 路由和控制器中完全访问所有 WordPress 函数、钩子和全局变量。

**Q: 如何创建自定义路由？**  
A: 在您的应用程序文件夹中的 `config/routes.php` 文件中定义路由。请查看文件夹结构生成器创建的示例文件获取示例。

## 更新日志

**1.0.0**  
初始发布。

---

如需更多信息，请查看 [GitHub repo](https://github.com/n0nag0n/wordpress-integration-for-flight-framework)。