<?php
/**
 * Setting Model (Key-Value Store)
 */
require_once 'models/BaseModel.php';

class Setting extends BaseModel
{

    // Get value by key
    public function get($key, $default = '')
    {
        $sql = "SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['setting_value'] : $default;
    }

    // Set value (Insert or Update)
    public function set($key, $value)
    {
        // Check if exists
        $sqlCheck = "SELECT id FROM settings WHERE setting_key = :key";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':key', $key);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            // Update
            $sql = "UPDATE settings SET setting_value = :val WHERE setting_key = :key";
        } else {
            // Insert
            $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (:key, :val)";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':val', $value);
        return $stmt->execute();
    }

    // Get multiple keys at once
    public function getMultiple($keys)
    {
        // Not strictly optimized (N queries), but fine for 5-6 settings
        $results = [];
        foreach ($keys as $k) {
            $results[$k] = $this->get($k);
        }
        return $results;
    }

    // Get ALL setting pairs [key => value]
    public function getAllPairs()
    {
        $sql = "SELECT setting_key, setting_value FROM settings";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pairs = [];
        foreach ($rows as $r) {
            $pairs[$r['setting_key']] = $r['setting_value'];
        }
        return $pairs;
    }
}
?>