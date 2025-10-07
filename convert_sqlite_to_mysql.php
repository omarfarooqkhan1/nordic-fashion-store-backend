<?php

/**
 * SQLite to MySQL Converter
 * This script reads a SQLite database and generates MySQL-compatible SQL
 */

$sqliteFile = 'database.sqlite';
$outputFile = 'database_mysql.sql';

if (!file_exists($sqliteFile)) {
    die("SQLite file not found: $sqliteFile\n");
}

try {
    // Connect to SQLite database
    $pdo = new PDO("sqlite:$sqliteFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to SQLite database: $sqliteFile\n";
    
    // Get all table names
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($tables) . " tables: " . implode(', ', $tables) . "\n";
    
    $sql = "-- MySQL dump generated from SQLite database\n";
    $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
    $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $sql .= "SET AUTOCOMMIT = 0;\n";
    $sql .= "START TRANSACTION;\n";
    $sql .= "SET time_zone = \"+00:00\";\n\n";
    
    foreach ($tables as $table) {
        echo "Processing table: $table\n";
        
        // Get table structure
        $createTable = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
        
        // Convert SQLite CREATE TABLE to MySQL
        $mysqlCreateTable = convertCreateTable($createTable, $table);
        $sql .= $mysqlCreateTable . "\n\n";
        
        // Get table data
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            echo "  Found " . count($rows) . " rows\n";
            
            // Get column names
            $columns = array_keys($rows[0]);
            $columnList = '`' . implode('`, `', $columns) . '`';
            
            $sql .= "-- Data for table `$table`\n";
            $sql .= "INSERT INTO `$table` ($columnList) VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $rowValues = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $rowValues[] = 'NULL';
                    } else {
                        $rowValues[] = "'" . addslashes($value) . "'";
                    }
                }
                $values[] = '(' . implode(', ', $rowValues) . ')';
            }
            
            $sql .= implode(",\n", $values) . ";\n\n";
        }
    }
    
    $sql .= "COMMIT;\n";
    
    // Write to file
    file_put_contents($outputFile, $sql);
    
    echo "\nConversion completed!\n";
    echo "MySQL SQL file created: $outputFile\n";
    echo "File size: " . number_format(filesize($outputFile)) . " bytes\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

/**
 * Convert SQLite CREATE TABLE statement to MySQL
 */
function convertCreateTable($sqliteSql, $tableName) {
    // Remove SQLite-specific syntax
    $mysqlSql = $sqliteSql;
    
    // Remove quotes around table name
    $mysqlSql = str_replace('"' . $tableName . '"', '`' . $tableName . '`', $mysqlSql);
    
    // Convert INTEGER PRIMARY KEY AUTOINCREMENT to AUTO_INCREMENT
    $mysqlSql = preg_replace('/INTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT/i', 'INT AUTO_INCREMENT PRIMARY KEY', $mysqlSql);
    
    // Convert INTEGER to INT
    $mysqlSql = preg_replace('/INTEGER/i', 'INT', $mysqlSql);
    
    // Convert TEXT to VARCHAR(255) for better compatibility
    $mysqlSql = preg_replace('/TEXT/i', 'VARCHAR(255)', $mysqlSql);
    
    // Convert REAL to DECIMAL
    $mysqlSql = preg_replace('/REAL/i', 'DECIMAL(10,2)', $mysqlSql);
    
    // Convert BLOB to LONGBLOB
    $mysqlSql = preg_replace('/BLOB/i', 'LONGBLOB', $mysqlSql);
    
    // Remove SQLite-specific constraints
    $mysqlSql = preg_replace('/\s+NOT\s+NULL\s+DEFAULT\s+NULL/', '', $mysqlSql);
    
    // Fix column definitions - add proper lengths
    $mysqlSql = preg_replace('/varchar\s+not\s+null/i', 'VARCHAR(255) NOT NULL', $mysqlSql);
    $mysqlSql = preg_replace('/varchar\s+null/i', 'VARCHAR(255) NULL', $mysqlSql);
    $mysqlSql = preg_replace('/INT\s+not\s+null/i', 'INT NOT NULL', $mysqlSql);
    $mysqlSql = preg_replace('/INT\s+null/i', 'INT NULL', $mysqlSql);
    
    // Remove quotes around column names and replace with backticks
    $mysqlSql = preg_replace('/"([^"]+)"/', '`$1`', $mysqlSql);
    
    // Add MySQL-specific settings
    $mysqlSql = rtrim($mysqlSql, ';');
    $mysqlSql .= " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    return $mysqlSql;
}

?>
