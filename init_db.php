<?php
$db = new SQLite3('db.sqlite');

// 建立 token 資料表
$db->exec("CREATE TABLE IF NOT EXISTS tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    agent_name TEXT NOT NULL,
    ip TEXT NOT NULL,
    token TEXT NOT NULL,
    create_time INTEGER NOT NULL,
    last_refresh INTEGER NOT NULL
)");

// 建立 admin 使用者 (帳號: admin, 密碼: password)
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY,
    username TEXT UNIQUE,
    password TEXT
)");

$hashedPassword = password_hash('password', PASSWORD_DEFAULT);
$db->exec("INSERT OR IGNORE INTO users (id, username, password) VALUES (1, 'admin', '$hashedPassword')");

echo "Database initialized.\n";
