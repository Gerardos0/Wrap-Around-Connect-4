<?php
class Board {
    private $board;

    public function __construct($numColumns = 7, $numRows = 6) {
        for ($row = 0; $row < $numRows; $row++) {
            for ($col = 0; $col < $numColumns; $col++) {
                $this->board[$row][$col] = 0;
            }
        }
    }

    public function isFull($column) {
        return $this->board[0][$column] != 0;
    }

    public function addPiece($column, $player) {
        for ($i = 5; $i >= 0; $i--) {
            if ($this->board[$i][$column] == 0) {
                $this->board[$i][$column] = $player;
                break;
            }
        }
    }
}
