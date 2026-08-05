<div class="flex aside-profile shadow-light"><a href="profile.php?id=<?= htmlspecialchars($user['id']) ?>" class="flex">
        <div class="aside-profile-img">
            <figure class="img-circle img-w-75">
                <?php if ($user['file_path'] != "") {
                    echo ' <img src="' . htmlspecialchars($user['file_path']) . '" alt="Foto aplikanta" class="">';
                } else {
                    echo ' <img src="assets/default-user.png" alt="Foto aplikanta" class="">';
                } ?>
                <figcaption>Vaše Foto</figcaption>
            </figure>
        </div>
        <div class="f-auto">
            <h2><?= htmlspecialchars($user['fullname']) ?></h2>
            <h5>Specializace:</h5>
            <p><?= htmlspecialchars($user['specialization']) ?></p>
        </div>
        <div>
            <h5>Popis:</h5>
            <p class=""><?= htmlspecialchars($user['description']) ?></p>
        </div>
    </a>
    <a href="logout.php?returnUrl=<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" class="btn btn-secondary btn-logout">
        <img src="assets/logout.png" width="18" alt="logout">
    </a>
</div>