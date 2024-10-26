<?php
require_once(__DIR__ . '/../../etc/config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Implement security

$data = json_decode(file_get_contents('php://input'));

if ($data === false) die();

$mysqli = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

if (!$mysqli) {
    echo 'Connection failed<br>';
    echo 'Error number: ' . mysqli_connect_errno() . '<br>';
    echo 'Error message: ' . mysqli_connect_error() . '<br>';
    die();
}

if ($data->op == 'approve') {
    $mysqli->query('UPDATE questions SET approved = 1 WHERE id = ' . (int) $data->id);
} else if ($data->op == 'reject') {
    $mysqli->query('UPDATE questions SET approved = 0 WHERE id = ' . (int) $data->id);
} else if ($data->op == 'save') {
    $arr = [];
    array_push($arr, $data->option1);
    array_push($arr, $data->option2);
    array_push($arr, $data->option3);
    array_push($arr, $data->option4);
    $answers = json_encode($arr);
    $stmt = $mysqli->prepare('UPDATE questions SET question = ?, answers = ?, correct = ? WHERE id = ' . (int) $data->id);
    $stmt->bind_param("sss", $data->question, $answers, $data->correct);
    $stmt->execute();

    $res = [
        'status' => 'ok',
        'question' => $data->question,
        'answers' => $answers,
        'correct' => $data->correct,
    ];
    echo json_encode($res);
}
