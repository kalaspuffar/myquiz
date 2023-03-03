<?php
    require_once(__DIR__ . '/../etc/config.php');

if (!is_numeric($_GET['num']) && $_GET['num'] < 20) {
    die();
}

$seenQuestions = array();
array_push($seenQuestions, '0');
if (isset($_GET['seen'])) {
    $seenParam = explode(',', $_GET['seen']);
    foreach ($seenParam as $seen) {
        if (is_numeric($seen)) {
            array_push($seenQuestions, $seen);
        }
    }
}

$mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

if (!$mysqli) {
    echo 'Connection failed<br>';
    echo 'Error number: ' . mysqli_connect_errno() . '<br>';
    echo 'Error message: ' . mysqli_connect_error() . '<br>';
    die();
}

$result = $mysqli->query(
    "SELECT * FROM questions WHERE id NOT IN (" . implode(',', $seenQuestions) . ") ORDER BY RAND() LIMIT " . $_GET['num']
);
$questions = array();
foreach ($result as $row) {    
    $question = array(
        'id' => $row['id'],
        'question' => $row['question'],
        'answers' => json_decode($row['answers']),
        'correct' => $row['correct']
    );
    array_push($questions, $question);
}              
echo json_encode($questions);

