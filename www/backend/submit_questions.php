<?php
    require_once(__DIR__ . '/head.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit questions</title>
    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/skeleton.css">
    <link rel="stylesheet" href="../css/custom.css?r=10">   
</head>
<body class="admin">
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
        <h1>Submit questions</h1>
        <div class="row u-full-width button-row">
            <a class="button green" href="overview.php">
                Overview page
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

        <form action="#" method="POST">
            <div class="row">
                <div class="column u-full-width">
                    <label for="question">Question</label>
                    <input id="question" name="question" type="text" class="u-full-width">
                </div>                
            </div>
            <div class="row">
                <div class="column u-full-width">
                    <label for="option1">Option 1</label>
                    <input id="option1" data-count="1" name="option1" type="text" class="u-full-width option">
                </div>                
            </div>
            <div class="row">
                <div class="column u-full-width">
                    <label for="option2">Option 2</label>
                    <input id="option2" data-count="2" name="option2" type="text" class="u-full-width option">
                </div>                
            </div>
            <div class="row">
                <div class="column u-full-width">
                    <label for="option3">Option 3</label>
                    <input id="option3" data-count="3" name="option3" type="text" class="u-full-width option">
                </div>                
            </div>
            <div class="row">
                <div class="column u-full-width">
                    <label for="option4">Option 4</label>
                    <input id="option4" data-count="4" name="option4" type="text" class="u-full-width option">
                </div>                
            </div>
            <div class="row">
                <div class="column u-full-width">
                    <label for="correct">Answer</label>
                    <select id="correct" name="correct" class="u-full-width">
                        <option id="correct1"></option>
                        <option id="correct2"></option>
                        <option id="correct3"></option>
                        <option id="correct4"></option>
                    </select>
                </div>                
            </div>
            <button class="button button-answer button-primary">Submit</button>
        </form>
    </div>
    <script>
            const optionInputs = document.querySelectorAll('input.option');
            optionInputs.forEach(input => {
                input.addEventListener('keyup', function(e) {
                    setTimeout(function() {
                        const correctOpt = document.getElementById('correct' + e.target.dataset.count);
                        correctOpt.value = e.target.value;
                        correctOpt.innerHTML = e.target.value;
                    }, 200);
                });
            });
    </script>    
</div>

</body>
</html>

<?php 
    if ($mysqli) {
        $mysqli->close(); 
    }
?>