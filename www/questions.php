<?php
    require_once(__DIR__ . '/../etc/config.php');

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    if (isset($_POST["pass"]) && $_POST["pass"] == $QUESTION_LOCK) {
        setcookie("pass", $_POST["pass"], time()+3600); 
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question</title>
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/skeleton.css">
    <link rel="stylesheet" href="css/custom.css">   
</head>
<body>

<?php if ($_COOKIE["pass"] != $QUESTION_LOCK) { ?>
    <form action="#" method="POST">
        <input name="pass" type="password">
    </form>
<?php } else { ?>
    <?php
        $mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

        if (!$mysqli) {
            echo 'Connection failed<br>';
            echo 'Error number: ' . mysqli_connect_errno() . '<br>';
            echo 'Error message: ' . mysqli_connect_error() . '<br>';
            die();
        }

        if (!empty($_POST['question']) && !empty($_POST['answers']) && !empty($_POST['correct'])) {
            $stmt = $mysqli->prepare('INSERT INTO questions (question, answers, correct) VALUES (?,?,?)');
            $stmt->bind_param("sss", $_POST['question'], $_POST['answers'], $_POST['correct']);
            $stmt->execute();
        }
    ?>
    <div class="section hero">
        <div class="container">
            <form action="#" method="POST">
                <div class="row">
                    <div class="column u-full-width">
                        <label for="question">Question</label>
                        <input id="question" name="question" type="text" class="u-full-width">
                    </div>                
                </div>
                <div class="row">
                    <div class="column u-full-width">
                        <label for="answers">Answers</label>
                        <input id="answers" name="answers" type="text" class="u-full-width">
                    </div>                
                </div>
                <div class="row">
                    <div class="column u-full-width">
                        <label for="correct">Correct answer</label>
                        <input id="correct" name="correct" type="text" class="u-full-width">
                    </div>                
                </div>
                <button class="button button-answer button-primary">Submit</button>
            </form>
        </div>
        <div class="container">
            <table class="u-full-width">
                <tr><th>ID</th><th>Question</th><th>Options</th><th>Answer</th></tr>
            <?php
                $result = $mysqli->query('SELECT * FROM questions order by id desc;');
                foreach ($result as $row) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['question'] . '</td>';
                    echo '<td>' . $row['answers'] . '</td>';
                    echo '<td>' . $row['correct'] . '</td>';
                    echo '</tr>';                    
                }                
            ?>
            </table>
        </div>
    </div>

<?php 
        $mysqli->close();    
    } 
?>

</body>
</html>