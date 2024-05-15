## 👑問診表LINE Bot(仮称)

- 説明書いてね

## 📌前提条件

- PHP (バージョン 7.2)
- phpdotenv (バージョン 4.3)
- line-bot-sdk (バージョン 7.5)
- その他のライブラリ（必要な場合）

## 📝環境設定

- 各ディレクトリの.env.sample を参考に各サービスの環境変数を設定

## 🤖はじめかた

- 下記コマンドより Docker を起動．

```Shell
docker-compose build
docker-compose up -d
```

- (開発中ファイルの変更などがキャッシュビルドによって反映されない場合は以下のコマンドを使用)

```Shell
docker-compose up --build --force-recreate -d
```

- モジュールが必要な場合 Docker コンテナ内のシェルからコマンドを実行

```Shell
docker-compose run --rm <<コンテナ名>> sh
```

## 🐤ngrokについて

## 🪶Line webhook

## 