CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    login_id TEXT NOT NULL UNIQUE,
    user_name TEXT NOT NULL,
    email TEXT UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'general',
    is_active INTEGER NOT NULL DEFAULT 1,
    last_login_at TEXT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS categories (
    category_id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_name TEXT NOT NULL UNIQUE,
    description TEXT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS dictionary_entries (
    entry_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    slug TEXT NOT NULL,
    problem_summary TEXT NULL,
    root_cause TEXT NULL,
    check_points TEXT NULL,
    command_examples TEXT NULL,
    solution_summary TEXT NULL,
    caution_notes TEXT NULL,
    status TEXT NOT NULL DEFAULT 'draft',
    priority_level INTEGER NOT NULL DEFAULT 3,
    version_no INTEGER NOT NULL DEFAULT 1,
    published_at TEXT NULL,
    created_at TEXT NOT NULL,
    created_by INTEGER NOT NULL,
    updated_at TEXT NOT NULL,
    updated_by INTEGER NOT NULL,
    deleted_at TEXT NULL,
    deleted_by INTEGER NULL,
    FOREIGN KEY(user_id) REFERENCES users(user_id),
    FOREIGN KEY(category_id) REFERENCES categories(category_id)
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_dictionary_entries_user_slug ON dictionary_entries(user_id, slug);
CREATE INDEX IF NOT EXISTS idx_dictionary_entries_user_id ON dictionary_entries(user_id);
CREATE INDEX IF NOT EXISTS idx_dictionary_entries_category_id ON dictionary_entries(category_id);
CREATE INDEX IF NOT EXISTS idx_dictionary_entries_status ON dictionary_entries(status);
CREATE INDEX IF NOT EXISTS idx_dictionary_entries_updated_at ON dictionary_entries(updated_at);

CREATE TABLE IF NOT EXISTS dictionary_entry_keywords (
    keyword_id INTEGER PRIMARY KEY AUTOINCREMENT,
    entry_id INTEGER NOT NULL,
    keyword TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    created_by INTEGER NOT NULL,
    FOREIGN KEY(entry_id) REFERENCES dictionary_entries(entry_id) ON DELETE CASCADE
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_dictionary_entry_keywords_entry_keyword ON dictionary_entry_keywords(entry_id, keyword);
CREATE INDEX IF NOT EXISTS idx_dictionary_entry_keywords_keyword ON dictionary_entry_keywords(keyword);

CREATE TABLE IF NOT EXISTS dictionary_entry_histories (
    history_id INTEGER PRIMARY KEY AUTOINCREMENT,
    entry_id INTEGER NOT NULL,
    version_no INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    slug TEXT NOT NULL,
    problem_summary TEXT NULL,
    root_cause TEXT NULL,
    check_points TEXT NULL,
    command_examples TEXT NULL,
    solution_summary TEXT NULL,
    caution_notes TEXT NULL,
    status TEXT NOT NULL,
    priority_level INTEGER NOT NULL,
    keyword_snapshot TEXT NULL,
    snapshot_created_at TEXT NOT NULL,
    snapshot_created_by INTEGER NOT NULL,
    FOREIGN KEY(entry_id) REFERENCES dictionary_entries(entry_id) ON DELETE CASCADE,
    FOREIGN KEY(category_id) REFERENCES categories(category_id)
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_dictionary_entry_histories_entry_version ON dictionary_entry_histories(entry_id, version_no);
CREATE INDEX IF NOT EXISTS idx_dictionary_entry_histories_entry_id ON dictionary_entry_histories(entry_id);
