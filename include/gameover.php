<?php

$session_id = $_GET['session'];
if (!isset($game_id) || !isset($session_id)) {
    showGameOverScreen();
}
if (!is_numeric($game_id) || preg_match('/[^a-zA-Z0-9]/', $session_id)) {
    showGameOverScreen();
}

$mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);
if (!$mysqli) {    
    showGameOverScreen();
}

$result = $mysqli->query(
    "SELECT UNIX_TIMESTAMP(session_end) FROM games WHERE game_id = $game_id AND session_id = '$session_id' AND session_end > now();"
);
if (!$result || mysqli_num_rows($result) == 0) {
    showGameOverScreen();
}
$row = $result->fetch_row();

$timeleft = $row[0] - time();

if ($timeleft < 0) {
    showGameOverScreen();
}

function showGameOverScreen() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>MyQuiz - Game over</title>
            <meta name="description" content="Small site to play quick quizes. This game is now finished, start a new game.">
            <meta name="author" content="Daniel Persson">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            
            <script async src="js/sha256.min.js"></script>
            
            <link rel="stylesheet" href="css/normalize.css">
            <link rel="stylesheet" href="css/skeleton.css">
            <link rel="stylesheet" href="css/custom.css">
        </head>
        <body>
            <div class="section hero">
                <div class="container" id="main_body">
                    <img id="gameover" src="images/gameover.png" />
                </div>
            </div>
        </body>
    </html>
    <?php
    die();
}
