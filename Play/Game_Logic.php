<?php
require_once '../New/Strategy.php'

if (isset($_GET['pid']) && isset($_GET['move'])) {
    $pid = $_GET['pid'];
    $move = (int) $_GET['move'];

//---------------------------------------
// $game = getGameById($pid);
//---------------------------------------

//---------------------------------------
// Verify game is valid?
//---------------------------------------

     // Check if the move is valid (i.e., within the valid columns)
     if ($move < 0 || $move >= $game->getWidth()) {
         echo json_encode([
             'response' => false,
             'reason' => 'Invalid move (out of bounds)'
         ]);
         exit;
     }

     // Player makes the move (drop a disc in the chosen column)
     $ackMove = $game->playerMove($move);

     // Check if the move results in a win or a draw
     $isWin = $ackMove['isWin'];
     $isDraw = $ackMove['isDraw'];

     // If it's a winning or draw move, acknowledge and end the game
     if ($isWin || $isDraw) {
         echo json_encode([
             'response' => true,
             'ack_move' => $ackMove,
             'move' => null  // No need for computer's move since the game is over
         ]);
         exit;
     }

     // If it's not a winning or draw move, let the computer make its move
     $computerMove = $game->computerMove();  // Implement this in your Game class

     // Generate the response
     echo json_encode([
         'response' => true,
         'ack_move' => $ackMove,
         'move' => $computerMove  // Include the computer's move in the response
     ]);
 } else {
     // If parameters are missing, provide an exact error message
     echo json_encode([
         'response' => false,
         'reason' => 'Missing pid or move parameter'
     ]);
 }

