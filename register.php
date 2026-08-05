<?php
include_once('db.php');

checkRU();

if (isset($user)) {
    header('Location:' . getRetUrl());
    exit();
}

if (isset($_GET['username'])) {
    $sql = 'select username from user where username = :username';
    $params = [
        ':username' => $_GET['username']
    ];
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);
    $username = $stmt->fetch();
    if ($username) {
        echo $username[0];
        exit();
    }
    echo "";
    exit();
}

if (isset(
    $_POST['regUsername'],
    $_POST['regPwd1'],
    $_POST['regPwd2'],
    $_POST['userType'],
    $_POST['fullname'],
    $_POST['email'],
    $_POST['specialization']
)) {
    $birth = ($_POST['birthday'] == "") ? null : $_POST['birthday'];
    $descr = ($_POST['description'] == "") ? null : $_POST['description'];

    //pwd check
    if ($_POST['regPwd1'] !== $_POST['regPwd2'] || !preg_match($pwdRegex, $_POST['regPwd1'])) {
        header('Location: index.php?info=pwdError');
        exit();
    }

    //control if username exists
    $sql = 'select username from user where username = :username';
    $params = [
        ':username' => $_POST['regUsername']
    ];
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);
    $username = $stmt->fetch();
    if ($username) {
        header('Location: index.php?info=usernameExists');
        exit();
    }

    //insert new user
    $sql = 'insert into user values(null,:user_type,true,:username,:pwd,:email,:fullname,:birth,:spec,:descr,:file_path)';
    $params = [
        ':user_type' => $_POST['userType'],
        ':username' => $_POST['regUsername'],
        ':pwd' => crypt($_POST["regPwd1"], '$5$rounds=5000$trebamozna$'),
        ':email' => $_POST['email'],
        ':fullname' => $_POST['fullname'],
        ':birth' => $birth,
        ':spec' => $_POST['specialization'],
        ':descr' => $descr,
        ':file_path' => '',
    ];
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);

    //login registered user
    if ($result !== false) {
        $insertedId = $conn->lastInsertId();
        $sql = 'select * from user where id = :id';
        $params = [
            ':id' => $insertedId,
        ];
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);
        $user = $stmt->fetch();
        if ($user !== false) {
            unset($user['password']);
            unset($user[4]);
            $_SESSION['user'] = $user;
            header('Location: profile.php?id=' . $user['id']);
            exit();
        }
    }
    header('Location: ' . getRetUrl());
    exit();
}

?>

<div id="myModal" class="d-none">
    <div class="modal-bg flex">
        <div id="regAlert" class="alert shadow-dark d-none">
            <p id="regAlertText"></p><span id="regAlertX" class="alert-x f-right">&#10006;</span>
        </div>
        <form action="register.php" method="POST" id="regForm">
            <h3>Nový účet:</h3>
            <div>
                <label for="regUsername">Username: </label>
                <input type="text" name="regUsername" id="regUsername" required minlength="5" autocomplete="off">
            </div>
            <div>
                <label for="regPwd1">Heslo:</label>
                <input type="password" name="regPwd1" id="regPwd1" required autocomplete="off">
            </div>
            <div>
                <label for="regPwd2">Zopakujte heslo:</label>
                <input type="password" name="regPwd2" id="regPwd2" required autocomplete="off">
            </div>
            <h3>Údaje:</h3>
            <div class="reg-radios">
                <p>Práci: </p>
                <input type="radio" name="userType" id="userTypeU" value="1" class="radio" checked>
                <label for="userTypeU">Hledám</label>
                <input type="radio" name="userType" id="userTypeC" value="2" class="radio">
                <label for="userTypeC">Nabízím</label>
            </div>
            <div>
                <label for="fullname">Celé jméno:</label>
                <input type="text" name="fullname" id="fullname" required autocomplete="off">
            </div>
            <div>
                <label for="email">Váš email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="unreq">
                <label for="birthday">Datum narození:</label>
                <input type="date" name="birthday" id="birthday">
            </div>
            <div>
                <label for="specialization">Vaše specializace:</label>
                <textarea id="specialization" name="specialization" rows="3" maxlength="150" placeholder="Vámi preferovaná pracovní pozice.." required></textarea>
            </div>
            <div class="unreq">
                <label for="description">Popište sebe:</label>
                <textarea id="description" name="description" rows="5" maxlength="500" placeholder="Popište svoje zkušennosti, preference nebo koníčky.."></textarea>
            </div>
            <div class="jd-btns">
                <a href="" id="regClose" class="btn btn-secondary f-left">Zpět</a>
                <button type="submit" class="btn btn-primary">Registrovat se</button>
            </div>
        </form>
    </div>
</div>
<div class="alert d-none">

</div>