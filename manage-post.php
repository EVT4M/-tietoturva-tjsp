
<?php
session_start();
require_once 'connect.php';

if (empty($_SESSION['user']['id'])) {
    header('Location: index.php');
    exit;
}

$userId = (int) $_SESSION['user']['id'];

$stmt = $conn->prepare(
    "SELECT role FROM users WHERE id = :id"
);
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(403);
    exit('Ei käyttöoikeutta.');
}

$role = $user['role'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = filter_var(
    $_POST['id'] ?? $_GET['id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Virheellinen viestin ID.');
}

$stmt = $conn->prepare(
    "SELECT * FROM posts WHERE id = :id"
);
$stmt->execute(['id' => $id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    http_response_code(404);
    exit('Viestiä ei löydy.');
}

$canEdit =
    $role === 'admin' ||
    $role === 'moderator' ||
    ($role === 'user' &&
     $userId === (int)$post['author']);

$canDelete = $role === 'admin';

if (($action === 'edit' || $action === 'update') && !$canEdit) {
    http_response_code(403);
    exit('Sinulla ei ole oikeutta muokata viestiä.');
}

if ($action === 'delete' && !$canDelete) {
    http_response_code(403);
    exit('Sinulla ei ole oikeutta poistaa viestiä.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Virheellinen turvatunniste.');
    }

    if ($action === 'delete') {
        $stmt = $conn->prepare(
            "DELETE FROM posts WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        header('Location: home.php');
        exit;
    }

    if ($action === 'update') {
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');

        if ($title === '' || $body === '') {
            exit('Otsikko ja sisältö ovat pakollisia.');
        }

        $stmt = $conn->prepare(
            "UPDATE posts
             SET title = :title, body = :body
             WHERE id = :id"
        );

        $stmt->execute([
            'title' => $title,
            'body' => $body,
            'id' => $id
        ]);

        header('Location: home.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || $action !== 'edit') {
    http_response_code(400);
    exit('Virheellinen toiminto.');
}

if (!$canEdit) {
    http_response_code(403);
    exit('Ei muokkausoikeutta.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Muokkaa viestiä</title>
</head>
<body>

<h1>Muokkaa viestiä</h1>

<form action="manage-post.php" method="post">

    <input type="hidden" name="action" value="update">

    <input type="hidden" name="id"
           value="<?= (int)$post['id'] ?>">

    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(
               $_SESSION['csrf_token'],
               ENT_QUOTES,
               'UTF-8'
           ) ?>">

    <label for="title">Otsikko:</label><br>

    <input type="text" name="title" id="title"
           value="<?= htmlspecialchars(
               $post['title'],
               ENT_QUOTES,
               'UTF-8'
           ) ?>" required>

    <br><br>

    <label for="body">Viesti:</label><br>

    <textarea name="body" id="body"
              rows="8" cols="50"
              required><?= htmlspecialchars(
                  $post['body'],
                  ENT_QUOTES,
                  'UTF-8'
              ) ?></textarea>

    <br><br>

    <button type="submit">Tallenna muutokset</button>

</form>

<p><a href="home.php">Takaisin foorumiin</a></p>

</body>
</html>
