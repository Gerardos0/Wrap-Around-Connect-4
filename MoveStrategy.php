<?php
abstract class MoveStrategy {
    protected $board;

    public function __construct(Board $board = null) {
        $this->board = $board;
    }

    abstract public function pickSlot();

    public function toJson() {
        return array('name' => get_class($this));
    }

    public static function fromJson() {
        $strategy = new static();
        return $strategy;
    }
}
?>