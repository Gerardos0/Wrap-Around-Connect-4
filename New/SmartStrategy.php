<?php
require_once 'MoveStrategy.php';

class SmartStrategy extends MoveStrategy {
    public function __construct(Board $board) {
        parent::__construct($board);
    }
    public function pickSlot() {
        $numColumns = 7;
        $validColumns = [];

        for ($i = 0; $i < $numColumns; $i++) {
            if (!$this->board->isFull($i)) {
                $validColumns[] = $i;
            }
        }

        if (empty($validColumns)) {
            return -1;
        }

        $randomIndex = rand(0, count($validColumns) - 1);
        return $validColumns[$randomIndex];
    }
}
?>