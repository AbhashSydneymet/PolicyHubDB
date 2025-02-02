<?php
// config/database.php
class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'PolicyManagement';
    public $connection;

    public function __construct() {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
        
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public function closeConnection() {
        $this->connection->close();
    }
}
