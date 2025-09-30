# Flight vs Fat-Free

## Fat-Free とは？
[Fat-Free](https://fatfreeframework.com)（愛情を込めて **F3** と呼ばれる）は、動的で堅牢なウェブアプリケーションを迅速に構築するための強力で使いやすい PHP マイクロフレームワークです。

Flight は Fat-Free と多くの点で比較でき、機能とシンプルさの点で最も近い親戚です。Fat-Free には Flight にない機能がたくさんありますが、Flight にもある機能もたくさんあります。Fat-Free は古さを見せ始め、かつてほど人気はありません。

更新は徐々に少なくなり、コミュニティもかつてほど活発ではありません。コードは十分にシンプルですが、文法の規律の欠如が読みにくく理解しにくくすることがあります。PHP 8.3 で動作しますが、コード自体はまだ PHP 5.3 に住んでいるように見えます。

## Flight との Pros

- Fat-Free は GitHub で Flight より少し多くのスターを持っています。
- Fat-Free にはまともなドキュメントがありますが、一部の領域で明確さに欠けます。
- Fat-Free には YouTube チュートリアルやオンライン記事などのまばらなリソースがあり、フレームワークを学ぶのに使えます。
- Fat-Free には [いくつかの役立つプラグイン](https://fatfreeframework.com/3.8/api-reference) が組み込まれており、時には役立ちます。
- Fat-Free にはデータベースとやり取りするための組み込み ORM である Mapper があります。Flight には [active-record](/awesome-plugins/active-record) があります。
- Fat-Free には Sessions、Caching、localization が組み込まれています。Flight ではサードパーティライブラリを使用する必要がありますが、[ドキュメント](/awesome-plugins) でカバーされています。
- Fat-Free にはフレームワークを拡張するための [コミュニティ作成のプラグイン](https://fatfreeframework.com/3.8/development#Community) の小さなグループがあります。Flight には [ドキュメント](/awesome-plugins) および [examples](/examples) ページでカバーされているものがあります。
- Fat-Free は Flight と同じく依存関係がありません。
- Fat-Free は Flight と同じく、開発者にアプリケーションの制御とシンプルな開発体験を与えることを目指しています。
- Fat-Free は Flight と同じく後方互換性を維持しています（部分的に更新が [少なく](https://github.com/bcosca/fatfree/releases) なっているため）。
- Fat-Free は Flight と同じく、フレームワークの世界に初めて足を踏み入れる開発者向けです。
- Fat-Free には Flight のテンプレートエンジンより堅牢な組み込みテンプレートエンジンがあります。Flight ではこれを達成するために [Latte](/awesome-plugins/latte) を推奨します。
- Fat-Free にはユニークな CLI タイプの "route" コマンドがあり、Fat-Free 内で CLI アプリを構築でき、`GET` リクエストのように扱えます。Flight では [runway](/awesome-plugins/runway) でこれを実現します。

## Flight との Cons

- Fat-Free にはいくつかの実装テストがあり、独自の [test](https://fatfreeframework.com/3.8/test) クラスもありますが、それは非常に基本的なものです。しかし、
  Flight のように 100% ユニットテストされていません。 
- ドキュメントサイトを実際に検索するには Google などの検索エンジンを使用する必要があります。
- Flight のドキュメントサイトにはダークモードがあります。（mic drop）
- Fat-Free にはひどくメンテナンスされていないモジュールがいくつかあります。
- Flight には [PdoWrapper](/learn/pdo-wrapper) があり、Fat-Free の組み込み `DB\SQL` クラスより少しシンプルです。
- Flight にはアプリケーションをセキュアにするための [permissions plugin](/awesome-plugins/permissions) があります。Fat Free ではサードパーティライブラリを使用する必要があります。
- Flight には [active-record](/awesome-plugins/active-record) という ORM があり、Fat-Free の Mapper より ORM のように感じます。
  `active-record` の追加の利点は、レコード間の関係を定義して自動ジョインが可能で、Fat-Free の Mapper では [SQL views](https://fatfreeframework.com/3.8/databases#ProsandCons) を作成する必要があります。
- 驚くべきことに、Fat-Free にはルート名前空間がありません。Flight は自分のコードと衝突しないようにすべて名前空間化されています。
  `Cache` クラスがここで最大の違反者です。
- Fat-Free にはミドルウェアがありません。代わりに、コントローラーでリクエストとレスポンスをフィルタリングするための `beforeroute` と `afterroute` フックがあります。
- Fat-Free はルートをグループ化できません。
- Fat-Free には依存性注入コンテナハンドラーがありますが、使い方のドキュメントは非常にまばらです。
- デバッグは少しトリッキーになることがあり、基本的にすべてが [`HIVE`](https://fatfreeframework.com/3.8/quick-reference) と呼ばれるものに保存されているためです。