
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';

$role = $_SESSION['user']['role'] ?? 'user';
$currentUserId = (int)($_SESSION['user']['id'] ?? 0);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    $sql = "SELECT posts.*, users.realname
            FROM posts
            LEFT JOIN users ON posts.author = users.id
            ORDER BY posts.id DESC";

    $query = $conn->prepare($sql);
    $query->execute();

    $posts = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $posts = [];
}
?>

<ul>
    <?php foreach ($posts as $row): ?>

        <li>
            <h3>
                <?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?>
            </h3>

            <p>
                <i>
                    <?= htmlspecialchars($row['posted'], ENT_QUOTES, 'UTF-8') ?>
                    -
                    <?= htmlspecialchars($row['realname'] ?? 'Tuntematon', ENT_QUOTES, 'UTF-8') ?>
                </i>
            </p>

            <p>
                <?= nl2br(htmlspecialchars($row['body'], ENT_QUOTES, 'UTF-8')) ?>
            </p>

            <?php
            $canEdit =
                $role === 'admin' ||
                $role === 'moderator' ||
                ($currentUserId > 0 &&
                 $currentUserId === (int)$row['author']);

            $canDelete = $role === 'admin';
            ?>

            <?php if ($canEdit): ?>
                <a href="manage-post.php?action=edit&id=<?= (int)$row['id'] ?>">
                    Muokkaa
                </a>
            <?php endif; ?>

            <?php if ($canDelete): ?>
                <form action="manage-post.php"
                      method="post"
                      style="display:inline;">

                    <input type="hidden"
                           name="action"
                           value="delete">

                    <input type="hidden"
                           name="id"
                           value="<?= (int)$row['id'] ?>">

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <button type="submit">Poista</button>
                </form>
            <?php endif; ?>

            <hr>
        </li>

    <?php endforeach; ?>
</ul>
