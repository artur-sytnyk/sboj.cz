<footer class="flex jc-between">
    <div class="f-left">&copy; Artur Sytnyk</div>
    <form id="searchForm" method="GET" action="index.php">
        <a href="index.php"><img src="assets/icons/logo-light-gy.png" width="30" alt="Domů"></a>
        <input name="search" type="text" placeholder="Vyhledat pracovní nabídky..." required>
        <button type="submit" class="btn btn-secondary">Vyhledat</button>
        <?php
        if ((isset($user)) && ($user["user_type"] == 3 || $user["user_type"] == 2)) { ?>
            <a href="detail.php?new=true" class="btn btn-secondary" id="newJobOffer">Nová nabídka</a>
        <?php } ?>
    </form>
    <div class="f-right"><a href="docs/index.html" class="btn btn-outline">Dokumentace</a></div>
</footer>