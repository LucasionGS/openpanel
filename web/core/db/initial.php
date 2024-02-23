<?php
function initial_database_setup(mysqli $sql, string $db_database) {
  // Drop the database if it exists
  if (!$sql->query("DROP DATABASE IF EXISTS $db_database")) {
    Logger::error("Error dropping database: " . $sql->error);
  }


  if ($sql->query("CREATE DATABASE IF NOT EXISTS $db_database")) {
    Logger::log("Database created successfully");
  } else {
    Logger::error("Error creating database: " . $sql->error);
  }

  $sql->select_db($db_database);
  
  $sql->query(
    "CREATE TABLE IF NOT EXISTS hosts (
      id INT AUTO_INCREMENT PRIMARY KEY,
      hostname VARCHAR(255),
      port INT DEFAULT 80,
      portssl INT DEFAULT 443
    )"
  );
  $sql->query(
    "CREATE TABLE IF NOT EXISTS users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(255),
      password VARCHAR(255)
    )"
  );
  $sql->query(
    "CREATE TABLE IF NOT EXISTS logs (
      id INT AUTO_INCREMENT PRIMARY KEY,
      message TEXT,
      timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
  );
  $sql->query(
    "CREATE TABLE IF NOT EXISTS settings (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255),
      value TEXT
    )"
  );
}