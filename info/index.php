<?php
session_start();
require_once '../Board.php';
require_once '../Strategy/MoveStrategy.php';
require_once '../Strategy/RandomStrategy.php';
require_once '../Strategy/SmartStrategy.php';

$gameInfo = array( "width" => 7, "height" => 6, "strategies" => array("Smart", "Random"));




if (!isset($_SESSION['strategy'])):
?>
<html>
    <form method="POST">
        <label for="strategy">Choose a Strategy:</label><br>
        <input type="radio" name="strategy" value="smart" required> Smart
        <input type="radio" name="strategy" value="random" required> Random<br>
        <input type="submit" class="button" value="Submit">
    </form>
</html>
<?php endif;
$board = new board();
if (isset($_POST['strategy'])) {
    $strategy = $_POST['strategy'];
    echo "You selected the '$strategy' strategy!";
    if ($strategy == 'smart') {
        $strategy = new SmartStrategy($board);
    } elseif ($strategy == 'random') {
        $strategy = new RandomStrategy($board);
    }
}

echo json_encode($gameInfo);



?>
