> _この記事は元々 2015 年に [Airpair](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) で公開されました。全クレジットは元々この記事を書いた Airpair と Brian Fenton に帰属しますが、ウェブサイトはもはや利用できず、記事は [Wayback Machine](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) でのみ存在します。この記事は PHP コミュニティ全体の学習と教育目的でサイトに追加されました。_

1 セットアップと構成
-------------------------

### 1.1 現在のものを保持

最初からこれを強調しましょう - 野生で使われている PHP のインストールの驚くほど少ない数が、現在のもの、または更新されたものです。これは共有ホスティングの制限、デフォルトが誰も変更しないこと、またはアップグレードテストのための時間/予算がないためです。PHP のバイナリは後回しにされがちです。ですから、常に現在の PHP のバージョンを使用するという明確なベストプラクティスを強調する必要があります（この記事時点では 5.6.x）。さらに、PHP 自体と使用している拡張やベンダーライブラリを定期的にアップグレードするスケジュールを組むことも重要です。アップグレードにより、新しい言語機能、改善された速度、少ないメモリ使用量、そしてセキュリティ更新が得られます。アップグレードを頻繁に行うほど、プロセスが苦痛にならなくなります。

### 1.2 適切なデフォルトを設定

PHP は _php.ini.development_ と _php.ini.production_ ファイルでデフォルトの良い設定をしますが、さらに改善できます。例えば、それらは日付/タイムゾーンを設定してくれません。これは配布の観点から理にかなっていますが、設定がないと、日付/時間関連の関数を呼び出すたびに E_WARNING エラーが発生します。以下は推奨設定です：

*   date.timezone - [サポートされているタイムゾーンのリスト](http://php.net/manual/en/timezones.php) から選択
*   session.save_path - セッションをファイルで使用し、他の保存ハンドラでない場合、これを _/tmp_ 以外の場所に設定。_ /tmp_ をそのままにしておくと、共有ホスティング環境でリスクがあります。なぜなら _/tmp_ は通常、権限が広く開かれているからです。スティッキービットが設定されていても、このディレクトリのコンテンツをリストできる人は、すべてのアクティブなセッション ID を知ることができます。
*   session.cookie_secure - PHP コードを HTTPS で提供している場合、これをオンに。
*   session.cookie_httponly - PHP セッションクッキーが JavaScript からアクセスされないように設定
*   もっと... [iniscan](https://github.com/psecio/iniscan) のようなツールを使って、構成の一般的な脆弱性をテスト

### 1.3 拡張

使用しない拡張（例: データベースドライバなど）は無効にする（または少なくとも有効にしない）のが良い考えです。有効になっているものを確認するには、`phpinfo()` コマンドを実行するか、コマンドラインでこれを実行します。

```bash
$ php -i
``` 

情報は同じですが、phpinfo() には HTML フォーマットが追加されています。CLI バージョンは、特定の情報を検索するために grep にパイプしやすくなります。例えば。

```bash
$ php -i | grep error_log
```

ただし、この方法の注意点: ウェブ向けのバージョンと CLI バージョンの PHP 設定が異なる可能性があります。

2 Composer を使用
--------------

これは驚きかもしれませんが、現代の PHP を書くためのベストプラクティスの一つは、少ないコードを書くことです。プログラミングを上達させる最良の方法は実際にやることでありますが、ルーティング、基本的な入力検証ライブラリ、単位変換、データベース抽象レイヤーなどの多くの問題は、PHP 領域で既に解決されています。ただ [Packagist](https://www.packagist.org/) に行って調べてみてください。おそらく、解決しようとしている問題の重要な部分が既に書かれていてテストされているでしょう。

すべてを自分で書きたくなる temptation はありますが（学習体験として自分のフレームワークやライブラリを書くこと自体は問題ありません）、Not Invented Here の感情に戦って、時間と頭痛を節約してください。代わりに PIE の教義に従ってください - Proudly Invented Elsewhere。また、自分で書くものを選んだ場合、既存のものと大きく異なったり優れているものでない限り、公開しないでください。

[Composer](https://www.getcomposer.org/) は PHP のパッケージマネージャーで、Python の pip、Ruby の gem、Node の npm に似ています。JSON ファイルでコードの依存を定義し、それらの要件を解決して必要なコードバンドルをダウンロードしてインストールします。

### 2.1 Composer のインストール

これはローカルプロジェクトだと仮定しますので、現在のプロジェクト用の Composer のインスタンスをインストールしましょう。プロジェクトディレクトリに移動して、これを実行します：
```bash
$ curl -sS https://getcomposer.org/installer | php
```

任意のダウンロードをスクリプトインタープリタ (sh, ruby, php など) に直接パイプするのはセキュリティリスクです。ですから、インストールコードを読み、実行する前に快適に感じてください。

利便性のために ( `php composer.phar install` より `composer install` とタイプするのが好みなら)、composer の単一コピーをグローバルにインストールするコマンドを使えます：

```bash
$ mv composer.phar /usr/local/bin/composer
$ chmod +x composer
```

ファイル権限によって、sudo で実行する必要があるかもしれません。

### 2.2 Composer の使用

Composer は管理できる依存の主なカテゴリを 2 つ持っています: "require" と "require-dev"。 "require" としてリストされた依存はどこでもインストールされますが、"require-dev" の依存は特にリクエストされた場合にのみインストールされます。通常、これらはアクティブな開発中のツールで、[PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) のようなものです。以下は [Guzzle](http://docs.guzzlephp.org/en/latest/)、人気の HTTP ライブラリをインストールする方法の例です。

```bash
$ php composer.phar require guzzle/guzzle
```

開発目的のみのツールをインストールするには、--dev フラグを追加します：

```bash
$ php composer.phar require --dev 'sebastian/phpcpd'
```

これは [PHP Copy-Paste Detector](https://github.com/sebastianbergmann/phpcpd) を、開発専用依存としてインストールします。他のコード品質ツールです。

### 2.3 Install 対 update

最初に `composer install` を実行すると、_composer.json_ ファイルに基づいて必要なライブラリとその依存をインストールします。それが完了すると、composer は _composer.lock_ というロックファイルを作成します。このファイルには、composer が見つけた依存とその正確なバージョン、 hashes が含まれています。その後、将来 `composer install` を実行するたびに、ロックファイルを見てその正確なバージョンをインストールします。

`composer update` は少し違います。_composer.lock_ ファイル (存在する場合) を無視して、_composer.json_ の制約を満たす各依存の最新バージョンを探します。完了したら、新しい _composer.lock_ ファイルを書き込みます。

### 2.4 オートロード

composer install と composer update の両方が、インストールしたライブラリを使うために必要なファイルを PHP に教える [autoloader](https://getcomposer.org/doc/04-schema.md#autoload) を生成します。使用するには、この行を追加します (通常、毎リクエストで実行されるブートストラップファイルに)：
```php
require 'vendor/autoload.php';
```

3 良い設計原則に従う
-------------------------------

### 3.1 SOLID

SOLID は、良いオブジェクト指向ソフトウェア設計の 5 つの主要な原則を思い出すためのニーモニックです。

#### 3.1.1 S - 単一責任原則

これは、クラスは 1 つの責任だけを持つべきだと言っています。つまり、変更する理由は 1 つだけです。これは、Unix の哲学である、1 つのことをうまくやる小さなツールのたくさんと一致します。1 つのことだけをするクラスは、テストしやすく、デバッグしやすく、驚かされにくくなります。Validator クラスのメソッド呼び出しが DB レコードを更新しないようにしたいです。以下は [ActiveRecord pattern](http://en.wikipedia.org/wiki/Active_record_pattern) に基づくアプリケーションでよく見られる、SRP 違反の例です。

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
    

これは基本的な [entity](http://lostechies.com/jimmybogard/2008/05/21/entities-value-objects-aggregates-and-roots/) モデルです。ただし、これらのうち 1 つはここに属していません。エンティティモデルの唯一の責任は、それが表すエンティティに関連する行動であって、自分自身を永続化する責任を持つべきではありません。

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

これは改善です。Person モデルは 1 つのことだけに戻り、save 行動は永続化オブジェクトに移動しました。また、Model のみを型ヒントに使用したことに注意してください。Person ではありません。SOLID の L と D の部分でこれに戻ります。

#### 3.1.2 O - 開放閉鎖原則

これをまとめた素晴らしいテストがあります: 実装する機能について考えてみてください。おそらく最近作業したもの、または作業中のもの。既存のコードベースで、新しいクラスを追加するだけで、既存のクラスの変更なしにその機能を実装できますか？ 構成と配線コードは少し例外ですが、ほとんどのシステムでこれは驚くほど難しいです。ポリモーフックディスパッチに頼らなければなりませんし、ほとんどのコードベースはそれに設定されていません。これに興味があるなら、[polymorphism and writing code without Ifs](https://www.youtube.com/watch?v=4F72VULWFvc) についての良い Google トークが YouTube にあります。ボーナスとして、トークは [Miško Hevery](http://misko.hevery.com/) によって行われ、多くの人が [AngularJs](https://angularjs.org/) の作成者として知っています。

#### 3.1.3 L - Liskov 置換原則

この原則は [Barbara Liskov](http://en.wikipedia.org/wiki/Barbara_Liskov) の名前にちなんで名付けられ、以下のように述べられています：

> "プログラム内のオブジェクトは、そのサブタイプのインスタンスに置き換えても、プログラムの正しさを変えないべきです。"

これはすべて良さそうですが、例でより明確に示されます。

```php
abstract class Shape
{
    public function getHeight();
    public function setHeight($height);
    public function getLength();
    public function setLength($length);
}
```   

これは基本的な四角形を表します。何も特別なものはありません。

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

私たちの最初の形状、Square です。まっすぐな形状ですね？ 寸法を設定するコンストラクタがあると仮定できますが、この実装から、length と height は常に同じになることがわかります。Square はそういうものです。

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

だからここに別の形状があります。同じメソッドシグネチャを持ちますが、四角形ですが、お互いに置き換えて使い始めるとどうなるでしょうか？ Shape の height を変更すると、shape の length が一致しなくなります。私たちの Square 形状に与えた契約に違反しています。

これは LSP の違反の教科書的な例で、型システムを最大限に活用するためにこのような原則が必要です。[duck typing](http://en.wikipedia.org/wiki/Duck_typing) でさえ、基礎的な行動が違うことを教えてくれませんし、それが壊れるまで知ることはできないので、最初から違うものにしないのが最善です。

#### 3.1.3 I - インターフェース分離原則

この原則は、多くの小さな、細かいインターフェースを好むと言っています。一つ大きなものではなく。インターフェースは行動に基づくべきで、"これらのクラスの一つ" ではありません。PHP に付属するインターフェースを考えてみてください。Traversable、Countable、Serializable などです。それらはオブジェクトが持つ能力を宣伝し、継承するものではありません。だから、インターフェースを小さく保ってください。30 メソッドを持つものは望ましくなく、3 が良い目標です。

#### 3.1.4 D - 依存逆転原則

これは [Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection) について話した他の場所で聞いたことがあるかもしれませんが、依存逆転と依存注入は全く同じものではありません。依存逆転は、システムの詳細ではなく抽象に依存すべきだと言う方法です。日常的にこれは何を意味するでしょうか？

> コード全体で mysqli_query() を直接使用しないで、DataStore->query() のようなものを使ってください。

この原則の核心は抽象についてです。つまり、"データベースアダプタを使用" と言うことなので、mysqli_query のような直接呼び出しに依存しないということです。mysqli_query を半分のクラスで直接使用している場合、すべてをデータベースに直接結びつけています。ここで MySQL に反対しているわけではありませんが、mysqli_query を使用している場合、そのような低レベルの詳細は 1 つの場所に隠され、汎用ラッパー経由で公開されるべきです。

今、私はこれが hackneyed な例だと知っていますが、製品が本番環境にある後でデータベースエンジンを完全に変更する回数は非常に少ないです。私は人々が自分のコードからアイデアに慣れていると思ったので選んだものです。また、特定のデータベースに固執している場合でも、その抽象ラッパーオブジェクトはバグを修正、行動を変更、または選択したデータベースに欲しい機能を実装することを可能にします。また、ユニットテストを可能にします。

4 オブジェクトキャリスティクス
---------------------

これはこれらの原則への完全な潜入ではありませんが、最初の 2 つは簡単に覚えやすく、良い価値を提供し、ほぼすべてのコードベースにすぐに適用できます。

### 4.1 メソッドごとのインデントを 1 レベル以内に

これは、メソッドを小さなチャンクに分解して考えるのに役立ち、より明確で自己文書化されたコードを残します。インデントのレベルが多いほど、メソッドがより多くのことをし、作業中に頭の中で追跡する状態が増えます。

すぐに人々がこれに反対するでしょうが、これはガイドライン/ヒューリスティックで、厳格なルールではありません。私は PHP_CodeSniffer のルールをこれで施行するのを期待していません (しかし [people have](https://github.com/object-calisthenics/phpcs-calisthenics-rules))。

これがどうなるかを素早くサンプルで実行しましょう：

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

これはひどいコードではありません (技術的に正しく、テスト可能など) が、これを明確にするために多くを改善できます。ここでネストのレベルを減らすには？

まず、foreach ループを簡略化する必要があります (または完全に削除) ので、そこから始めましょう。

```php
if (!$row) {
    continue;
}
```   

これは簡単です。これは空の行を無視するだけです。ループに到達する前に、PHP の組み込み関数でこれをショートカットできます。

```php
$data = array_filter($data);
foreach ($data as $row) {
    $csvLines[] = implode(',', $row);
}
```

今、単一のネストレベルです。しかし、これを見ると、配列の各項目に関数を適用しているだけです。これで foreach ループは必要ありません。

```php
$data = array_filter($data);
$csvLines = array_map(function($row) {
    return implode(',', $row);
}, $data);
```

今、ネストが全くありません、そしてコードは速くなるでしょう。なぜなら、ループをネイティブ C 関数で行っているからです。ただし、implode にコンマを渡すための少しの trickery が必要なので、前のステップで止めるのがより理解しやすいと主張できます。

### 4.2 `else` を使用しない

これは 2 つの主要なアイデアを扱っています。1 つ目は、メソッドからの複数の return 文です。メソッドの結果についての決定を下すのに十分な情報がある場合、その決定を下して return してください。2 つ目は [Guard Clauses](http://c2.com/cgi/wiki?GuardClause) として知られるアイデアです。これらは基本的に、メソッドの先頭近くで検証チェックと早期 return を組み合わせたものです。意味を説明しましょう。

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

これは再びストレートフォワードで、3 つの int を加えて結果を返します、またはパラメータのいずれかが整数でない場合 `null` を返します。AND 演算子でこれらのチェックを 1 行に組み合わせられることを無視して、入れ子になった if/else 構造がコードを追いづらくしていると思います。代わりにこの例を見てください。

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

私にとってこの例はより追いやすいです。ここで guard clauses を使用して、パラメータについての初期の主張を検証し、それらが通過しない場合すぐにメソッドを終了します。また、sum をメソッド全体で追跡する中間変数もありません。私たちは既に happy path にあり、来たことをするだけです。再び、すべてのチェックを 1 つの `if` でできるのですが、原則は明確です。

5 ユニットテスト
--------------

ユニットテストは、コードの行動を検証する小さなテストを書く練習です。ほとんどいつもコードと同じ言語 (この場合 PHP) で書かれ、いつでも実行できるほど速いです。これらはコードを改善するための非常に価値あるツールです。コードが何をしているかを確保するという明らかな利点以外に、ユニットテストは設計フィードバックも提供します。テストしにくいコードは、設計問題をしばしば示します。また、回帰に対する安全網を与え、より頻繁にリファクタリングし、コードをよりクリーンな設計に進化させることを可能にします。

### 5.1 ツール

PHP にはいくつかのユニットテストツールがありますが、断然最も一般的なのは [PHPUnit](https://phpunit.de/) です。[PHAR](http://php.net/manual/en/intro.phar.php) ファイルを [directly](https://phar.phpunit.de/phpunit.phar) ダウンロードするか、composer でインストールできます。composer を他のすべてに使用しているので、その方法を示します。また、PHPUnit は本番環境に展開されない可能性が高いので、dev 依存として以下コマンドでインストールできます：

```bash
composer require --dev phpunit/phpunit
```

### 5.2 テストは仕様

コード内のユニットテストの最も重要な役割は、コードが何をするはずかを提供する実行可能な仕様です。テストコードが間違っている、またはコードにバグがあるとしても、システムが _supposed_ に何をするかを知ることは非常に価値があります。

### 5.3 最初にテストを書く

コードの前にテストを書いたものと、コードが完成した後に書いたものを比較すると、驚くほど違います。"後" のテストはクラスの実装詳細に焦点を当て、良い行カバレッジを確保しますが、"前" のテストは望ましい外部行動を検証します。それがユニットテストで気にするものです。つまり、クラスが正しい行動を示すことです。実装に焦点を当てたテストは、クラスの内部が変更すると壊れるので、リファクタリングを難しくします、そして OOP の情報隠蔽の利点を失います。

### 5.4 良いユニットテストの条件

良いユニットテストは以下の特性を共有します：

*   速い - ミリ秒で実行。
*   ネットワークアクセスなし - 無線をオフにしたり、ケーブルを抜いてもすべてのテストが通る。
*   ファイルシステムアクセスの制限 - 速度と環境への柔軟性を追加。
*   データベースアクセスなし - コストのかかるセットアップとクリーンアップ活動を避ける。
*   1 つずつテスト - ユニットテストは失敗する理由を 1 つだけ持つ。
*   良い名前 - 5.2 を参照。
*   ほとんど偽オブジェクト - ユニットテスト内の唯一の "real" オブジェクトはテストしているオブジェクトとシンプルな値オブジェクトで、残りは [test double](https://phpunit.de/manual/current/en/test-doubles.html) の一部。

これらのいくつかに反する理由がありますが、一般的なガイドラインとして役立ちます。

### 5.5 テストが苦痛なとき

> Unit testing forces you to feel the pain of bad design up front - Michael Feathers

ユニットテストを書いているとき、クラスを実際に使用してものを成し遂げています。テストを最後に書くか、または最悪の場合、QA や誰かにコードを投げてテストを書かせるなら、クラスが実際にどう行動するかのフィードバックが得られません。テストを書いていて、クラスが本当に苦痛なら、それを書きながら知ることになり、これはほぼ最安の修正時間です。

クラスがテストしにくい場合、それは設計の欠陥です。異なる欠陥は異なる方法で現れます。多くの mocking をしなければならない場合、クラスに依存が多すぎるか、メソッドが多すぎる可能性があります。各テストのためのセットアップが多い場合、メソッドが多すぎる可能性が高いです。行動を練習するために複雑なテストシナリオを書かなければならない場合、クラスのメソッドが多すぎる可能性があります。プライベートメソッドと状態の内部を掘ってテストしなければならない場合、もしかすると別のクラスが外に出ようとしているのかもしれません。ユニットテストは "iceberg classes" を公開するのが非常に上手く、クラスの 80% が保護またはプライベートコードで隠されているものです。私は以前、可能な限り多くを保護にするのが大ファンでしたが、今は個々のクラスが多すぎる責任を持っていたことに気づき、真の解決策はクラスを小さな部分に分解するでした。

> **Brian Fenton 執筆** - Brian Fenton はミッドウェストとベイエリアで 8 年間 PHP 開発者で、現在 Thismoment で働いています。彼はコード職人技と設計原則に焦点を当てています。ブログは www.brianfenton.us、Twitter は @brianfenton。お父さんをしている以外に、食べ物、ビール、ゲーム、そして学習を楽しんでいます。