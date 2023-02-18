<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyQuiz</title>
    <meta name="description" content="Small site to play quick quizes.">
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
                    <h4 class="hero-heading">
                        Soon there will be a site with playful joy where you can challange your friends and 
                        collegues to a friendly quiz challange.
                    </h4>
                    <button class="button button-disabled" href="#">Get started</button>
                </div>
                <div class="one-half column">
                    <img class="playful" src="images/playful.jpeg">
                </div>
            </div>
        </div>
    </div>

<?php
/*
require_once(__DIR__ . '/../etc/config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Hello world2<br>";
$mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

if (!$mysqli) {
   echo 'Connection failed<br>';
   echo 'Error number: ' . mysqli_connect_errno() . '<br>';
   echo 'Error message: ' . mysqli_connect_error() . '<br>';
   die();
}
echo 'Successfully connected!<br>';

$result = $mysqli->query('SELECT CHAR(92);');
foreach ($result as $row) {
    var_dump($row);
}

$mysqli->close();
echo 'After<br>';
*/
?>

</body>
</html>