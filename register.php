
<?php
require_once 'connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $realname = trim($_POST['realname'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $realname === '' || $password === '') {
        $message = "Täytä kaikki kentät!";
    } elseif (strlen($password) < 8) {
        $message = "Salasanan pitää olla vähintään 8 merkkiä.";
    } else {

        $sql = "SELECT id FROM users WHERE username = :username";
        $query = $conn->prepare($sql);
        $query->execute(['username' => $username]);

        if ($query->fetch()) {
            $message = "Käyttäjänimi on jo käytössä!";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, realname, password)
                    VALUES (:username, :realname, :password)";

            $query = $conn->prepare($sql);
            $query->execute([
                'username' => $username,
                'realname' => $realname,
                'password' => $hashedPassword
            ]);

            $message = "Käyttäjä luotu onnistuneesti!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Rekisteröidy</title>
</head>
<body>

    <h1>Luo uusi käyttäjä</h1>

    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>

    <form action="register.php" method="post">

        <label for="username">Käyttäjänimi:</label><br>
        <input type="text" name="username" id="username"
               maxlength="255" required><br><br>

        <label for="realname">Oikea nimi:</label><br>
        <input type="text" name="realname" id="realname"
               maxlength="255" required><br><br>

        <label for="password">Salasana:</label><br>
        <input type="password" name="password" id="password"
               minlength="8" required><br><br>

        <input type="submit" value="Rekisteröidy">

    </form>

    <p><a href="index.php">Takaisin kirjautumiseen</a></p>

</body>
</html>
