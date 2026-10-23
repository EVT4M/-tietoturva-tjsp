
<?php
require_once 'connect.php';

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
    <hr>

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

            <hr>
        </li>

    <?php endforeach; ?>

</ul>
