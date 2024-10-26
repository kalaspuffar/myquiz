<?php
    require_once(__DIR__ . '/head.php');

    $mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

    if (!$mysqli) {
        echo 'Connection failed<br>';
        echo 'Error number: ' . mysqli_connect_errno() . '<br>';
        echo 'Error message: ' . mysqli_connect_error() . '<br>';
        die();
    }

    $num_questions = 0;
    $num_sessions = 0;

    $result = $mysqli->query('SELECT count(*) as num FROM questions;');
    foreach($result as $row) {
        $num_questions = $row["num"];
    }
    $result = $mysqli->query('SELECT count(*) as num FROM games WHERE session_end > NOW();');
    foreach($result as $row) {
        $num_sessions = $row["num"];
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Overview</title>
        <link rel="stylesheet" href="../css/normalize.css">
        <link rel="stylesheet" href="../css/skeleton.css">
        <link rel="stylesheet" href="../css/custom.css?r=1">
    </head>
    <body class="admin">    
        <div class="section">
            <div class="container">
                <h1>Backend overview</h1>
                <div class="row padding-bottom-5">
                    <div class="one-half column">
                        Number of questions: <?php echo $num_questions ?>
                    </div>
                    <div class="one-half column">
                        Number of active sessions: <?php echo $num_sessions ?> / 100
                    </div>                    
                </div>
                <div class="row u-full-width button-row">
                    <a class="button green" href="submit_questions.php">
                        Submit questions
                    </a>
                    <?php if ($role == 1) { ?>
                    <a class="button green" href="questions.php">
                        Manage questions
                    </a>
                    <a class="button green" href="users.php">
                        Users
                    </a>
                    <a class="button green" href="sessions.php">
                        Session listing
                    </a>
                    <?php } ?>
                    <a class="button green" href="newgame.php">
                       Start new game
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>