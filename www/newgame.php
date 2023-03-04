<?php
require_once(__DIR__ . '/../etc/config.php');

$mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);
if (!$mysqli) {    
    echo 'Connection failed<br>';
    die();
}


$unique_session_id = hash("sha256", random_bytes(2000));

$found = false;
while (!$found) {
    $found = true;
    $game_id = rand(100000, 999999);
    $result = $mysqli->query('SELECT * FROM games WHERE game_id = ' . $game_id . ' AND session_end > now();');
    if (!$result) {
        echo 'Query failed<br>';
        die();
    }    
    if (mysqli_num_rows($result) > 0) {
        $found = false;
    }
}

$result = $mysqli->query("INSERT INTO games (game_id, session_id, session_end)"
    . " VALUES ($game_id,'$unique_session_id', DATE_ADD(NOW(), INTERVAL 1 HOUR))");
if (!$result) {
    echo $mysqli->error;
    echo 'Could not create game<br>';
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyQuiz - New game</title>
    <meta name="description" content="Small site to play quick quizes. This screen facilitates creating new gaming sessions.">
    <meta name="author" content="Daniel Persson">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/skeleton.css">
    <link rel="stylesheet" href="css/custom.css">   
</head>
<body>
    <div class="section hero">
        <div class="container">
            <div class="row">
                <div class="one-half column">
                    <h2>New game</h2>
                    <p>
                        Time to start a new game, you will soon open up your own game master interface where, you can
                        open up the big view for conference calls and invite your collegues and friends to a game.<br/>
                    </p>
                    <p>
                        Your new game id is <span style="color:yellow"><?= $game_id ?></span>.<br/>
                    </p>
                    <p>
                        Have fun playing!
                    </p>
                    <form action="gamemaster.php" method="GET">
                        <input type="hidden" name="game" value="<?= $game_id ?>"/>
                        <input type="hidden" name="session" value="<?= $unique_session_id ?>"/>
                        <button class="button button-disabled" href="#">Get started</button>
                    </form>
                </div>
                <div class="one-half column">
                    <img class="playful" src="images/playful.jpeg">
                </div>
            </div>
        </div>
    </div>
</body>
</html>