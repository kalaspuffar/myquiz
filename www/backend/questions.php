<?php
    require_once(__DIR__ . '/head.php');
    if ($role != 1) die();
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
<?php
    $mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

    if (!$mysqli) {
        echo 'Connection failed<br>';
        echo 'Error number: ' . mysqli_connect_errno() . '<br>';
        echo 'Error message: ' . mysqli_connect_error() . '<br>';
        die();
    }

    if (!empty($_GET['delete'])) {
        $mysqli->query('DELETE FROM questions WHERE id = ' . (int) $_GET['delete']);
    }

    if (
        !empty($_POST['question']) && 
        !empty($_POST['option1']) && 
        !empty($_POST['option2']) && 
        !empty($_POST['option3']) && 
        !empty($_POST['option4']) && 
        !empty($_POST['correct'])
    ) {
        $arr = [];
        array_push($arr, $_POST['option1']);
        array_push($arr, $_POST['option2']);
        array_push($arr, $_POST['option3']);
        array_push($arr, $_POST['option4']);
        $answers = json_encode($arr);
        $stmt = $mysqli->prepare('INSERT INTO questions (question, answers, correct) VALUES (?,?,?)');
        $stmt->bind_param("sss", $_POST['question'], $answers, $_POST['correct']);
        $stmt->execute();
    }
?>
<div class="section hero">
    <div class="container">
        <h1>Manage questions</h1>
        <div class="row u-full-width button-row">
            <a class="button green" href="overview.php">
                Overview page
            </a>
            <a class="button green" href="submit_questions.php">
                Submit questions
            </a>
            <a class="button green" href="users.php">
                Users
            </a>
            <a class="button green" href="sessions.php">
                Session listing
            </a>
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
    <div class="container">
        <table class="u-full-width">
            <tr><th>ID</th><th>Question</th><th>Options</th><th>Answer</th><th>Buttons</th></tr>
        <?php
            $result = $mysqli->query('SELECT * FROM questions order by id desc;');
            foreach ($result as $row) {
                ?>
                <tr id="<?php echo $row['id'] ?>">
                    <td><?php echo $row['id'] ?></td>
                    <td><?php echo $row['question']; ?></td>
                    <td><?php echo $row['answers']; ?></td>
                    <td><?php echo $row['correct']; ?></td>
                    <td>
                        <button class="button approve" id="<?php echo $row['id'] ?>"><?php echo $row['approved'] == 1 ? 'Reject' : 'Approve' ?></button>
                        <button class="button save" id="<?php echo $row['id'] ?>">Edit</button>
                        <a class="button" href="?delete=<?php echo $row['id'] ?>">Delete</a>
                    </td>
                </tr>
                <?php
            }                
        ?>
        </table>
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

            const approveButtons = document.querySelectorAll('button.approve');
            approveButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const id = e.target.id;
                    var op = 'unknown';
                    if (e.target.innerText == 'APPROVE') {
                        op = 'approve';
                    } else if (e.target.innerText == 'REJECT') {
                        op = 'reject';
                    }
                    data = {
                        'op': op,
                        'id': id,                        
                    };
                    fetch('question_ajax.php', {
                        method: 'POST',
                        mode: 'same-origin',
                        cache: 'no-cache',
                        credentials: "same-origin",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify(data)
                    }).then(res => {
                        if (op == 'approve') {
                            e.target.innerText = 'reject';
                        } else {
                            e.target.innerText = 'approve';
                        }
                    });
                });
            });
            const saveButtons = document.querySelectorAll('button.save');
            saveButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const id = e.target.id;
                    if (e.target.innerText == 'EDIT') {
                        const el = document.querySelector('tr[id="' + id + '"]');
                        const questionEl = document.createElement('textarea');
                        questionEl.id = id + '_question';
                        questionEl.name = 'question';
                        questionEl.type = 'text';
                        questionEl.style = 'height: 310px; margin-top: 5px;';
                        questionEl.className = 'u-full-width';
                        questionEl.value = el.children[1].innerText;
                        el.children[1].innerHTML = '';
                        el.children[1].append(questionEl);
                        const options = JSON.parse(el.children[2].innerText);
                        el.children[2].innerHTML = '';
                        const selectedAnswer = el.children[3].innerText;
                        el.children[3].innerHTML = '';

                        var optionCount = 1;
                        const selectAnswerEl = document.createElement('select');
                        selectAnswerEl.id = id + '_correct';
                        options.forEach(option => {
                            const optionDiv = document.createElement('div');
                            optionDiv.className = 'u-full-width';
                            const optionLabel = document.createElement('label');
                            optionLabel.for = id + '_option' + optionCount;
                            optionLabel.innerHTML = 'Option ' + optionCount;
                            const optionInput = document.createElement('input');
                            optionInput.id = id + '_option' + optionCount;
                            optionInput.dataset.count = optionCount;
                            optionInput.name = 'option' + optionCount;
                            optionInput.type = 'text';
                            optionInput.className = 'u-full-width';
                            optionInput.value = option;
                            optionInput.addEventListener('keyup', function(e) {
                                setTimeout(function() {
                                    const correctOpt = document.getElementById(id + '_correct' + e.target.dataset.count);
                                    correctOpt.value = e.target.value;
                                    correctOpt.innerHTML = e.target.value;
                                }, 200);
                            });
                            optionDiv.append(optionLabel);
                            optionDiv.append(optionInput);
                            el.children[2].append(optionDiv);

                            const selectOption = document.createElement('option');
                            selectOption.id = id + '_correct' + optionCount;                            
                            selectOption.value = option;
                            selectOption.innerHTML = option;
                            if (selectedAnswer == option) {
                                selectOption.selected = 'selected';
                            }
                            selectAnswerEl.append(selectOption);
                            optionCount++;
                        });
                        el.children[3].append(selectAnswerEl);

                        e.target.innerText = 'SAVE';
                    } else if (e.target.innerText == 'SAVE') {
                        const el = document.querySelector('tr[id="' + id + '"]');                        
                        console.log(el);
                        data = {
                            'op': 'save',
                            'id': id,
                            'question': document.getElementById(id + '_question').value,
                            'option1': document.getElementById(id + '_option1').value,
                            'option2': document.getElementById(id + '_option2').value,
                            'option3': document.getElementById(id + '_option3').value,
                            'option4': document.getElementById(id + '_option4').value,
                            'correct': document.getElementById(id + '_correct').value,
                        };
                        fetch('question_ajax.php', {
                            method: 'POST',
                            mode: 'same-origin',
                            cache: 'no-cache',
                            credentials: "same-origin",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify(data)
                        }).then(res => {
                            return res.json();
                        }).then(data => {
                            if (data.status == 'ok') {
                                const el = document.querySelector('tr[id="' + id + '"]');
                                el.children[1].innerHTML = data.question;
                                el.children[2].innerHTML = data.answers;
                                el.children[3].innerHTML = data.correct;
                                e.target.innerText = 'EDIT';
                            }
                        });                                    
                    }
                });
            });
        </script>
    </div>
</div>

</body>
</html>

<?php 
    if ($mysqli) {
        $mysqli->close(); 
    }
?>