# Flight vs Laravel

## Laravel とは？
[Laravel](https://laravel.com) は、すべての機能が揃ったフル機能のフレームワークで、素晴らしい開発者向けエコシステムを備えていますが、パフォーマンスと複雑さの代償を伴います。Laravel の目標は、開発者が最高レベルの生産性を発揮し、共通のタスクを容易にすることです。Laravel は、フル機能のエンタープライズ Web アプリケーションを構築したい開発者にとって優れた選択肢です。これにはいくつかのトレードオフが伴い、特にパフォーマンスと複雑さの点でそうです。Laravel の基礎を学ぶのは簡単ですが、フレームワークに習熟するには時間がかかる場合があります。

また、Laravel のモジュールが非常に多く、開発者は問題を解決する唯一の方法がこれらのモジュールを使うことだと感じることがよくあります。しかし、実際には別のライブラリを使ったり、自分のコードを書いたりするだけで十分な場合もあります。

## Flight との比較での利点

- Laravel は、共通の問題を解決するために使用できる **巨大なエコシステム** の開発者とモジュールを持っています。
- Laravel は、データベースとやり取りするために使用できるフル機能の ORM を備えています。
- Laravel は、フレームワークを学ぶために使用できる膨大なドキュメントとチュートリアルを持っています。これは、細部まで掘り下げるのに良い一方で、量が多すぎて大変な場合もあります。
- Laravel は、アプリケーションを保護するために使用できる組み込みの認証システムを持っています。
- Laravel は、フレームワークを学ぶために使用できるポッドキャスト、カンファレンス、ミーティング、ビデオ、その他のリソースを持っています。
- Laravel は、フル機能のエンタープライズ Web アプリケーションを構築したい経験豊富な開発者向けに設計されています。

## Flight との比較での欠点

- Laravel は Flight よりも内部で多くの処理が行われており、これにより **劇的な** パフォーマンスの低下が生じます。詳細は [TechEmpower ベンチマーク](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) を参照してください。
- Flight は、軽量で高速、使いやすい Web アプリケーションを構築したい開発者向けに設計されています。
- Flight は、シンプルさと使いやすさを重視しています。
- Flight のコア機能の1つは、後方互換性を最大限に保つことです。一方、Laravel はメジャーバージョン間で [多くのフラストレーション](https://www.google.com/search?q=laravel+breaking+changes+major+version+complaints&sca_esv=6862a9c407df8d4e&sca_upv=1&ei=t72pZvDeI4ivptQP1qPMwQY&ved=0ahUKEwiwlurYuNCHAxWIl4kEHdYRM2gQ4dUDCBA&uact=5&oq=laravel+breaking+changes+major+version+complaints&gs_lp=Egxnd3Mtd2l6LXNlcnAiMWxhcmF2ZWwgYnJlYWtpbmcgY2hhbmdlcyBtYWpvciB2ZXJzaW9uIGNvbXBsYWludHMyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEdIjAJQAFgAcAF4AZABAJgBAKABAKoBALgBA8gBAJgCAaACB5gDAIgGAZAGCJIHATGgBwA&sclient=gws-wiz-serp) を引き起こします。
- Flight は、フレームワークの世界に初めて足を踏み入れる開発者向けです。
- Flight は依存関係がなく、一方 [Laravel はひどい量の依存関係](https://github.com/laravel/framework/blob/12.x/composer.json) を持っています。
- Flight もエンタープライズレベルのアプリケーションを作成できますが、Laravel ほどボイラープレートコードが多くありません。ただし、開発者が組織化と構造化を維持するためにより多くの規律を必要とします。
- Flight は開発者にアプリケーションに対するより多くの制御を与えますが、Laravel は裏側で多くのマジックがあり、それがフラストレーションを生むことがあります。