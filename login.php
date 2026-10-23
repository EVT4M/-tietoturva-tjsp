
<?php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: index.php');
    exit;
}

try {
    $sql = "SELECT id, username, realname, password
            FROM users
            WHERE username = :username
            LIMIT 1";

    $query = $conn->prepare($sql);
    $query->execute([
        'username' => $username
    ]);

    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'realname' => $user['realname']
        ];

        header('Location: home.php');
        exit;

    } else {
        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Tietokantavirhe.');
}
