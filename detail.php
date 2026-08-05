<?php
include_once("db.php");

checkRU();


$detailId = getFormVal('id');
$new = getFormVal('new');
$create = getFormVal('create');
$update = getFormVal('update');
$delete = getFormVal('delete');

if ($new && !$user) {
    header('Location: index.php?info=noLogin');
    exit();
}

//SELECT detail
if ($detailId) {
    $sql = 'select * from job where id = :id';
    $params = [
        ':id' => $detailId
    ];

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);
    if ($result === false) {
        var_dump($stmt->errorInfo());
    }
    $data = $stmt->fetch();

    if (isset($user) && $data['id_owner'] == $user['id']) {
        $sql = 'select user.id as id, user.fullname as fullname, user.specialization as spec, user.file_path as pfp
    from user join rel_user_job ruj on user.id = ruj.id_user where ruj.id_job = :id';
        $params = [
            ':id' => $detailId
        ];
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);
        $aplicants = $stmt->fetchAll();
    }
} else if (!$detailId && !$new && !$create) {
    header('Location: index.php?info=noDetailId');
    exit();
}

//CREATE new detail
if ($create) {
    if (!$user) {
        header('Location: index.php?info=noLogin');
        exit();
    }
    if ($user['user_type'] == 2 || $user['user_type'] == 3) {
        $sql = 'insert into job values(null,:id,:compName,:title,:address,:desc,:benefits,:logoPath,:link)';
        $params = [
            ':id' => $user["id"],
            ':compName' => $_POST['compName'],
            ':title' => $_POST['title'],
            ':address' => $_POST['address'],
            ':desc' => $_POST['desc'],
            ':benefits' => $_POST['benefits'],
            ':logoPath' => "",
            #':link' => $_POST['link'],
            ':link' => 'https://www.example.com/' . $_POST['title'],
        ];
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);

        if ($result !== false) {
            $lastInsertedId = $conn->lastInsertId();
            if (isset($_FILES['uploadImg']) && $_FILES['uploadImg']['error'] === UPLOAD_ERR_OK) {
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
                        $photo_new_name = base64_encode($_POST['compName']. 'x' . $lastInsertedId) . '.jpg'; // . $photo_ext;
                        $photo_destination = 'attachments/logos/' . $photo_new_name;
                        imagejpeg($temp_file, $photo_destination, 75);
                        //move_uploaded_file($photo_tmp_name, $photo_destination);

                        $sql = 'update job set logo_path = :logoPath where id = :id';
                        $params = [
                            ':id' => $lastInsertedId,
                            ':logoPath' => $photo_destination,
                        ];
                        $stmt = $conn->prepare($sql);
                        $stmt->execute($params);
                    } else {
                        header('Location: detail.php?new=true&info=fileToBig');
                        exit();
                    }
                } else {
                    header('Location: detail.php?new==true&info=fileUnsuported');
                    exit();
                }
            }
            header('Location: detail.php?id=' . $lastInsertedId);
            exit();
        } else {
            header('Location: detail.php?new=true&info=couldntCreate');
            exit();
        }
    }
}

$edit = getFormVal('edit');
if ($edit && !$user) {
    header('Location: detail.php?id=' . $_GET['id'] . '&info=noLogin');
    exit();
}
if ($edit && $user['id'] != $_GET['id'] && $user['user_type'] !== 3) {
    header('Location: detail.php?id=' . $_GET['id'] . '&info=wrongUser');
    exit();
}
//UDPATE detail
if ($update) {
    if (!$user && isset($_POST['id'])) {
        header('Location: detail.php?id=' . $_POST['id'] . '&info=noLogin');
        exit();
    }
    if ($_POST['id']) {
        $logoPath = "";
        if (isset($_FILES['uploadImg']) && $_FILES['uploadImg']['error'] === UPLOAD_ERR_OK) {
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
                    $photo_new_name = base64_encode($_POST['compName']. 'x' . $_POST['id']) . '.jpg'; // . $photo_ext;
                    $photo_destination = 'attachments/logos/' . $photo_new_name;
                    //Delete old image
                    if (file_exists($_POST['oldImg'])) {
                        unlink($_POST['oldImg']);
                    }
                    imagejpeg($temp_file, $photo_destination, 75);
                    //move_uploaded_file($photo_tmp_name, $photo_destination);
                    $logoPath = $photo_destination;
                } else {
                    header('Location: detail.php?id=' . $_POST['id'] . '&edit=true&info=fileToBig');
                    exit();
                }
            } else {
                header('Location: detail.php?id=' . $_POST['id'] . '&edit=true&info=fileUnsuported');
                exit();
            }
        }

        //$logoPath = 'attachments/logos/' . $_POST['compName'];
        $myLink = 'https://www.example.com/' . $_POST['compName'];

        $sql = 'update job set company_name = :compName, title = :title, address = :address, description = :desc, benefits = :benefits, logo_path = :logoPath, homepage_link = :link
        where id = :id and id_owner = :id_user';
        $params = [
            ':id' => $_POST['id'],
            ':id_user' => $user["id"],
            ':compName' => $_POST['compName'],
            ':title' => $_POST['title'],
            ':address' => $_POST['address'],
            ':desc' => $_POST['desc'],
            ':benefits' => $_POST['benefits'],
            ':logoPath' => $logoPath,
            #':link' => $_POST['link'],
            ':link' => $myLink,
        ];
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);
        header('Location: detail.php?id=' . $_POST['id']);
        exit();
    }
}

$aplied = false;
if (!$new && !$edit && $user) {
    $sql = 'select * from rel_user_job where id_user = :id_user and id_job = :id_job';
    $params = [
        ':id_user' => $user['id'],
        ':id_job' => $data['id']
    ];
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);
    if ($result === false) {
        var_dump($stmt->errorInfo());
    }
    $userJobRel = $stmt->fetch();
    if ($userJobRel) {
        $aplied = true;
    }
}

$apply = getFormVal('apply');
if ($apply && isset($_GET['id'])) {
    if (!$user) {
        header('Location: detail.php?info=noLogin');
        exit();
    }
    $sql = 'insert into rel_user_job values(null,:id_user,:id_job)';
    $params = [
        ':id_user' => $user["id"],
        ':id_job' => $_GET['id'],
    ];
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);
    header('Location: detail.php?id=' . $_GET['id']);
    exit();
}

//DELETE detail
if ($delete && isset($_GET['id'])) {
    if ($user['user_type'] == 3) {
        $sql = 'delete from job where id = :id';
        $params = [
            ':id' => $_GET['id'],
        ];
    } else {
        $sql = 'delete from job where id = :id and id_owner = :id_user'; //db has cascade on delete - therefore deletes from rel_user_job too 
        $params = [
            ':id_user' => $user["id"],
            ':id' => $_GET['id'],
        ];
    }
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);
    header('Location: index.php?info=jobDeleted');
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
        <form action="detail.php" method="POST" class="job-detail" enctype="multipart/form-data">
            <div class="popup-info shadow-gold">
                <h1>Detail nabídky</h1>
                <?php if ($aplied) {
                    echo '<p class="italic">Na tuto nabídku jste již reagoval/la</p>';
                } ?>
            </div>
            <?php if ($new) { ?>
                <div class="profile-pic f-left">
                    <figure class="img-circle img-w-100 f-left">
                        <img src="assets/default-company.png" alt="Logo Firmy" class="">
                    </figure>
                    <label for="uploadImg" class="upload-photo"><img src="assets/rewrite-b.png" width="20" alt="" class=""></label>
                    <input type="file" name="uploadImg" id="uploadImg" class="d-none" title=" ">
                </div>
                <div class="jd-info">
                    <div class="jd-fields">
                        <h4>Název nabídky:</h4>
                        <input name="title" type="text" class="" placeholder="Nazev pracovní pozice.." required>
                        <fieldset>
                            <legend>Firma</legend>
                            <textarea name="compName" class="jd-txtAr" rows="1" maxlength="80" placeholder="Nazev firmy" required></textarea>
                            <textarea name="link" class="jd-txtAr" rows="2" maxlength="500" placeholder="Zadejte adresu domovké stránky"></textarea>
                        </fieldset>
                        <fieldset>
                            <legend>Popis</legend>
                            <textarea name="desc" class="jd-txtAr" rows="5" required maxlength="2000"></textarea>
                        </fieldset>
                        <fieldset>
                            <legend>Adresa</legend>
                            <textarea name="address" class="jd-txtAr" rows="7" required maxlength="300"></textarea>
                        </fieldset>
                        <fieldset>
                            <legend>Benefity</legend>
                            <textarea name="benefits" class="jd-txtAr" rows="7" maxlength="800"></textarea>
                        </fieldset>
                    </div>
                    <div class="jd-btns"><a href="<?= htmlspecialchars(getRetUrl()) ?>" class="btn btn-secondary f-left">Zpět</a>
                        <button type="submit" name="create" value="true" class="btn btn-primary">Vytvořit</button>
                    </div>
                </div>
            <?php } else if (isset($data) && $edit) { ?>
                <div class="profile-pic f-left">
                    <figure class="img-circle img-w-100 f-left">
                        <?php if ($data['logo_path'] != "") {
                            echo '<img src="' . $data['logo_path'] . '" alt="Logo Firmy" class="">';
                        } else {
                            echo '<img src="assets/default-company.png" alt="Logo Firmy" class="">';
                        } ?>
                    </figure>
                    <label for="uploadImg" class="upload-photo"><img src="assets/rewrite-b.png" width="20px" alt="" class=""></label>
                    <input type="file" name="uploadImg" id="uploadImg" class="d-none" title=" ">
                    <input type="hidden" name="oldImg" value="<?= htmlspecialchars($data['logo_path']) ?>">
                </div>
                <div class="jd-info">
                    <input name="id" type="hidden" value="<?= htmlspecialchars($data['id']) ?>">
                    <div class="jd-fields">
                        <label for="title">
                            <h4>Název nabídky:</h4>
                            <input name="title" type="text" class="" placeholder="Nazev pracovní pozice.." required value="<?= htmlspecialchars($data['title']) ?>">
                        </label>
                        <fieldset>
                            <legend>Firma</legend>
                            <textarea name="compName" type="text" class="jd-txtAr" rows="1" maxlength="80" placeholder="Nazev firmy" required><?= htmlspecialchars($data['company_name']) ?></textarea>
                            <textarea name="link" class="jd-txtAr" rows="2" maxlength="500" placeholder="Zadejte adresu domovké stránky"><?= htmlspecialchars($data['homepage_link']) ?></textarea>
                        </fieldset>
                        <fieldset>
                            <legend>Popis</legend><textarea name="desc" class="jd-txtAr" rows="5" required maxlength="2000"><?= htmlspecialchars($data['description']) ?></textarea>
                        </fieldset>
                        <fieldset>
                            <legend>Adresa</legend><textarea name="address" class="jd-txtAr" rows="7" required maxlength="300"><?= htmlspecialchars($data['address']) ?></textarea>
                        </fieldset>
                        <fieldset>
                            <legend>Benefity</legend><textarea name="benefits" class="jd-txtAr" rows="7" maxlength="800"><?= htmlspecialchars($data['benefits']) ?></textarea>
                        </fieldset>
                    </div>
                    <div class="jd-btns"><a href="<?= htmlspecialchars(getRetUrl()) ?>" class="btn btn-secondary f-left">Zpět</a>
                        <button type="submit" name="update" value="true" class="btn btn-primary">Uložit</button>
                    </div>
                </div>
            <?php } else if (isset($data)) { ?>
                <figure class="img-circle img-w-100 f-left">
                    <?php if ($data['logo_path'] != "") {
                        echo '<img src="' . $data['logo_path'] . '" alt="Logo Firmy" class="">';
                    } else {
                        echo '<img src="assets/default-company.png" alt="Logo Firmy" class="">';
                    } ?>
                </figure>
                <div class="jd-info">
                    <div class="jd-fields">
                        <h2><?= htmlspecialchars($data['title']) ?></h2>
                        <fieldset>
                            <legend>Firma</legend><textarea class="d-none jd-txtAr" rows="7" maxlength="500"><?= htmlspecialchars($data['homepage_link']) ?></textarea>
                            <p><a class="jobLink" href="<?= htmlspecialchars($data['homepage_link']) ?>" target="_blank"><?= htmlspecialchars($data['company_name']) ?></a></p>
                        </fieldset>
                        <fieldset>
                            <legend>Popis</legend><textarea class="d-none jd-txtAr" rows="5" maxlength="2000"><?= htmlspecialchars($data['description']) ?></textarea>
                            <p><?= htmlspecialchars($data['description']) ?></p>
                        </fieldset>
                        <fieldset>
                            <legend>Adresa</legend><textarea class="d-none jd-txtAr" rows="7" maxlength="300"><?= htmlspecialchars($data['address']) ?></textarea>
                            <p><?= htmlspecialchars($data['address']) ?></p>
                        </fieldset>
                        <fieldset>
                            <legend>Benefity</legend><textarea class="d-none jd-txtAr" rows="7" maxlength="800"><?= htmlspecialchars($data['benefits']) ?></textarea>
                            <p><?= htmlspecialchars($data['benefits']) ?></p>
                        </fieldset>
                    </div>
                    <?php
                    $divForEcho = '<div class="jd-btns"><a href="' . getRetUrl() . '" class="btn btn-secondary f-left">Zpět</a>';
                    if ($user) {
                        if ($user['id'] === $data['id_owner'] || $user['user_type'] == 3) {
                            $divForEcho .= '<a href="detail.php?id=' . htmlspecialchars($data['id']) . '&delete=true" class="btn btn-primary">Smazat nabídku</a>';
                            $divForEcho .= '<a href="detail.php?id=' . htmlspecialchars($data['id']) . '&edit=true" id="jobDetailEdit" class="btn btn-primary">Upravit</a></div></div>';
                        } else if (!$aplied) {
                            $divForEcho .= '<a href="detail.php?id=' . htmlspecialchars($data['id']) . '&apply=true" class="btn btn-primary">Aplikovat</a></div></div>';
                        } else {
                            $divForEcho .= '</div></div>';
                        }
                    } else {
                        $divForEcho .= '</div></div>';
                    }
                    echo $divForEcho;
                    ?>
                <?php } ?>

                <?php if (isset($aplicants) && $aplicants) {
                    echo '<h2 class="header-gridlist">Na vaši nabídku reagovali:</h2><div class="gridlist">';

                    foreach ($aplicants as $a) { ?>
                        <a href="profile.php?id=<?= htmlspecialchars($a['id']) ?>">
                            <div class="clickable gridlist-item">
                                <figure class="img-circle img-w-50">
                                    <?php if ($a['pfp'] != "") {
                                        echo '<img src="' . $a['pfp'] . '" alt="Foto aplikanta" class="">';
                                    } else {
                                        echo '<img src="assets/default-user.png" alt="Foto aplikanta" class="">';
                                    } ?>
                                </figure>
                                <h4><?= htmlspecialchars($a['fullname']) ?></h4>
                                <p><?= htmlspecialchars($a['spec']) ?></p>
                            </div>
                        </a>
                <?php }
                    echo '</div>';
                } ?>
        </form>
    </main>

    <?php
    if (!isset($_SESSION['user'])) {
        include("login.php");
        include("register.php");
    } else {
        include("miniprofile.php");
    }
    ?>

    <?php include("footer.php") ?>

    <script src="js/main.js"></script>
</body>

</html>

<?php $_SESSION['returnUrl'] = $_SERVER['REQUEST_URI']; ?>