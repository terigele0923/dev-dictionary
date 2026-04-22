CREATE TABLE users (
    user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    login_id VARCHAR(100) NOT NULL UNIQUE,
    user_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'general',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    category_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE dictionary_entries (
    entry_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    problem_summary TEXT NULL,
    root_cause TEXT NULL,
    check_points TEXT NULL,
    command_examples MEDIUMTEXT NULL,
    solution_summary TEXT NULL,
    caution_notes TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    priority_level TINYINT UNSIGNED NOT NULL DEFAULT 3,
    version_no INT UNSIGNED NOT NULL DEFAULT 1,
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_at DATETIME NOT NULL,
    updated_by BIGINT UNSIGNED NOT NULL,
    deleted_at DATETIME NULL,
    deleted_by BIGINT UNSIGNED NULL,
    UNIQUE KEY uq_dictionary_entries_user_slug (user_id, slug),
    KEY idx_dictionary_entries_user_id (user_id),
    KEY idx_dictionary_entries_category_id (category_id),
    KEY idx_dictionary_entries_status (status),
    KEY idx_dictionary_entries_updated_at (updated_at),
    CONSTRAINT fk_dictionary_entries_user FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_dictionary_entries_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE dictionary_entry_keywords (
    keyword_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entry_id BIGINT UNSIGNED NOT NULL,
    keyword VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY uq_dictionary_entry_keywords_entry_keyword (entry_id, keyword),
    KEY idx_dictionary_entry_keywords_keyword (keyword),
    CONSTRAINT fk_dictionary_entry_keywords_entry FOREIGN KEY (entry_id) REFERENCES dictionary_entries(entry_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE dictionary_entry_histories (
    history_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entry_id BIGINT UNSIGNED NOT NULL,
    version_no INT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    problem_summary TEXT NULL,
    root_cause TEXT NULL,
    check_points TEXT NULL,
    command_examples MEDIUMTEXT NULL,
    solution_summary TEXT NULL,
    caution_notes TEXT NULL,
    status VARCHAR(20) NOT NULL,
    priority_level TINYINT UNSIGNED NOT NULL,
    keyword_snapshot TEXT NULL,
    snapshot_created_at DATETIME NOT NULL,
    snapshot_created_by BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY uq_dictionary_entry_histories_entry_version (entry_id, version_no),
    KEY idx_dictionary_entry_histories_entry_id (entry_id),
    CONSTRAINT fk_dictionary_entry_histories_entry FOREIGN KEY (entry_id) REFERENCES dictionary_entries(entry_id) ON DELETE CASCADE,
    CONSTRAINT fk_dictionary_entry_histories_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
