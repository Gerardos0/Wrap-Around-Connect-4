<?php

require_once 'Board.php';
require_once 'MoveStrategy.php';
require_once 'RandomStrategy.php';

$gameInfo = array( "width" => 7, "height" => 6, "strategies" => array("Smart", "Random"));

header('Content-Type: application/json');

echo json_encode($gameInfo);

?>
