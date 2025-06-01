<?php

abstract class Controller {
    protected $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    protected function redirect($url, $message = '', $type = 'success') {
        if ($message) {
            $_SESSION[$type] = $message;
        }
        header("Location: $url");
        exit;
    }

    protected function setError($message) {
        $_SESSION['error'] = $message;
    }

    protected function setSuccess($message) {
        $_SESSION['success'] = $message;
    }
}