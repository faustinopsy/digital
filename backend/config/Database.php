<?php

 class Database {
 private $host = "localhost";
 private $db_name = "digital";
 private $username = "root";
 private $password = "root123";
 private $conn;
 private $db_type = "mysql"; // Opções: "mysql", "pgsql", "sqlite", "mssql"
 public function __construct() {
     $this->connect();
 }
 private function connect() {
  $this->conn = null;
  try {
    switch ($this->db_type) {
        case "mysql":
          $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
            break;
        case "pgsql":
            $dsn = "pgsql:host=" . $this->host . ";dbname=" . $this->db_name;
            break;
        case "sqlite":
            $dsn = "sqlite:" . $this->db_name;
            break;
        case "mssql":
           $dsn = "sqlsrv:Server=" . $this->host . ";Database=" . $this->db_name;
           break;
        default:
            throw new Exception("Database type not supported.");
      }
     $this->conn = new PDO($dsn, $this->username, $this->password);
     $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $exception) {
      echo "Connection error: " . $exception->getMessage();
  } catch (Exception $exception) {
      echo $exception->getMessage();
  }
}
public function create($table, $data) {
    $columns = implode(", ", array_keys($data));
    $placeholders = implode(", ", array_map(function($item) {
        return ":$item"; 
    }, array_keys($data)));
    $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $this->conn->prepare($query);
  foreach ($data as $key => $value) {
      $stmt->bindValue(":$key", $value);
  }
    return $stmt->execute();
}
public function read($table, $conditions = []) {
    $query = "SELECT * FROM $table";
    if (!empty($conditions)) {
        $conditionsStr = implode(" AND ", array_map(function($item) {
            return "$item = :$item";
        }, array_keys($conditions)));
        $query .= " WHERE $conditionsStr";
    }
    $stmt = $this->conn->prepare($query);
    foreach ($conditions as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->execute();
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function update($table, $data, $conditions) {
    $dataStr = implode(", ", array_map(function($item) {
        return "$item = :$item"; 
    }, array_keys($data)));
    $conditionsStr = implode(" AND ", array_map(function($item) { 
        return "$item = :condition_$item"; 
    }, array_keys($conditions)));
    $query = "UPDATE $table SET $dataStr WHERE $conditionsStr";
    $stmt = $this->conn->prepare($query);
    foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    foreach ($conditions as $key => $value) {
        $stmt->bindValue(":condition_$key", $value);
    }
   return $stmt->execute();
}
public function delete($table, $conditions) {
    $conditionsStr = implode(" AND ", array_map(function($item) {
        return "$item = :$item"; 
    }, array_keys($conditions)));
    $query = "DELETE FROM $table WHERE $conditionsStr";
    $stmt = $this->conn->prepare($query);
    foreach ($conditions as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
        return $stmt->execute();
    }
}