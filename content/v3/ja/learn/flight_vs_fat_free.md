# Fat-Free vs Flight

## 何がFat-Freeか？
[Fat-Free](https://fatfreeframework.com)（愛称**F3**）は、迅速に動的かつ堅牢なウェブアプリケーションを構築するのに役立つ強力で使いやすいPHPマイクロフレームワークです。

Flightは多くの点でFat-Freeと比較され、機能とシンプリシティの面ではおそらく最も近しい親戚です。 Fat-FreeにはFlightにはない機能が多く含まれていますが、Flightにはある機能も多くあります。 Fat-Freeは時代遅れになりつつあり、かつてほど人気がありません。

更新頻度が低くなり、コミュニティも以前ほど活発ではありません。コードは十分にシンプルですが、構文の規律が欠如していることが時々読み取りやすさを損なうことがあります。PHP 8.3でも動作しますが、コード自体はまだPHP 5.3であるかのように見えます。

## Flightと比較したPros

- Fat-FreeにはFlightよりもGitHubでいくつかのスターが多い。
- Fat-Freeにはいくつかのきちんとしたドキュメントがありますが、明確さに欠ける部分もあります。
- Fat-Freeには、フレームワークを学ぶのに使用できるYouTubeチュートリアルやオンライン記事など、いくつかのスカスカリソースがあります。
- Fat-Freeには時々役立つ[いくつかのプラグイン](https://fatfreeframework.com/3.8/api-reference)が組み込まれています。
- Fat-Freeには、データベースとやり取りするために使用できるMapperと呼ばれる組み込みのORMがあります。Flightには[active-record](/awesome-plugins/active-record)があります。
- Fat-Freeにはセッション、キャッシング、ローカライゼーションが組み込まれています。Flightではサードパーティライブラリを使用する必要がありますが、[ドキュメント](/awesome-plugins)でカバーされています。
- Fat-Freeには、フレームワークを拡張するために使用できる[コミュニティ作成のプラグイン](https://fatfreeframework.com/3.8/development#Community)が少数あります。Flightには[ドキュメント](/awesome-plugins)と[例](/examples)ページでカバーされています。
- Fat-FreeはFlight同様に依存関係がありません。
- Fat-FreeはFlight同様に開発者がアプリケーションを制御し、シンプルな開発体験を提供することを目的としています。
- Fat-Freeは更新が[少なくなってきている](https://github.com/bcosca/fatfree/releases)ため、Flightと同様に後方互換性を維持しています。
- Fat-FreeはFlight同様に、フレームワークの世界に初めて足を踏み入れる開発者を対象としています。
- Fat-Freeには、Flightのテンプレートエンジンよりも堅牢な組み込みのテンプレートエンジンがあります。Flightはこれを達成するために[Latte](/awesome-plugins/latte)を推奨しています。
- Fat-Freeには、「route」と呼ばれるユニークなCLI型コマンドがあり、Fat-Free自体内でCLIアプリを構築して、それをGETリクエストのように処理できます。Flightはこれを[runway](/awesome-plugins/runway)で実現しています。

## Flightと比較したCons

- Fat-Freeには一部の実装テストがあり、非常に基本的な自社の[test](https://fatfreeframework.com/3.8/test) クラスがありますが、Flightのように100％ユニットテストされていません。
- ドキュメントサイトを実際に検索するにはGoogleのような検索エンジンを使用する必要があります。
- Flightのドキュメントサイトにはダークモードがあります。（マイクを落とす）
- Fat-Freeにはメンテナンスされていないモジュールがいくつかあります。
- Flightには、Fat-Freeの組み込みの`DB \ SQL`クラスよりも少しシンプルな[PdoWrapper](/awesome-plugins/pdo-wrapper)があります。
- Flightにはアプリケーションを保護するために使用できる[permissionsプラグイン](/awesome-plugins/permissions)があります。Slimではサードパーティライブラリを使用する必要があります。
- Flightには、Fat-FreeのMapperよりもORMらしい[active-record](/awesome-plugins/active-record)があります。`active-record`の追加メリットは、Fat-FreeのMapperが[SQLビュー](https://fatfreeframework.com/3.8/databases#ProsandCons)を作成する必要があるのに対し、レコード間の関係を定義して自動結合することができます。
- 驚くべきことに、Fat-Freeにはルート名前空間がありません。Flightは、独自のコードと衝突しないようにすべての方法で名前空間が付けられています。`Cache`クラスが最も問題があります。
- Fat-Freeにはミドルウェアがありません。代わりに、リクエストとレスポンスをフィルタリングするために使用できる`beforeroute`および`afterroute`フックがあります。
- Fat-Freeでは、ルートをグループ化することはできません。
- Fat-Freeには依存性注入コンテナハンドラがありますが、その使用方法に関するドキュメントが非常にわずかです。
- デバッギングは、基本的にすべてが[`HIVE`](https://fatfreeframework.com/3.8/quick-reference)に保存されているため、少し複雑になることがあります。