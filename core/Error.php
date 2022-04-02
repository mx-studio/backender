<?php
namespace adjai\backender\core;

class Error {

    private $message;

    public function __construct($message) {
        $this->setMessage($message);
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message): void {
        $this->message = $message;
    }

}
