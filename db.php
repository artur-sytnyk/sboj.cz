<?php
if (session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loadEnv($path = __DIR__ . '/.env')
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name === '') {
            continue;
        }
        if (getenv($name) === false) {
            putenv("$name=$value");
        }
        if (!isset($_SERVER[$name])) {
            $_SERVER[$name] = $value;
        }
        if (!isset($_ENV[$name])) {
            $_ENV[$name] = $value;
        }
    }
}

loadEnv();

$dbDriver = strtolower(getenv('DB_DRIVER') ?: 'sqlite');

if ($dbDriver === 'sqlite') {
    $dbPath = getenv('DB_PATH') ?: 'database.sqlite';
    $isAbsolutePath = preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/]{2}|\/)/', $dbPath) === 1;
    if (!$isAbsolutePath) {
        $dbPath = __DIR__ . DIRECTORY_SEPARATOR . $dbPath;
    }

    $databaseIsNew = !file_exists($dbPath);
    $conn = new PDO('sqlite:' . $dbPath);
    $conn->exec('PRAGMA foreign_keys = ON');

    if ($databaseIsNew) {
        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        if ($schema === false) {
            throw new RuntimeException('Unable to load the SQLite schema.');
        }
        $conn->exec($schema);

        $portfolioSeed = file_get_contents(__DIR__ . '/database/portfolio_seed.sql');
        if ($portfolioSeed === false) {
            throw new RuntimeException('Unable to load the portfolio demo data.');
        }
        $conn->exec($portfolioSeed);
    }
} elseif ($dbDriver === 'mysql') {
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_NAME') ?: '';
    $dbUser = getenv('DB_USER') ?: '';
    $dbPass = getenv('DB_PASS') ?: '';
    $conn = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass);
} else {
    throw new RuntimeException("Unsupported DB_DRIVER: {$dbDriver}");
}

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);

// if ($_SESSION != null && !$_SESSION['returnUrl']) {
//     $_SESSION['returnUrl'] = $_SERVER['REQUEST_URI'];
// }

$pwdRegex = "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{6,}$/";

$user = null;
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}

if (!isset($_SESSION['ruArray']) || !isset($_SESSION['ruStep'])) {
    $_SESSION['ruArray'] = [];
    $_SESSION['ruStep'] = 0;
}

$alertText = "";
if (isset($_GET['info'])) {
    $info = $_GET['info'];
    switch ($info) {
        case 'noOffers':
            $alertText = 'Nebyly nalezeny žádné nabídky vyhovujicí vyhledaváni';
            break;
        case 'noLogin':
            $alertText = 'Musíte se přihlásit pro tuto akci';
            break;
        case 'jobDeleted':
            $alertText = 'Nabídka úspěšně odstraněná';
            break;
        case 'failedLogin':
            $alertText = 'Nepodařilo se přihlásit - nesprávné údaje';
            break;
        case 'emailExists':
            $alertText = 'Registrace neproběhla, tento e-mail je již registrován';
            break;
        case 'usernameExists':
            $alertText = 'Tento username nemůžete použít';
            break;
        case 'pwdError':
            $alertText = 'Heslá nejsou stejná, nebo nesplňuji podmínky (6+ znaku, aspoň jedno číslo a velké písmeno)';
            break;
        case 'wrongUser':
            $alertText = 'Pro tuto akci nemáte práva';
            break;
        case 'fileToBig':
            $alertText = 'Soubor příliš velký';
            break;
        case 'fileUnsuported':
            $alertText = 'Nahrávejte pouze JPG, JPEG a PNG obrázky';
            break;
        case 'noDetailId':
            $alertText = 'Detail nabídky nebyl nalezen';
            break;
        case 'couldntCreate':
            $alertText = 'Nepodařilo se vytvořit nabídku';
            break;
    }
}

// ##### ##### RU - Return Urls ##### ##### 
/**
 * Check return URL settings
 *
 * @return void
 */
function checkRU()
{
    global $alertText;
    if ($alertText != "") {
        return;
    }

    if (isset($_SESSION['ruStep']) && isRUinArray() != "") {
        //$step = $_SESSION['ruStep'] - 1;
        $step = isRUinArray();
        if ($step <= 0) {
            resetRU();
        }
        //$_SESSION['ruArray'][$step] = $_SERVER['REQUEST_URI'];
        $_SESSION['ruStep'] = $step;
    } else if (isset($_SESSION['ruStep'])) {
        $step = $_SESSION['ruStep'] + 1;
        if ($step > 5) {
            $step = 0;
            // resetRU();
        }
        $_SESSION['ruArray'][$step] = $_SERVER['REQUEST_URI'];
        $_SESSION['ruStep'] = $step;
    } else {
        resetRU();
    }
}
/**
 * Reset return URL settings
 *
 * @return void
 */
function resetRU()
{
    $_SESSION['ruArray'] = [];
    $_SESSION['ruStep'] = 0;
    $_SESSION['ruArray'][$_SESSION['ruStep']] = $_SERVER['REQUEST_URI'];
}
/**
 * Check if actuall URL is already in URL history (to prevent from duplicity)
 *
 * @return string
 */
function isRUinArray()
{
    foreach ($_SESSION['ruArray'] as $key => $value) {
        if ($_SERVER['REQUEST_URI'] == $value) {
            return $key;
        }
    }
    return "";
}
/**
 * Get URL for "back" buttons on actual page from URL history
 *
 * @return string
 */
function getRetUrl()
{
    // if (isset($_SESSION['returnUrl'])) {
    //     return $_SESSION['returnUrl'];
    // } else if (isset($_GET['returnUrl'])) {
    //     return $_GET['returnUrl'];
    // } else if (isset($_POST['returnUrl'])) {
    //     return $_POST['returnUrl'];
    // } else {
    //     return "index.php";
    // }
    $step = $_SESSION['ruStep'] - 1;
    if ($step < 0 || $step > 5) {
        $step = 5;
    }
    if (!isset($_SESSION['ruArray'][$step])) {
        return 'index.php';
    } else {
        return $_SESSION['ruArray'][$step];
    }
}
// ##### ##### RU - Return Urls ##### ##### 


/**
 * Get value of a key. Checks value of both $_POST and $_GET arrays
 *
 * @param  string $data
 * @return string
 */
function getFormVal($data)
{
    if (isset($_POST[$data])) {
        return htmlspecialchars(($_POST[$data]));
    } else if (isset($_GET[$data])) {
        return htmlspecialchars(($_GET[$data]));
    } else {
        return "";
    }
}

/**
 * Get age of user by given birth date
 *
 * @param  string $dateString
 * @return string
 */
function getAge($dateString)
{
    $birthDate = new DateTime($dateString);
    $now = new DateTime();
    $interval = $now->diff($birthDate);
    $result = $interval->y . ' let';
    return $result;
}
