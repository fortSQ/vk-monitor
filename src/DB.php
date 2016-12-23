<?php

class DB {
    private static $instance;
    private $connection;

    private $host;
    private $database;
    private $user;
    private $password;
    private $table;

    private function __construct() {}
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setParams($host, $database, $user, $password)
    {
        $this->host     = $host;
        $this->database = $database;
        $this->user     = $user;
        $this->password = $password;

        return $this;
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }
    
    private function getDb()
    {
        if (!$this->connection) {
            $this->connection = new PDO("mysql:host={$this->host};dbname={$this->database}", $this->user, $this->password);
        }

        return $this->connection;
    }

    public function insertIntoTable($userId, $isOnline, $isMobile)
    {
        $stmt = $this->getDb()->prepare("INSERT INTO {$this->table}(user_id, is_online, is_mobile) VALUES (:user_id, :is_online, :is_mobile)");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':is_online', $isOnline);
        $stmt->bindValue(':is_mobile', $isMobile);
        $stmt->execute();
    }

    public function selectFromTable($userId, DateTime $date)
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM {$this->table} WHERE user_id=:user_id AND date BETWEEN :date_from AND :date_to");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':date_from', $date->format('Y-m-d') . ' 00:00:00');
        $stmt->bindValue(':date_to', $date->format('Y-m-d') . ' 23:59:59');
        $stmt->execute();

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $log) {
            $time = substr($log['date'], 11, 5);
            $result[$time] = [
                'is_online' => $log['is_online'] == 'y',
                'is_mobile' => $log['is_mobile'] == 'y',
            ];
        }

        return $result;
    }
}
