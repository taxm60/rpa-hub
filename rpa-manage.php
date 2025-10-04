<?php
$db = new SQLite3('db.sqlite');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

switch ($action) {

    case 'get_token':
        $agent_name = $data['agent_name'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (!$agent_name) {
            echo json_encode(['status' => false, 'error' => 'Missing agent_name']);
            exit;
        }

        $count = $db->querySingle("SELECT COUNT(*) FROM tokens");
        if ($count >= 5) {
            echo json_encode(['status' => false, 'error' => 'Too many active tokens']);
            exit;
        }

        $token = bin2hex(random_bytes(4));
        $time = time();
        $stmt = $db->prepare("INSERT INTO tokens (agent_name, ip, token, last_refresh) VALUES (?, ?, ?, ?)");
        $stmt = $db->prepare("INSERT INTO tokens (agent_name, ip, token, create_time, last_refresh) VALUES (?, ?, ?, ?, ?)");

        $stmt->bindValue(1, $agent_name);
        $stmt->bindValue(2, $ip);
        $stmt->bindValue(3, $token);
        $stmt->bindValue(4, $time); // create_time
        $stmt->bindValue(5, $time); // last_refresh
        $stmt->execute();

        echo json_encode(['status' => true, 'token' => $token]);
        break;

    case 'refresh_token':
        $token = $data['token'] ?? '';
        if (!$token) {
            echo json_encode(['status' => false, 'error' => 'Missing token']);
            exit;
        }

        $stmt = $db->prepare("UPDATE tokens SET last_refresh = ? WHERE token = ?");
        $stmt->bindValue(1, time());
        $stmt->bindValue(2, $token);
        $stmt->execute();

        echo json_encode(['status' => $db->changes() > 0]);
        break;

    case 'close_token':
        $token = $data['token'] ?? '';
        if (!$token) {
            echo json_encode(['status' => false, 'error' => 'Missing token']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM tokens WHERE token = ?");
        $stmt->bindValue(1, $token);
        $stmt->execute();

        echo json_encode(['status' => true]);
        break;

    default:
        echo json_encode(['status' => false, 'error' => 'Unknown action']);
}
