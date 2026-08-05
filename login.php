<?php
include_once('db.php');

//checkRU();

if (isset($_POST["username"], $_POST["pwd"])) {
    $pwd = crypt($_POST["pwd"], '$5$rounds=5000$trebamozna$');

    $sql = 'select * from user where username = :username and password = :password';

    $params = [
        ':username' => $_POST["username"],
        ':password' => $pwd
    ];

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);
    if ($result === false) {
        var_dump($stmt->errorInfo());
    }
    $user = $stmt->fetch();
    if ($user !== false) {
        if ($user['active'] == 0) {
            // header('Location: ' . $_POST["username"]);
            session_destroy();
            header('Location: index.php?info=failedLogin');
            exit();
        }

        unset($user['password']);
        unset($user[4]);
        $_SESSION['user'] = $user;
        header('Location: ' . $_POST["returnUrl"]);
        exit();
    } else {
        header('Location: index.php?info=failedLogin');
        exit();
    }
}
?>

<div class="aside-login shadow-light">
    <form id="loginForm" action="login.php" method="POST">
        <input type="hidden" name="returnUrl" value="<?= $_SERVER['REQUEST_URI'] ?>">
        <div class="row-reg">
            <label for="username">Přihlašeni:</label>
            <input id="username" name="username" type="text" placeholder="" required>
        </div>
        <div class="row-reg">
            <label for="pwd">Heslo:</label>
            <input id="pwd" name="pwd" type="password" placeholder="" required>
        </div>
        <div class="row-reg">
            <a id="btnRegister" class="btn btn-secondary">Registrace</a>
            <button id="btnLogin" type="submit" class="btn btn-secondary">Přihlasit</button>
        </div>
    </form>
</div>