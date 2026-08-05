<?php
include_once("db.php");

if (!$_GET) {
    resetRU();
} else {
    checkRU();
}

$jobOffers = [];
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $sql = 'select * from job where title like :search or company_name like :search or description like :search 
    order by title';
    $params = [
        ':search' => "%$search%"
    ];

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);
    if ($result === false) {
        var_dump($stmt->errorInfo());
    }
    $data = $stmt->fetchAll();
} else {
    $sql = 'select * from job order by title';

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute();
    if ($result === false) {
        var_dump($stmt->errorInfo());
    }
    $data = $stmt->fetchAll();
}

//pagination
$page = 1;
$maxPage = 1;
const itemsPerPage = 8; //set items amout per page (8 is perfect for page print)
if ($data) {
    $maxPage = ceil(count($data) / itemsPerPage);
    if (isset($_GET['page']) && $_GET['page'] <= $maxPage) {
        $page = $_GET['page'];
    }
    for ($i = 0; $i < itemsPerPage; $i++) {
        $pos = $i + (($page - 1) * itemsPerPage);
        if (!empty($data[$pos])) {
            $jobOffers[$i] = $data[$pos];
        }
    }
} else {
    header('Location: index.php?info=noOffers');
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
        <div class="job-list" id="job-list">
            <?php
            foreach ($jobOffers as $offer) { ?>
                <a href="detail.php?id=<?= htmlspecialchars($offer['id']) ?>">
                    <article class="job-article flex clickable">
                        <figure class="img-circle img-w-75">
                            <?php if ($offer['logo_path'] != "") {
                                echo '<img src="' . htmlspecialchars($offer['logo_path']) . '" alt="Logo Firmy" class="">';
                            } else {
                                echo '<img src="assets/default-company.png" alt="Logo Firmy" class="">';
                            } ?>
                        </figure>
                        <div class="job-body">
                            <h3><?= htmlspecialchars($offer['title']) ?></h3>
                            <p class=""><?= htmlspecialchars($offer['description']) ?></p>
                        </div>
                    </article>
                </a>
            <?php } ?>
        </div>
        <?php
        if (isset($_GET['search'])) {
            echo '<a href="index.php" class="btn btn-primary btn-back">&#171;</a>';
        }
        ?>
        <div class="pager flex">
            <?php
            $prevhidden = $nexthidden = $prevPage = $nextPage = "";
            if ($page <= 1) {
                $prevhidden = "hidden";
            } else {
                $prevPage = "index.php?page=" . ($page - 1);
            }
            if ($page >= $maxPage || $maxPage == 1) {
                $nexthidden = "hidden";
            } else {
                $nextPage = "index.php?page=" . ($page + 1);
            }
            if (isset($_GET['search'])) {
                $prevPage .= "&search=" . $_GET['search'];
                $nextPage .= "&search=" . $_GET['search'];
            }
            if (empty($jobOffers) || ( $page == $maxPage && $maxPage == 1) ) {
                $hidePage = "hidden";
            }
            ?>
            <a href="<?= $prevPage ?>" class="btn btn-secondary <?= $prevhidden ?>">&#8810;</a>
            <p class="<?= $hidePage ?>"><?= htmlspecialchars($page) ?> / <?= htmlspecialchars($maxPage) ?></p>
            <a href="<?= $nextPage ?>" class="btn btn-secondary <?= $nexthidden ?>">&#8811;</a>
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

    <?php include("footer.php") ?>

    <script src="js/main.js"></script>
</body>

</html>

<?php $_SESSION['returnUrl'] = $_SERVER['REQUEST_URI']; ?>