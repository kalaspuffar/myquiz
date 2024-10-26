<?php
    require_once(__DIR__ . '/head.php');
    if ($role != 1) die();

    $mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

    if (!$mysqli) {
        echo 'Connection failed<br>';
        echo 'Error number: ' . mysqli_connect_errno() . '<br>';
        echo 'Error message: ' . mysqli_connect_error() . '<br>';
        die();
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
                <h1>Users</h1>
                <div class="row u-full-width button-row">
                    <a class="button green" href="overview.php">
                        Overview page
                    </a>
                    <a class="button green" href="submit_questions.php">
                        Submit questions
                    </a>
                    <a class="button green" href="sessions.php">
                        Session listing
                    </a>
                    <a class="button green" href="questions.php">
                        Manage questions
                    </a>
                    <a class="button green" href="newgame.php">
                       Start new game
                    </a>
                </div>  
            </div>              
            <div class="container">
                <table class="u-full-width">                        
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Auth Key</th>
                        <th>Role</th>
                    </tr>
                    <?php
                    
                    $result = $mysqli->query('SELECT * FROM users;');
                    foreach($result as $row) {
                    ?>
                    <tr>
                        <td><?php echo $row["id"] ?></td>
                        <td><?php echo $row["email"] ?></td>
                        <td><?php echo $row["authkey"] ?></td>
                        <td><?php echo $row["role"] == 1 ? 'Admin' : '' ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </body>
</html>