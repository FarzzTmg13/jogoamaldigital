<?php

abstract class Model {
    protected $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method umum untuk menjalankan query SELECT
    protected function fetchAll($sql, $params = [], $types = '') {
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Method umum untuk menjalankan query INSERT/UPDATE/DELETE
    protected function executeQuery($sql, $params = [], $types = '') {
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        return $stmt->execute();
    }

    // Method abstrak harus didefinisikan di child class
    abstract public function save($conn);
    abstract public function update($conn);
}