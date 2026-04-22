-- Dev Dictionary 初期データ投入用（MySQL / MariaDB）
-- 使い方:
-- 1) 先に users テーブルで対象ユーザーを登録してください
-- 2) 下の @target_login_id を自分の login_id に合わせて修正してください
-- 3) dev_dictionary DB に対してこのSQLを実行してください
--    mysql -u <user> -p dev_dictionary < insert_initial_data.sql

SET @target_login_id := 'terigele';
SET @now := NOW();
SET @target_user_id := (
    SELECT user_id
    FROM users
    WHERE login_id = @target_login_id
    LIMIT 1
);

-- 確認用
SELECT @target_login_id AS target_login_id, @target_user_id AS target_user_id;

START TRANSACTION;

-- --------------------------------------------------
-- 1. categories 初期データ
-- debug / mysql を上位に配置
-- --------------------------------------------------
INSERT INTO categories (category_name, description, sort_order, is_active, created_at, updated_at)
VALUES
    ('debug',    'エラー調査、ログ確認、切り分け',                                1, 1, @now, @now),
    ('mysql',    'MySQLの接続、確認、DDL/DML、運用メモ',                          2, 1, @now, @now),
    ('linux',    'Linuxの基本操作、権限、プロセス、ログ確認',                     3, 1, @now, @now),
    ('server',   'サーバー状態確認、サービス管理、ディスク・メモリ確認',           4, 1, @now, @now),
    ('git',      'Gitの基本操作、ブランチ、差分確認、履歴操作',                    5, 1, @now, @now),
    ('ssh通信',  'SSH接続、鍵認証、疎通確認、known_hosts対応',                    6, 1, @now, @now),
    ('インフラ', 'Web / DB / ネットワーク / 監視などの基礎',                      7, 1, @now, @now),
    ('shell',    'bash / sh の基礎、変数、条件分岐、繰り返し',                     8, 1, @now, @now),
    ('sql',      'SELECT / JOIN / 集計 / UPDATE / DELETE の基礎',                  9, 1, @now, @now)
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active),
    updated_at = VALUES(updated_at);

-- --------------------------------------------------
-- 2. dictionary_entries 初期データ
-- 対象ユーザーが存在する場合のみ投入
-- --------------------------------------------------

INSERT INTO dictionary_entries (
    user_id, category_id, title, slug,
    problem_summary, root_cause, check_points, command_examples,
    solution_summary, caution_notes,
    status, priority_level, version_no, published_at,
    created_at, created_by, updated_at, updated_by
)
SELECT
    @target_user_id,
    c.category_id,
    'debug: エラー調査の初動テンプレート',
    'debug-first-response',
    'よくある質問: どこから見ればいいか分からない / 500エラーの原因が見えない / 動かないが再現条件が曖昧。',
    '原因を決め打ちしてしまう、ログを見ない、手順と期待値を整理しないことが多い。',
    '1. 再現手順はあるか\n2. 直前の変更は何か\n3. エラーメッセージは何か\n4. アプリ・Webサーバー・DBのどこで失敗しているか\n5. 正常時との差分は何か',
    'tail -f /var/log/nginx/error.log\n tail -f /var/log/nginx/access.log\n tail -f storage/logs/app.log\n php -v\n php -m | grep -E "pdo|mysql|sqlite"\n systemctl status nginx\n systemctl status php*-fpm\n df -h\n free -m\n ps aux | grep php',
    'まずログ、次に再現条件、最後に設定差分を見る。エラー1件ごとに「現象 / 原因 / 対応 / 再発防止」を残す。',
    '本番では設定変更前にバックアップを取る。いきなり複数箇所を触らない。',
    'published', 5, 1, @now,
    @now, @target_user_id, @now, @target_user_id
FROM categories c
WHERE c.category_name = 'debug'
  AND @target_user_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM dictionary_entries e
      WHERE e.user_id = @target_user_id AND e.slug = 'debug-first-response'
  );

INSERT INTO dictionary_entries (
    user_id, category_id, title, slug,
    problem_summary, root_cause, check_points, command_examples,
    solution_summary, caution_notes,
    status, priority_level, version_no, published_at,
    created_at, created_by, updated_at, updated_by
)
SELECT
    @target_user_id,
    c.category_id,
    'mysql: よく使う確認コマンドと基本操作',
    'mysql-basic-operations',
    'よくある質問: DB一覧を見たい / テーブル構造を確認したい / 権限を付けたい / 接続できない。',
    '接続先の取り違え、権限不足、文字コード不一致、WHERE なし更新などが典型。',
    '1. 接続先ホストとユーザーは正しいか\n2. 使用DBは正しいか\n3. テーブル構造は確認したか\n4. 更新系SQLにWHEREはあるか\n5. バックアップはあるか',
    'mysql -u user -p\n SHOW DATABASES;\n USE dev_dictionary;\n SHOW TABLES;\n DESCRIBE users;\n SHOW CREATE TABLE users\\G\n SELECT * FROM users LIMIT 10;\n CREATE DATABASE dev_dictionary CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n GRANT ALL PRIVILEGES ON dev_dictionary.* TO ''app_user''@''localhost'';',
    '確認系SQLから入り、更新前に SELECT で対象行を確認する。権限と接続先の確認を先に行う。',
    'DELETE / UPDATE は WHERE を付ける。DDL 実行前はバックアップを取る。',
    'published', 5, 1, @now,
    @now, @target_user_id, @now, @target_user_id
FROM categories c
WHERE c.category_name = 'mysql'
  AND @target_user_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM dictionary_entries e
      WHERE e.user_id = @target_user_id AND e.slug = 'mysql-basic-operations'
  );

INSERT INTO dictionary_entries (
    user_id, category_id, title, slug,
    problem_summary, root_cause, check_points, command_examples,
    solution_summary, caution_notes,
    status, priority_level, version_no, published_at,
    created_at, created_by, updated_at, updated_by
)
SELECT
    @target_user_id,
    c.category_id,
    'linux: 初心者が最初に覚える基本コマンド',
    'linux-basic-commands',
    'よくある質問: 今どこにいるか / ファイル一覧を見たい / 権限を変えたい / ログを追いたい。',
    'cd と pwd の混同、相対パスと絶対パスの理解不足、権限の扱い不足が多い。',
    '1. 現在地はどこか\n2. 対象ファイルは存在するか\n3. 権限は足りているか\n4. root が必要か\n5. ログはどこにあるか',
    'pwd\n ls -la\n cd /var/www/html\n mkdir sample_dir\n cp source.txt backup.txt\n mv old.txt new.txt\n rm -i file.txt\n cat file.txt\n less file.txt\n tail -f /var/log/nginx/error.log\n grep -R "keyword" .\n chmod 755 script.sh\n chown www-data:www-data file.txt',
    '場所・権限・ログの3点を押さえると、Linuxの調査が進めやすい。',
    'rm -rf は対象を確認してから使う。chmod 777 は基本避ける。',
    'published', 4, 1, @now,
    @now, @target_user_id, @now, @target_user_id
FROM categories c
WHERE c.category_name = 'linux'
  AND @target_user_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM dictionary_entries e
      WHERE e.user_id = @target_user_id AND e.slug = 'linux-basic-commands'
  );

INSERT INTO dictionary_entries (
    user_id, category_id, title, slug,
    problem_summary, root_cause, check_points, command_examples,
    solution_summary, caution_notes,
    status, priority_level, version_no, published_at,
    created_at, created_by, updated_at, updated_by
)
SELECT
    @target_user_id,
    c.category_id,
    'server: サーバー確認の基本チェックリスト',
    'server-initial-checklist',
    'よくある質問: サイトが重い / 落ちている / ディスクが足りない / サービスが上がらない。',
    'CPU・メモリ・ディスク・サービス状態・ポート疎通を順に見ないと切り分けできない。',
    '1. Webサーバーは起動しているか\n2. PHP-FPM は起動しているか\n3. ディスク容量はあるか\n4. メモリ不足ではないか\n5. ポートはLISTENしているか',
    'hostnamectl\n uptime\n df -h\n free -m\n top\n systemctl status nginx\n systemctl status mysql\n ss -lntp\n journalctl -xe\n tail -f /var/log/syslog',
    '「稼働状況」「資源状況」「ログ」の3軸で確認すると原因に近づきやすい。',
    'いきなり再起動せず、状態とログを取ってから操作する。',
    'published', 4, 1, @now,
    @now, @target_user_id, @now, @target_user_id
FROM categories c
WHERE c.category_name = 'server'
  AND @target_user_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM dictionary_entries e
      WHERE e.user_id = @target_user_id AND e.slug = 'server-initial-checklist'
  );

INSERT INTO dictionary_entries (
    user_id, category_id, title, slug,
    problem_summary, root_cause, check_points, command_examples,
    solution_summary, caution_notes,
    status, priority_level, version_no, published_at,
    created_at, created_by, updated_at, updated_by
)
SELECT
    @target_user_id,
    c.category_id,
    'git: 日常操作でよく使う基本コマンド',
    'git-daily-commands',
    'よくある質問: add / commit / push の順番は? ブランチを切りたい。差分や履歴を見たい。',
    'ワークツリー・インデックス・ローカル履歴・リモートの違いが曖昧だと事故が起きやすい。',
    '1. 今どのブランチか\n2. 未コミット変更はあるか\n3. pull してよい状態か\n4. push先は正しいか\n5. 共有ブランチを壊さないか',
    'git status\n git branch\n git switch -c feature/sample\n git add .\n git commit -m "feat: add sample"\n git pull origin main\n git push origin feature/sample\n git log --oneline --graph --decorate -10\n git diff\n git restore file.txt',
    'status → diff → add → commit → push の流れを固定すると事故が減る。',
    'main へ直接 push しない。意味のあるコミットメッセージを書く。',
    'published', 4, 1, @now,
    @now, @target_user_id, @now, @target_user_id
FROM categories c
WHERE c.category_name = 'git'
  AND @target_user_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM dictionary_entries e
      WHERE e.user_id = @target_user_id AND e.slug = 'git-daily-commands'
  );

INSERT INTO dictionary_entries (
    user_id, category_id, title, slug,
    problem_summary, root_cause, check_points, command_examples,
    solution_summary, caution_notes,
    status, priority_level, version_no, published_at,
    created_at, created_by, updated_at, updated_by
)
SELECT
    @target_user_id,
    c.category_id,
    'ssh通信: 接続トラブル時の確認ポイント',
    'ssh-common-troubleshooting',
    'よくある質問: Permission denied が出る / 鍵が通らない / 接続できない / known_hosts が違う。',
    '鍵ファイル権限、接続先ユーザー、ポート番号、known_hosts の不整合が典型。',
    '1. 接続先IPとポートは正しいか\n2. ユーザー名は正しいか\n3. 鍵ファイル権限は600か\n4. 疎通はあるか\n5. known_hosts に古い情報はないか',
    'ssh user@host\n ssh -i ~/.ssh/id_rsa user@host\n ssh -p 2222 user@host\n ssh -v user@host\n chmod 600 ~/.ssh/id_rsa\n ls -la ~/.ssh\n ping host\n nc -zv host 22\n ssh-keygen -R host',
    'まず疎通、次に鍵、最後にサーバー側設定を確認する。-v オプションで詳細を見る。',
    '秘密鍵を共有しない。known_hosts を消す前に接続先が正しいか確認する。',
    'published', 4, 1, @now,
    @now, @target_user_id, @now, @target_user_id
FROM categories c
WHERE c.category_name = 'ssh通信'
  AND @target_user_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM dictionary_entries e
      WHERE e.user_id = @target_user_id AND e.slug = 'ssh-common-troubleshooting'
  );

INSERT INTO dictionary_entries (
    user_id, category_id, title, slug,
    problem_summary, root_cause, check_points, command_examples,
    solution_summary, caution_notes,
    status, priority_level, version_no, published_at,
    created_at, created_by, updated_at, updated_by
)
SELECT
    @target_user_id,
    c.category_id,
    'インフラ: 初学者向けの基本確認ポイント',
    'infra-basic-checkpoints',
    'よくある質問: WebとDBの関係は? DNSとは? 本番と開発の違いは? どこを見ればよいか。',
    '役割の違いを理解しないまま設定変更すると、どこが原因か分からなくなる。',
    '1. Web / App / DB の役割を分けて考える\n2. どのサーバーに入っているか把握する\n3. ドメイン名とIPの関係を知る\n4. ポートの意味を確認する\n5. 監視とログの場所を知る',
    'nslookup example.com\n dig example.com\n curl -I http://localhost\n ping 8.8.8.8\n traceroute example.com\n ss -lntp\n systemctl list-units --type=service',
    'インフラは「名前解決」「疎通」「サービス」「ログ」で整理すると理解しやすい。',
    '本番・検証・開発環境を混同しない。変更作業前に対象環境を確認する。',
    'published', 3, 1, @now,
    @now, @target_user_id, @now, @target_user_id
FROM categories c
WHERE c.category_name = 'インフラ'
  AND @target_user_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM dictionary_entries e
      WHERE e.user_id = @target_user_id AND e.slug = 'infra-basic-checkpoints'
  );

INSERT INTO dictionary_entries (
    user_id, category_id, title, slug,
    problem_summary, root_cause, check_points, command_examples,
    solution_summary, caution_notes,
    status, priority_level, version_no, published_at,
    created_at, created_by, updated_at, updated_by
)
SELECT
    @target_user_id,
    c.category_id,
    'shell: bashスクリプトの基本テンプレート',
    'shell-script-basics',
    'よくある質問: 変数の書き方は? if 文は? ループは? 引数はどう受ける? 実行権限は? ',
    'クォート不足、実行権限不足、相対パス依存、変数未定義がよくある失敗。',
    '1. shebang はあるか\n2. 実行権限はあるか\n3. 変数展開時にクォートしたか\n4. 終了コードを見たか\n5. set -eu を使うか',
    '#!/usr/bin/env bash\n set -eu\n name="world"\n echo "Hello ${name}"\n\n if [ $# -lt 1 ]; then\n   echo "usage: $0 <file>"\n   exit 1\n fi\n\n for f in "$@"; do\n   echo "target: $f"\n done\n\n chmod +x script.sh\n ./script.sh sample.txt',
    '最初は「変数」「if」「for」「引数」「終了コード」を押さえると実用に乗せやすい。',
    '空白を含む値は必ずクォートする。rm を自動化するときは特に注意する。',
    'published', 3, 1, @now,
    @now, @target_user_id, @now, @target_user_id
FROM categories c
WHERE c.category_name = 'shell'
  AND @target_user_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM dictionary_entries e
      WHERE e.user_id = @target_user_id AND e.slug = 'shell-script-basics'
  );

INSERT INTO dictionary_entries (
    user_id, category_id, title, slug,
    problem_summary, root_cause, check_points, command_examples,
    solution_summary, caution_notes,
    status, priority_level, version_no, published_at,
    created_at, created_by, updated_at, updated_by
)
SELECT
    @target_user_id,
    c.category_id,
    'sql: よく使うSELECT / JOIN / 集計パターン',
    'sql-basic-query-patterns',
    'よくある質問: WHERE の書き方は? JOIN はどう使う? GROUP BY で集計したい。',
    'テーブル同士の関係と、JOIN 条件の理解不足で誤集計が起きやすい。',
    '1. どのテーブルを起点にするか\n2. JOIN 条件は正しいか\n3. 期待件数は何件か\n4. LIMIT で途中確認したか\n5. 更新系SQLは SELECT で検証したか',
    'SELECT * FROM users LIMIT 10;\n SELECT user_id, login_id FROM users WHERE is_active = 1;\n SELECT category_id, category_name FROM categories ORDER BY sort_order;\n SELECT e.entry_id, e.title, c.category_name\n FROM dictionary_entries e\n JOIN categories c ON c.category_id = e.category_id\n ORDER BY e.updated_at DESC;\n SELECT category_id, COUNT(*) AS cnt\n FROM dictionary_entries\n GROUP BY category_id;',
    'まず単表SELECT、次にJOIN、最後に集計の順で組み立てると理解しやすい。',
    '本番データで UPDATE / DELETE を打つ前に、同条件の SELECT を必ず実行する。',
    'published', 3, 1, @now,
    @now, @target_user_id, @now, @target_user_id
FROM categories c
WHERE c.category_name = 'sql'
  AND @target_user_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM dictionary_entries e
      WHERE e.user_id = @target_user_id AND e.slug = 'sql-basic-query-patterns'
  );

-- --------------------------------------------------
-- 3. keywords 初期データ
-- --------------------------------------------------
INSERT IGNORE INTO dictionary_entry_keywords (entry_id, keyword, sort_order, created_at, created_by)
SELECT e.entry_id, kw.keyword, kw.sort_order, @now, @target_user_id
FROM dictionary_entries e
JOIN (
    SELECT 'debug-first-response' AS slug, 'log' AS keyword, 1 AS sort_order
    UNION ALL SELECT 'debug-first-response', 'error', 2
    UNION ALL SELECT 'debug-first-response', '500', 3
    UNION ALL SELECT 'debug-first-response', 'nginx', 4
    UNION ALL SELECT 'debug-first-response', 'php-fpm', 5
    UNION ALL SELECT 'debug-first-response', '切り分け', 6

    UNION ALL SELECT 'mysql-basic-operations', 'mysql', 1
    UNION ALL SELECT 'mysql-basic-operations', 'database', 2
    UNION ALL SELECT 'mysql-basic-operations', 'show tables', 3
    UNION ALL SELECT 'mysql-basic-operations', 'describe', 4
    UNION ALL SELECT 'mysql-basic-operations', 'grant', 5
    UNION ALL SELECT 'mysql-basic-operations', '権限', 6

    UNION ALL SELECT 'linux-basic-commands', 'linux', 1
    UNION ALL SELECT 'linux-basic-commands', 'ls', 2
    UNION ALL SELECT 'linux-basic-commands', 'pwd', 3
    UNION ALL SELECT 'linux-basic-commands', 'chmod', 4
    UNION ALL SELECT 'linux-basic-commands', 'chown', 5
    UNION ALL SELECT 'linux-basic-commands', 'grep', 6

    UNION ALL SELECT 'server-initial-checklist', 'systemctl', 1
    UNION ALL SELECT 'server-initial-checklist', 'df', 2
    UNION ALL SELECT 'server-initial-checklist', 'free', 3
    UNION ALL SELECT 'server-initial-checklist', 'ss', 4
    UNION ALL SELECT 'server-initial-checklist', 'journalctl', 5
    UNION ALL SELECT 'server-initial-checklist', '監視', 6

    UNION ALL SELECT 'git-daily-commands', 'git', 1
    UNION ALL SELECT 'git-daily-commands', 'commit', 2
    UNION ALL SELECT 'git-daily-commands', 'push', 3
    UNION ALL SELECT 'git-daily-commands', 'branch', 4
    UNION ALL SELECT 'git-daily-commands', 'diff', 5
    UNION ALL SELECT 'git-daily-commands', 'restore', 6

    UNION ALL SELECT 'ssh-common-troubleshooting', 'ssh', 1
    UNION ALL SELECT 'ssh-common-troubleshooting', 'known_hosts', 2
    UNION ALL SELECT 'ssh-common-troubleshooting', '鍵認証', 3
    UNION ALL SELECT 'ssh-common-troubleshooting', 'permission denied', 4
    UNION ALL SELECT 'ssh-common-troubleshooting', 'port 22', 5
    UNION ALL SELECT 'ssh-common-troubleshooting', '疎通', 6

    UNION ALL SELECT 'infra-basic-checkpoints', 'dns', 1
    UNION ALL SELECT 'infra-basic-checkpoints', 'port', 2
    UNION ALL SELECT 'infra-basic-checkpoints', 'curl', 3
    UNION ALL SELECT 'infra-basic-checkpoints', 'dig', 4
    UNION ALL SELECT 'infra-basic-checkpoints', 'nslookup', 5
    UNION ALL SELECT 'infra-basic-checkpoints', 'network', 6

    UNION ALL SELECT 'shell-script-basics', 'bash', 1
    UNION ALL SELECT 'shell-script-basics', 'shell', 2
    UNION ALL SELECT 'shell-script-basics', 'if', 3
    UNION ALL SELECT 'shell-script-basics', 'for', 4
    UNION ALL SELECT 'shell-script-basics', '引数', 5
    UNION ALL SELECT 'shell-script-basics', 'chmod +x', 6

    UNION ALL SELECT 'sql-basic-query-patterns', 'sql', 1
    UNION ALL SELECT 'sql-basic-query-patterns', 'select', 2
    UNION ALL SELECT 'sql-basic-query-patterns', 'join', 3
    UNION ALL SELECT 'sql-basic-query-patterns', 'group by', 4
    UNION ALL SELECT 'sql-basic-query-patterns', 'where', 5
    UNION ALL SELECT 'sql-basic-query-patterns', 'limit', 6
) kw ON kw.slug = e.slug
WHERE e.user_id = @target_user_id
  AND @target_user_id IS NOT NULL;

-- --------------------------------------------------
-- 4. version 1 の履歴データ作成
-- --------------------------------------------------
INSERT INTO dictionary_entry_histories (
    entry_id, version_no, category_id, title, slug,
    problem_summary, root_cause, check_points, command_examples,
    solution_summary, caution_notes, status, priority_level,
    keyword_snapshot, snapshot_created_at, snapshot_created_by
)
SELECT
    e.entry_id,
    1,
    e.category_id,
    e.title,
    e.slug,
    e.problem_summary,
    e.root_cause,
    e.check_points,
    e.command_examples,
    e.solution_summary,
    e.caution_notes,
    e.status,
    e.priority_level,
    (
        SELECT GROUP_CONCAT(k.keyword ORDER BY k.sort_order SEPARATOR ', ')
        FROM dictionary_entry_keywords k
        WHERE k.entry_id = e.entry_id
    ) AS keyword_snapshot,
    e.created_at,
    e.created_by
FROM dictionary_entries e
WHERE e.user_id = @target_user_id
  AND e.slug IN (
      'debug-first-response',
      'mysql-basic-operations',
      'linux-basic-commands',
      'server-initial-checklist',
      'git-daily-commands',
      'ssh-common-troubleshooting',
      'infra-basic-checkpoints',
      'shell-script-basics',
      'sql-basic-query-patterns'
  )
  AND @target_user_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM dictionary_entry_histories h
      WHERE h.entry_id = e.entry_id AND h.version_no = 1
  );

COMMIT;

-- --------------------------------------------------
-- 5. 確認用クエリ
-- --------------------------------------------------
SELECT category_id, category_name, sort_order
FROM categories
ORDER BY sort_order, category_id;

SELECT e.entry_id, e.title, e.slug, e.priority_level, c.category_name
FROM dictionary_entries e
JOIN categories c ON c.category_id = e.category_id
WHERE e.user_id = @target_user_id
ORDER BY e.priority_level DESC, e.updated_at DESC, e.entry_id DESC;
