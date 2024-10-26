<?php
require_once(__DIR__ . '/../../etc/config.php');
require_once(__DIR__ . '/../../include/mail_tools.php');

function curlCall($url, $method, $headers, $data) {
    array_push($headers, 'Content-type: application/x-www-form-urlencoded');
    array_push($headers, 'Content-Length: ' . strlen($data));
    array_push($headers, 'User-Agent: MyQuiz/0.1');

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($curl_error) {
        $response = [
            "status_code" => $info['http_code'],
            "message" => $curl_error
        ];
        return [$info['http_code'], json_encode($response)];
    } else {
        return [$info['http_code'], $response];
    }
}

function verify_captcha_token($token) {
    global $RECAPTHA_BACKEND_SECRET;

    $data = [
        'secret' => $RECAPTHA_BACKEND_SECRET,
        'response' => $token
    ];

    $response = curlCall(
        'https://www.google.com/recaptcha/api/siteverify', 
        'POST', 
        [], 
        http_build_query($data)
    );
    if (!isset($response[1])) {
        return false;
    }
    $json = json_decode($response[1]);
    if ($json == false) {
        return false;
    }
    return $json->success;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);
if (!$mysqli) {
    echo 'Connection failed<br>';
    echo 'Error number: ' . mysqli_connect_errno() . '<br>';
    echo 'Error message: ' . mysqli_connect_error() . '<br>';
    die();
}

$dontSendEmail = false;

if (
    !empty($_GET['auth_key']) && 
    $_GET['auth_key'] == $_COOKIE["auth_key"] && 
    !empty($_GET['new_user']) && 
    $_GET['new_user'] == 1 &&
    file_exists("/tmp/" . base64_encode($_COOKIE['auth_key']))
) {
    $email = file_get_contents("/tmp/" . base64_encode($_COOKIE['auth_key']));

    $stmt = $mysqli->prepare('INSERT INTO users (email, authkey, timeout) values (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
    $stmt->bind_param("ss", $email, $_COOKIE['auth_key']);
    $stmt->execute();

    header("Location: /backend/overview.php");
    exit();
} else if (!empty($_GET['auth_key']) && $_GET['auth_key'] == $_COOKIE["auth_key"]) {
    $stmt = $mysqli->prepare('UPDATE users SET timeout = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE authkey = ?');
    $stmt->bind_param("s", $_COOKIE['auth_key']);
    $stmt->execute();

    header("Location: /backend/overview.php");
    exit();
} else if (
    !empty($_POST['new_user']) && 
    $_POST['new_user'] == 1 &&
    !empty($_POST['auth_key']) && 
    !empty($_POST['email'])
) {
    $stmt = $mysqli->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        if (verify_captcha_token($_POST['captcha_token'])) {
            file_put_contents("/tmp/" . base64_encode($_POST['auth_key']), $_POST['email']);
            send_email($_POST['email'], 'MyQuiz login link', 'https://myquiz.app/backend/login.php?auth_key=' . $_POST['auth_key'] . '&new_user=1');    
        } else {
            $_POST['email'] = '';
        }
    } else {
        $auth_key = hash("sha256", random_bytes(2000));
        setcookie("auth_key", $auth_key, time()+3600);    
        $dontSendEmail = true;
    }
} else if (
    !empty($_POST['new_user']) && 
    $_POST['new_user'] == 2 &&
    !empty($_POST['auth_key']) && 
    !empty($_POST['email'])
) {
    $stmt = $mysqli->prepare('UPDATE users SET timeout = null, authkey = ? WHERE email = ?');
    $stmt->bind_param("ss", $_POST['auth_key'], $_POST['email']);
    $stmt->execute();

    if ($stmt->affected_rows == 1) {       
        send_email($_POST['email'], 'MyQuiz login link', 'https://myquiz.app/backend/login.php?auth_key=' . $_POST['auth_key']);
    } else {
        $auth_key = hash("sha256", random_bytes(2000));
        setcookie("auth_key", $auth_key, time()+3600);    
        $dontSendEmail = true;
    }
} else {
    $auth_key = hash("sha256", random_bytes(2000));
    setcookie("auth_key", $auth_key, time()+3600);
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Question</title>
        <link rel="stylesheet" href="../css/normalize.css">
        <link rel="stylesheet" href="../css/skeleton.css">
        <link rel="stylesheet" href="../css/custom.css?r=11">
    </head>
    <body class="admin">
        <div class="section hero">
            <div class="container">
                <?php
                if (
                    !empty($_POST['auth_key']) && 
                    !empty($_POST['email']) && $dontSendEmail === false
                ) { ?>

                <h1>Email have been sent to <?php echo $_POST['email'] ?></h1>
                
                <?php } else { ?>
                <h1>Login</h1>

                <?php 
                    if ($dontSendEmail) {
                        if ($_POST["new_user"] == 1) {
                            ?><h4 class="center red u-full-width">User already exists</h4><?php
                        } else {
                            ?><h4 class="center red u-full-width">User not registered</h4><?php
                        }
                    }
                ?>

                <form name="login_form" action="/backend/login.php" method="POST">
                    <input name="auth_key" type="hidden" value="<?php echo $auth_key ?>" />
                    <input id="captcha_token" name="captcha_token" type="hidden" value="" />
                    <input id="new_user" name="new_user" type="hidden" value="2" />
                    <div class="row">
                        <div class="column u-full-width">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="text" class="u-full-width">
                        </div>
                    </div>
                    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo $RECAPTHA_FRONTEND_SECRET ?>"></script>
                    <button onclick="javascript:login(event, false);" class="button button-answer button-primary">Login</button>
                    <button onclick="javascript:login(event, true);" class="button button-answer button-primary">New user</button>
                </form>

                <script>
                    function login(e, newuser) {
                        e.preventDefault();
                        if (newuser) {
                            document.getElementById('new_user').value = 1;
                            grecaptcha.ready(function() {
                                grecaptcha.execute('<?php echo $RECAPTHA_FRONTEND_SECRET ?>', {action: 'submit'}).then(function(token) {
                                    document.getElementById('captcha_token').value = token;
                                    document.login_form.submit();
                                });
                            });
                        } else {
                            document.login_form.submit();
                        }
                    }
                </script>

                <?php } ?>
            </div>
        </div>
    </body>
</html>
<?php