<?php
include_once("db.php");

if (!$user) {
    header('Location: index.php');
}

checkRU();

$profileId = getFormVal('id');
if ($profileId) {
    $sql = 'select * from user where id = :id';
    $params = [
        ':id' => $profileId
    ];
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);
    $profile = $stmt->fetch();
    if ($user['user_type'] == 1 && $profile['id'] != $user['id']) {
        header('Location: ' . getRetUrl());
        exit();
    }
    unset($profile['password']);
    unset($profile[4]);

    //load applied positions
    if ($_SESSION['user']['id'] == $profileId) {
        $sql = 'select job.id as id, job.title as title, job.description as descrip, job.logo_path as logo
    from job join rel_user_job ruj on job.id = ruj.id_job where ruj.id_user = :id';
        $params = [
            ':id' => $profileId
        ];
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);
        $applications = $stmt->fetchAll();
    }
}

$edit = getFormVal('edit');
$update = getFormVal('update');
if ($update && ($user['id'] == $_POST['id'] || $user['user_type'] == 3)) {
    if (isset($_POST['pwd']) && $_POST['pwd'] != "") {
        //pwd check
        if ($_POST['pwd'] !== $_POST['pwd2'] || !preg_match($pwdRegex, $_POST['pwd'])) {
            header('Location: profile.php?id=' . $_POST['id'] . '&edit=true&info=pwdError');
            exit();
        }
    }

    if ($_POST['username'] != "" && $_POST['username'] != $_POST['oldUsername']) {
        $sql = 'select username from user where username = :username';
        $params = [
            ':username' => $_POST['username']
        ];
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);
        $username = $stmt->fetchAll();
        if (count($username) > 0) {
            header('Location: profile.php?id=' . $_POST['id'] . '&edit=true&info=usernameExists');
            exit();
        }
    }

    $bday = ($_POST['birthday'] != "") ? $_POST['birthday'] : "";
    $descr = ($_POST['desc'] != "") ? $_POST['desc'] : "";

    //default update
    $sql = 'update user set username = :username, fullname = :fullname, 
            description = :desc, specialization = :spec, birth_date = :birthDate
            where id = :id and email = :email';
    $params = [
        ':id' => $_POST['id'],
        ':email' => $_POST['email'],
        ':username' => $_POST['username'],
        ':fullname' => $_POST['nameSurname'],
        ':desc' => $_POST['desc'],
        ':spec' => $_POST['spec'],
        ':birthDate' => $bday,
    ];

    //if new file uploaded
    $filePath = "";
    if (isset($_FILES['uploadImg']) && $_FILES['uploadImg']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['uploadImg']['error'] !== UPLOAD_ERR_OK) {
            header('Location: profile.php?id=' . $_POST['id'] . '&edit=true&info=fileToBig');
            exit();
        }
        $photo = $_FILES['uploadImg'];
        $photo_name = $photo['name'];
        $photo_tmp_name = $photo['tmp_name'];
        $photo_size = $photo['size'];
        //$photo_ext = pathinfo($photo_name, PATHINFO_EXTENSION);
        $photo_ext = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
        $photo_allowed = ['jpg', 'jpeg', 'png'];
        if (in_array($photo_ext, $photo_allowed)) {
            if ($photo_size < 1048576) { //MB filesize
                switch ($photo_ext) {
                    case 'png':
                        $temp_file = imagecreatefrompng($photo_tmp_name);
                        break;
                    default:
                        $temp_file = imagecreatefromjpeg($photo_tmp_name);
                        break;
                }
                $photo_new_name = base64_encode($_POST['username']) . '.jpg';// . $photo_ext;
                $photo_destination = 'attachments/profile_pics/' . $photo_new_name;
                //Delete old image
                if (file_exists($_POST['oldImg'])) {
                    unlink($_POST['oldImg']);
                }
                //Move uploaded file into directory
                imagejpeg($temp_file, $photo_destination, 90);
                //move_uploaded_file($photo_tmp_name, $photo_destination);
                $filePath = $photo_destination;
            } else {
                header('Location: profile.php?id=' . $_POST['id'] . '&edit=true&info=fileToBig');
                exit();
            }
        } else {
            header('Location: profile.php?id=' . $_POST['id'] . '&edit=true&info=fileUnsuported');
            exit();
        }

        //file but not pwd
        $sql = 'update user set username = :username, fullname = :fullname, 
                description = :desc, specialization = :spec, birth_date = :birthDate, file_path = :filePath
                where id = :id and email = :email';
        $params[':filePath'] = $filePath;

        //file and pwd
        if (isset($_POST['pwd']) && $_POST['pwd'] != "") {
            $sql = 'update user set username = :username, password = :pwd, fullname = :fullname, 
                description = :desc, specialization = :spec, birth_date = :birthDate, file_path = :filePath
                where id = :id and email = :email';
            $params[':pwd'] = crypt($_POST["pwd"], '$5$rounds=5000$trebamozna$');
        }
    }

    //If user changes password
    if (isset($_POST['pwd']) && $_POST['pwd'] != "") {
        $sql = 'update user set username = :username, password = :pwd, fullname = :fullname, 
                description = :desc, specialization = :spec, birth_date = :birthDate
                where id = :id and email = :email';
        $params[':pwd'] = crypt($_POST["pwd"], '$5$rounds=5000$trebamozna$');
    }

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);

    //if same user > update his info
    if ($result !== false && $_SESSION['user']['id']  == $_POST['id']) {
        $sql = 'select * from user where id = :id';
        $params = [
            ':id' => $_SESSION['user']['id']
        ];
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);
        $userData = $stmt->fetch();
        unset($userData['password']);
        unset($userData[4]);
        $_SESSION['user'] = $userData;
    }
    header('Location: profile.php?id=' . $_POST['id']);
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sboj - najdi práci</title>
    <link rel="icon" type="image/x-icon" href="assets/icons/icon-light-y.png">
    <link rel="stylesheet" href="styles/style.css">
</head>

<body>
    <main class="flex">
        <?php if ($alertText != "") {
            echo '<div id="divAlert" class="alert shadow-dark"><p id="alertText">' . $alertText . '</p><span id="alertX" class="alert-x f-right">&#10006;</span></div>';
        } ?>
        <a href="<?= htmlspecialchars(getRetUrl()) ?>" class="btn btn-primary btn-back">&#171;</a>
        <div id="regAlert" class="alert shadow-dark d-none">
            <p id="regAlertText"></p><span id="regAlertX" class="alert-x f-right">&#10006;</span>
        </div>
        <form action="profile.php" method="POST" enctype="multipart/form-data" class="profile" id="profileForm">
            <?php if (isset($profile) && $edit && ($user['id'] == $_GET['id'] || $user['user_type'] == 3)) { ?>
                <div class="profile-pic f-left">
                    <figure class="img-circle img-w-100">
                        <?php if ($profile['file_path'] != "") {
                            echo '<img src="' . $profile['file_path'] . '" alt="User photo" class="">';
                        } else {
                            echo '<img src="assets/default-user.png" alt="User photo" class="">';
                        } ?>
                    </figure>
                    <label for="uploadImg" class="upload-photo"><img src="assets/rewrite-b.png" width="20" alt="" class=""></label>
                    <input type="file" name="uploadImg" id="uploadImg" class="d-none" title=" ">
                    <input type="hidden" name="oldImg" value="<?= htmlspecialchars($profile['file_path']) ?>">
                </div>
                <div>
                    <input type="hidden" name="email" value="<?= htmlspecialchars($profile['email']) ?>">
                    <h2><?= htmlspecialchars($profile['fullname']) ?></h2>
                    <div class="pf-date">
                        <p><?= htmlspecialchars(getAge($profile['birth_date'])) ?></p>
                        <input name="birthday" type="date" class="input-clean" value="<?= htmlspecialchars($profile['birth_date']) ?>">
                        <label>&#171; Změnit datum?</label>
                    </div>
                    <fieldset>
                        <legend>Specializace</legend><textarea name="spec" class="jd-txtAr" rows="3" maxlength="300" placeholder="Vámi preferovaná pracovní pozice.." required><?= htmlspecialchars($profile['specialization']) ?></textarea>
                    </fieldset>
                    <fieldset>
                        <legend>Popisek</legend><textarea name="desc" class="jd-txtAr" rows="5" maxlength="1000" placeholder="Popište sebe, svoje zkušennosti, preference a koníčky.."><?= htmlspecialchars($profile['description']) ?></textarea>
                    </fieldset>
                </div>
                <fieldset class="pf-acc">
                    <legend>Učet</legend>
                    <div><label for="nameSurname">Celé jméno:</label>
                        <input id="nameSurname" name="nameSurname" type="text" placeholder="Zadejte své celé jméno" value="<?= htmlspecialchars($profile['fullname']) ?>" required>
                    </div>
                    <div><label for="username">Změna username:</label>
                        <input id="username" name="username" type="text" placeholder="Nový username" value="<?= htmlspecialchars($profile['username']) ?>" required>
                        <input type="hidden" name="oldUsername" value="<?= htmlspecialchars($profile['username']) ?>">
                    </div>
                    <div><label for="pwd">Změna Hesla:</label>
                        <input id="pwd" name="pwd" type="password" placeholder="Nové heslo">
                        <input name="pwd2" type="password" placeholder="Zopakujte heslo">
                    </div>
                </fieldset>
                <div class="pf-btns">
                    <a href="<?= htmlspecialchars(getRetUrl()) ?>" class="btn btn-secondary f-left">Zpět</a>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id']) ?>">
                    <input type="hidden" name="update" value="true">
                    <button type="submit" class="btn btn-primary f-right">Uložit</button>
                </div>
            <?php } else if (isset($profile)) { ?>
                <div class="profile-pic f-left">
                    <figure class="img-circle img-w-100">
                        <?php if ($profile['file_path'] != "") {
                            echo '<img src="' . $profile['file_path'] . '" alt="User photo" class="">';
                        } else {
                            echo '<img src="assets/default-user.png" alt="User photo" class="">';
                        } ?>
                    </figure>
                </div>
                <div>
                    <h2><?= htmlspecialchars($profile['fullname']) ?></h2>
                    <div class="pf-date">
                        <p><?= htmlspecialchars(getAge($profile['birth_date'])) ?></p><input name="birthday" type="date" class="input-clean d-none">
                    </div>
                    <fieldset>
                        <legend>Specializace</legend><textarea class="jd-txtAr d-none" rows="3" maxlength="300" placeholder="Vámi preferovana pracovní pozice.." disabled></textarea>
                        <p><?= htmlspecialchars($profile['specialization']) ?></p>
                    </fieldset>
                    <fieldset>
                        <legend>Popisek</legend>
                        <textarea class="jd-txtAr d-none" rows="7" maxlength="1000" placeholder="Popište sebe, svoje zkušennosti, preference nebo koničky.." disabled></textarea>
                        <p><?= htmlspecialchars($profile['description']) ?></p>
                    </fieldset>
                </div>
                <div class="pf-btns"><a href="<?= htmlspecialchars(getRetUrl()) ?>" class="btn btn-secondary f-left">Zpět</a>
                    <?php
                    if ($user['id'] == getFormVal('id') || $user['user_type'] == 3) {
                        echo '<a href="profile.php?id=' . getFormVal('id') . '&edit=true" class="btn btn-primary f-right">Upravit profil</a>';
                    }
                    ?>
                </div>
            <?php } ?>
        </form>
        <div class="pf-lists">
            <?php if (isset($applications) && $applications) {
                echo '<h2 class="header-gridlist">Reagovali jste na tyhle nabídky</h2><div class="gridlist">';
                foreach ($applications as $apply) { ?>
                    <a href="detail.php?id=<?= htmlspecialchars($apply['id']) ?>">
                        <div class="clickable gridlist-item">
                            <figure class="img-circle img-w-50 f-left">
                                <?php if ($apply['logo'] != "") {
                                    echo ' <img src="' . htmlspecialchars($apply['logo']) . '" alt="Foto aplikanta" class="">';
                                } else {
                                    echo ' <img src="assets/default-company.png" alt="Foto aplikanta" class="">';
                                } ?>
                            </figure>
                            <h4><?= htmlspecialchars($apply['title']) ?></h4>
                            <p><?= htmlspecialchars($apply['descrip']) ?></p>
                        </div>
                    </a>
            <?php }
                echo '</div>';
            } ?>
        </div>
    </main>

    <?php
    if (!isset($_SESSION['user'])) {
        include("login.php");
        include("register.php");
    } else {
        include("miniprofile.php");
    }
    ?>

    <?php include("footer.php"); ?>

    <script src="js/main.js"></script>
</body>

</html>

<?php $_SESSION['returnUrl'] = $_SERVER['REQUEST_URI']; ?>