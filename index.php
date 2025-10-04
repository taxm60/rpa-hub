<?php
date_default_timezone_set('Asia/Taipei'); // 設定台北時區

session_start();
$db = new SQLite3('db.sqlite');

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bindValue(1, $username);
    $res = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($res && password_verify($password, $res['password'])) {
        $_SESSION['user'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "登入失敗，帳密錯誤";
    }
}

// Handle token deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_token']) && isset($_SESSION['user'])) {
    $stmt = $db->prepare("DELETE FROM tokens WHERE token = ?");
    $stmt->bindValue(1, $_POST['delete_token']);
    $stmt->execute();
}

// Handle AJAX token reload
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && isset($_SESSION['user'])) {
    $results = $db->query("SELECT * FROM tokens ORDER BY last_refresh DESC");
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['agent_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ip']) . "</td>";
        echo "<td class='small-token'>" . htmlspecialchars($row['token']) . "</td>";
        echo "<td>" . date('Y-m-d H:i:s', $row['create_time']) . "</td>";
        echo "<td>" . date('Y-m-d H:i:s', $row['last_refresh']) . "</td>";
        echo "<td>
                <form method='POST' onsubmit=\"return confirm('確定刪除此 Token 嗎？');\">
                    <input type='hidden' name='delete_token' value='" . htmlspecialchars($row['token']) . "'>
                    <button type='submit'>刪除</button>
                </form>
              </td>";
        echo "</tr>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>RPA Token 管理系統</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
            word-wrap: break-word;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        td form {
            margin: 0;
        }

        .small-token {
            font-family: monospace;
            font-size: 1.2em;
            word-break: break-word;
        }

        .top-bar {
            text-align: right;
            margin-bottom: 10px;
        }

        .top-actions {
            display: inline-block;
            background-color: #9e9e9e;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            user-select: none;
        }

        .top-actions button {
            background-color: transparent;
            border: none;
            color: white;
            font-weight: bold;
            cursor: pointer;
            padding: 0;
            margin-right: 6px;
        }

        .top-actions button:hover {
            text-decoration: underline;
        }

        .top-actions a {
            color: white;
            text-decoration: underline;
            margin-left: 4px;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if (!isset($_SESSION['user'])): ?>
        <h2>RPA 管理系統登入</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <div class="form-group">
                <label>帳號：</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>密碼：</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login">登入</button>
        </form>
    <?php else: ?>
        <div class="top-bar">
            <div class="top-actions">
                <button type="button" onclick="loadTokens()">🔄 刷新列表</button>
                |
                <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>
                (<a href="?logout=1">登出</a>)
            </div>
        </div>

        <h2>RPA Token 列表</h2>

        <table>
            <thead>
            <tr>
                <th>程式名稱</th>
                <th>用戶IP</th>
                <th>Token</th>
                <th>建立時間</th>
                <th>刷新時間</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody id="tokenTableBody">
                <!-- AJAX 讀取 -->
            </tbody>
        </table>

        <script>
            function loadTokens() {
                fetch("index.php?ajax=1")
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById("tokenTableBody").innerHTML = html;
                    })
                    .catch(err => {
                        console.error("AJAX 載入失敗：", err);
                    });
            }

            // 頁面載入時讀取
            loadTokens();

            // 每 30 秒自動刷新
            setInterval(loadTokens, 30000);
        </script>
    <?php endif; ?>
</div>

</body>
</html>
