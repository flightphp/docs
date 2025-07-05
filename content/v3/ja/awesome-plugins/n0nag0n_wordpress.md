# WordPress 統合: n0nag0n/wordpress-integration-for-flight-framework

WordPress サイト内で Flight PHP を使用したいですか？ このプラグインがあれば簡単です！ `n0nag0n/wordpress-integration-for-flight-framework` を使って、WordPress と並行して完全な Flight アプリを実行できます—カスタム API、マイクロサービス、またはフル機能のアプリを構築するのに最適です。

---

## これは何をするのですか？

- **Flight PHP を WordPress とシームレスに統合**
- URL パターンに基づいてリクエストを Flight または WordPress にルーティング
- コントローラー、モデル、ビュー (MVC) でコードを整理
- 推奨される Flight フォルダー構造を設定
- WordPress のデータベース接続または独自のものを使用
- Flight と WordPress の相互作用を微調整
- 簡単な管理インターフェースで設定

## インストール

1. `flight-integration` フォルダーを `/wp-content/plugins/` ディレクトリにアップロードします。
2. WordPress 管理画面 (Plugins メニュー) でプラグインを有効化します。
3. **Settings > Flight Framework** に移動してプラグインを設定します。
4. Flight のインストール場所をベンダーパスに設定します (または Composer を使用して Flight をインストール)。
5. アプリフォルダーパスを設定し、フォルダー構造を作成します (プラグインがこれを助けてくれます!)。
6. Flight アプリケーションの構築を開始します！

## 使用例

### 基本的なルート例
アプリの `app/config/routes.php` ファイルで:

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### コントローラー例

`app/controllers/ApiController.php` にコントローラーを作成:

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // Flight 内で WordPress の関数を使用できます！
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

次に、`routes.php` で:

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## FAQ

**Q: このプラグインを使用するために Flight を知っておく必要がありますか？**  
A: はい、これは WordPress 内で Flight を使用したい開発者向けです。Flight のルーティングとリクエスト処理の基本的な知識をおすすめします。

**Q: これは WordPress サイトを遅くしますか？**  
A: いいえ！ プラグインは Flight のルートに一致するリクエストのみ処理します。他のすべてのリクエストは通常通り WordPress に渡されます。

**Q: Flight アプリで WordPress の関数を使用できますか？**  
A: もちろんです！ Flight のルートとコントローラーから WordPress のすべての関数、フック、グローバル変数にアクセスできます。

**Q: カスタムルートを作成するにはどうしますか？**  
A: アプリフォルダーの `config/routes.php` ファイルでルートを定義します。フォルダー構造生成ツールで作成されたサンプルファイルを参照してください。

## 変更履歴

**1.0.0**  
初回リリース。

---

詳細については、[GitHub repo](https://github.com/n0nag0n/wordpress-integration-for-flight-framework) を確認してください。