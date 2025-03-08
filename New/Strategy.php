<?php

require_once '../Board.php';
require_once '../Strategy/MoveStrategy.php';
require_once '../Strategy/RandomStrategy.php';
require_once '../Strategy/SmartStrategy.php';

$output = [
    'response' => false,
    'reason' => '',
    'pid' => ''
];


if (isset($_POST['strategy'])) {
    $strategy = $_POST['strategy'];

    if (empty($strategy)) {
        $output['reason'] = "Strategy not specified";
    }elseif ($strategy != 'Smart' && $strategy != 'Random') {
        $output['reason'] = "Unknown strategy";
    } else {
        $output['response'] = true;
        $output['pid'] = uniqid();
    }
} else {
    $output['reason'] = "Strategy not specified";
}

header('Content-Type: application/json');
echo json_encode($output);

?>
