# Dev Dictionary

個人・友人利用向けの軽量版 Dev Dictionary です。  
PHP + PDO だけで動く、小規模向けの辞書管理アプリです。

## 実装済み機能

- ログイン / ログアウト
- 新規ユーザー登録
- 辞書一覧 / 検索 / 詳細
- 辞書の新規登録 / 編集 / 論理削除
- キーワード管理
- 更新履歴一覧 / 履歴詳細
- カテゴリ一覧 / カテゴリ追加
- 所有者チェック
- CSRF対策
- セッション再生成

## 推奨環境

- PHP 8.1+
- SQLite 3 または MySQL / MariaDB
- nginx または Apache

## すぐ試す手順（SQLite）

```bash
cp .env.example .env
php scripts/init_sqlite.php
php -S localhost:8000 -t public
```

ブラウザで以下を開きます。

```text
http://localhost:8000/register
```

登録後、そのままログインして使えます。

## nginx で使う場合

- DocumentRoot は `public/`
- rewrite で `public/index.php` に流す

## MySQL で使う場合

1. `.env` の `DB_CONNECTION=mysql` に変更
2. `database/schema_mysql.sql` を流し込む
3. `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` を設定
4. categories と users は必要に応じて投入

## 軽量版として簡略化した点

- roles テーブルは廃止し、`users.role` のみ
- ダッシュボードは省略し、辞書一覧を起点画面化
- 履歴は「保存後スナップショット」方式
- slug は未入力なら自動生成
- 公開範囲はログインユーザー本人の辞書に限定

## ディレクトリ構成

```text
app/
  Controllers/
  Services/
  Repositories/
  Helpers/
  Views/
bootstrap/
config/
database/
public/
routes/
scripts/
storage/
```

## 注意

- 共有利用の本番運用では、メール認証、パスワード再発行、レート制限、監査ログの追加を推奨します。
- 今回の成果物は「個人・友人利用レベル」の軽量版です。
