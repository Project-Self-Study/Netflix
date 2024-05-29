<?php
require_once 'config.php';
header('Content-Type: application/json');

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Singleton Class
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if ($this->conn->connect_error) {
            die(json_encode(['status' => 'error', 'timestamp' => time(), 'data' => 'Database connection failed: ' . $this->conn->connect_error]));
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
class Streams {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function updateShow($input) {
        $allowedFields = ['name', 'language', 'genres', 'status', 'runtime', 'premiered', 'officialSite', 'summary', 'rating', 'image'];
        $updateFields = [];
        $updateParams = [];
        $updateTypes = '';
    
        // Extract fields to be updated
        if (isset($input['update']) && is_array($input['update'])) {
            foreach ($input['update'] as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updateFields[] = "$field = ?";
                    $updateParams[] = $value;
                    $updateTypes .= $this->getType($value);
                } else {
                    return ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid update fields'];
                }
            }
        } else {
            return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to update'];
        }
    
        if (empty($updateFields)) {
            return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to update'];
        }
    
        $sql = "UPDATE shows SET " . implode(", ", $updateFields);
        $conditions = [];
        $params = [];
        $types = '';
    
        // Apply search filters if provided
        if (isset($input['search']) && !empty($input['search'])) {
            $search = $input['search'];
            if (isset($search['id'])) {
                $conditions[] = "id = ?";
                $params[] = $search['id'];
                $types .= 'i';
            }
            if (isset($search['name'])) {
                $conditions[] = "name LIKE ?";
                $params[] = "%" . $search['name'] . "%";
                $types .= 's';
            }
            if (isset($search['language'])) {
                $conditions[] = "language LIKE ?";
                $params[] = "%" . $search['language'] . "%";
                $types .= 's';
            }
            if (isset($search['genres'])) {
                $conditions[] = "genres LIKE ?";
                $params[] = "%" . $search['genres'] . "%";
                $types .= 's';
            }
            if (isset($search['status'])) {
                $conditions[] = "status LIKE ?";
                $params[] = "%" . $search['status'] . "%";
                $types .= 's';
            }
        }
    
        // Append conditions to the SQL query
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
    
        // Debugging: Output the constructed SQL query
        error_log("SQL Query: $sql");
    
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            // Debugging: Output the SQL error
            error_log("SQL Error: " . $this->db->error);
            return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
        }
    
        $allParams = array_merge($updateParams, $params);
        $allTypes = $updateTypes . $types;
    
        if ($allParams) {
            $stmt->bind_param($allTypes, ...$allParams);
        }
        $stmt->execute();
    
        if ($stmt->affected_rows > 0) {
            return ['status' => 'success', 'timestamp' => time(), 'data' => 'Show updated successfully'];
        } else {
            return ['status' => 'error', 'timestamp' => time(), 'data' => 'No records updated'];
        }
    }
    
    private function getType($value) {
        if (is_int($value)) {
            return 'i';
        } elseif (is_double($value)) {
            return 'd';
        } else {
            return 's';
        }
    }
    

    public function getAllShows($input) {
        $allowedFields = ['id', 'name', 'language', 'genres', 'status', 'runtime', 'premiered', 'officialSite', 'summary', 'rating', 'image'];
        $columns = $allowedFields;
        $limit = null;
    
        // Handle the 'return' parameter
        if (isset($input['return'])) {
            if ($input['return'] === '*') {
                // Return all fields
                $columns = $allowedFields;
            } elseif (is_numeric($input['return']) && $input['return'] > 0) {
                // Return the specified number of records
                $limit = (int)$input['return'];
            } else {
                // Invalid 'return' parameter
                return ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid return parameter'];
            }
        }
    
        $sql = "SELECT " . implode(", ", $columns) . " FROM shows";
        $conditions = [];
        $params = [];
        $types = '';
    
        // Apply search filters if provided
        if (isset($input['search'])) {
            $search = $input['search'];
            if (isset($search['name'])) {
                $conditions[] = "name LIKE ?";
                $params[] = "%" . $search['name'] . "%";
                $types .= 's';
            }
            if (isset($search['language'])) {
                $conditions[] = "language LIKE ?";
                $params[] = "%" . $search['language'] . "%";
                $types .= 's';
            }
            if (isset($search['genres'])) {
                $conditions[] = "genres LIKE ?";
                $params[] = "%" . $search['genres'] . "%";
                $types .= 's';
            }
            if (isset($search['status'])) {
                $conditions[] = "status LIKE ?";
                $params[] = "%" . $search['status'] . "%";
                $types .= 's';
            }
            if (isset($search['runtime_min'])) {
                $conditions[] = "runtime >= ?";
                $params[] = $search['runtime_min'];
                $types .= 'i';
            }
            if (isset($search['runtime_max'])) {
                $conditions[] = "runtime <= ?";
                $params[] = $search['runtime_max'];
                $types .= 'i';
            }
            if (isset($search['rating_min'])) {
                $conditions[] = "rating >= ?";
                $params[] = $search['rating_min'];
                $types .= 'd';
            }
            if (isset($search['rating_max'])) {
                $conditions[] = "rating <= ?";
                $params[] = $search['rating_max'];
                $types .= 'd';
            }
            if (isset($search['premiered_after'])) {
                $conditions[] = "premiered >= ?";
                $params[] = $search['premiered_after'];
                $types .= 's';
            }
            if (isset($search['premiered_before'])) {
                $conditions[] = "premiered <= ?";
                $params[] = $search['premiered_before'];
                $types .= 's';
            }
        }
    
        // Append conditions to the SQL query
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
    
        // Handle sorting
        if (isset($input['sort']) && in_array($input['sort'], $allowedFields)) {
            $sql .= " ORDER BY " . $input['sort'];
        } else {
            // Default sorting by name
            $sql .= " ORDER BY name";
        }
    
        $sql .= isset($input['order']) && strtoupper($input['order']) == 'DESC' ? " DESC" : " ASC";
    
        // Apply limit
        if ($limit !== null) {
            $sql .= " LIMIT " . $limit;
        }
    
        // Debugging: Output the constructed SQL query
        error_log("SQL Query: $sql");
    
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            // Debugging: Output the SQL error
            error_log("SQL Error: " . $this->db->error);
            return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
        }
    
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $shows = $result->fetch_all(MYSQLI_ASSOC);
    
        return ['status' => 'success', 'timestamp' => time(), 'data' => $shows];
    }
    
    



public function getAllSeasons($input) {
    $allowedFields = ['id', 'show_id', 'number', 'episode_count', 'premiere_date', 'end_date', 'summary'];
    $columns = $allowedFields;
    $limit = null;

    // Handle the 'return' parameter
    if (isset($input['return'])) {
        if ($input['return'] === '*') {
            // Return all fields
            $columns = $allowedFields;
        } elseif (is_numeric($input['return']) && $input['return'] > 0) {
            // Return the specified number of records
            $limit = (int)$input['return'];
        } else {
            // Invalid 'return' parameter
            return ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid return parameter'];
        }
    }

    $sql = "SELECT " . implode(", ", $columns) . " FROM seasons";
    $conditions = [];
    $params = [];
    $types = '';

    // Apply search filters if provided
    if (isset($input['search']) && !empty($input['search'])) {
        $search = $input['search'];
        if (isset($search['show_id'])) {
            $conditions[] = "show_id = ?";
            $params[] = $search['show_id'];
            $types .= 'i';
        }
        if (isset($search['number'])) {
            $conditions[] = "number = ?";
            $params[] = $search['number'];
            $types .= 'i';
        }
        if (isset($search['episode_count_min'])) {
            $conditions[] = "episode_count >= ?";
            $params[] = $search['episode_count_min'];
            $types .= 'i';
        }
        if (isset($search['episode_count_max'])) {
            $conditions[] = "episode_count <= ?";
            $params[] = $search['episode_count_max'];
            $types .= 'i';
        }
        if (isset($search['premiere_date_after'])) {
            $conditions[] = "premiere_date >= ?";
            $params[] = $search['premiere_date_after'];
            $types .= 's';
        }
        if (isset($search['premiere_date_before'])) {
            $conditions[] = "premiere_date <= ?";
            $params[] = $search['premiere_date_before'];
            $types .= 's';
        }
        if (isset($search['end_date_after'])) {
            $conditions[] = "end_date >= ?";
            $params[] = $search['end_date_after'];
            $types .= 's';
        }
        if (isset($search['end_date_before'])) {
            $conditions[] = "end_date <= ?";
            $params[] = $search['end_date_before'];
            $types .= 's';
        }
    }

    // Append conditions to the SQL query
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // Handle sorting
    if (isset($input['sort']) && in_array($input['sort'], $allowedFields)) {
        $sql .= " ORDER BY " . $input['sort'];
    } else {
        // Default sorting by number
        $sql .= " ORDER BY number";
    }

    $sql .= isset($input['order']) && strtoupper($input['order']) == 'DESC' ? " DESC" : " ASC";

    // Apply limit
    if ($limit !== null) {
        $sql .= " LIMIT " . $limit;
    }

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $seasons = $result->fetch_all(MYSQLI_ASSOC);

    return ['status' => 'success', 'timestamp' => time(), 'data' => $seasons];
}

    




public function getPeople($input) {
    $allowedFields = ['id', 'name', 'birthday', 'deathday', 'gender', 'country', 'biography', 'image'];
    $columns = $allowedFields;
    $limit = null;

    // Handle the 'return' parameter
    if (isset($input['return'])) {
        if ($input['return'] === '*') {
            // Return all fields
            $columns = $allowedFields;
        } elseif (is_numeric($input['return']) && $input['return'] > 0) {
            // Return the specified number of records
            $limit = (int)$input['return'];
        } else {
            // Invalid 'return' parameter
            return ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid return parameter'];
        }
    }

    $sql = "SELECT " . implode(", ", $columns) . " FROM peoples";
    $conditions = [];
    $params = [];
    $types = '';

    // Apply search filters if provided
    if (isset($input['search']) && !empty($input['search'])) {
        $search = $input['search'];
        if (isset($search['name'])) {
            $conditions[] = "name LIKE ?";
            $params[] = "%" . $search['name'] . "%";
            $types .= 's';
        }
        if (isset($search['gender'])) {
            $conditions[] = "gender LIKE ?";
            $params[] = "%" . $search['gender'] . "%";
            $types .= 's';
        }
        if (isset($search['country'])) {
            $conditions[] = "country LIKE ?";
            $params[] = "%" . $search['country'] . "%";
            $types .= 's';
        }
        if (isset($search['birthday_after'])) {
            $conditions[] = "birthday >= ?";
            $params[] = $search['birthday_after'];
            $types .= 's';
        }
        if (isset($search['birthday_before'])) {
            $conditions[] = "birthday <= ?";
            $params[] = $search['birthday_before'];
            $types .= 's';
        }
        if (isset($search['deathday_after'])) {
            $conditions[] = "deathday >= ?";
            $params[] = $search['deathday_after'];
            $types .= 's';
        }
        if (isset($search['deathday_before'])) {
            $conditions[] = "deathday <= ?";
            $params[] = $search['deathday_before'];
            $types .= 's';
        }
    }

    // Append conditions to the SQL query
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // Handle sorting
    if (isset($input['sort']) && in_array($input['sort'], $allowedFields)) {
        $sql .= " ORDER BY " . $input['sort'];
    } else {
        // Default sorting by name
        $sql .= " ORDER BY name";
    }

    $sql .= isset($input['order']) && strtoupper($input['order']) == 'DESC' ? " DESC" : " ASC";

    // Apply limit
    if ($limit !== null) {
        $sql .= " LIMIT " . $limit;
    }

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $people = $result->fetch_all(MYSQLI_ASSOC);

    return ['status' => 'success', 'timestamp' => time(), 'data' => $people];
}


public function getEpisodes($input) {
    $allowedFields = ['id', 'show_id', 'season', 'number', 'name', 'airdate', 'runtime', 'summary', 'rating', 'image'];
    $columns = $allowedFields;
    $limit = null;

    // Handle the 'return' parameter
    if (isset($input['return'])) {
        if ($input['return'] === '*') {
            // Return all fields
            $columns = $allowedFields;
        } elseif (is_numeric($input['return']) && $input['return'] > 0) {
            // Return the specified number of records
            $limit = (int)$input['return'];
        } else {
            // Invalid 'return' parameter
            return ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid return parameter'];
        }
    }

    $sql = "SELECT " . implode(", ", $columns) . " FROM episodes";
    $conditions = [];
    $params = [];
    $types = '';

    // Apply search filters if provided
    if (isset($input['search']) && !empty($input['search'])) {
        $search = $input['search'];
        if (isset($search['show_id'])) {
            $conditions[] = "show_id = ?";
            $params[] = $search['show_id'];
            $types .= 'i';
        }
        if (isset($search['season'])) {
            $conditions[] = "season = ?";
            $params[] = $search['season'];
            $types .= 'i';
        }
        if (isset($search['number'])) {
            $conditions[] = "number = ?";
            $params[] = $search['number'];
            $types .= 'i';
        }
        if (isset($search['name'])) {
            $conditions[] = "name LIKE ?";
            $params[] = "%" . $search['name'] . "%";
            $types .= 's';
        }
        if (isset($search['airdate_after'])) {
            $conditions[] = "airdate >= ?";
            $params[] = $search['airdate_after'];
            $types .= 's';
        }
        if (isset($search['airdate_before'])) {
            $conditions[] = "airdate <= ?";
            $params[] = $search['airdate_before'];
            $types .= 's';
        }
        if (isset($search['runtime_min'])) {
            $conditions[] = "runtime >= ?";
            $params[] = $search['runtime_min'];
            $types .= 'i';
        }
        if (isset($search['runtime_max'])) {
            $conditions[] = "runtime <= ?";
            $params[] = $search['runtime_max'];
            $types .= 'i';
        }
        if (isset($search['rating_min'])) {
            $conditions[] = "rating >= ?";
            $params[] = $search['rating_min'];
            $types .= 'd';
        }
        if (isset($search['rating_max'])) {
            $conditions[] = "rating <= ?";
            $params[] = $search['rating_max'];
            $types .= 'd';
        }
    }

    // Append conditions to the SQL query
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // Handle sorting
    if (isset($input['sort']) && in_array($input['sort'], $allowedFields)) {
        $sql .= " ORDER BY " . $input['sort'];
    } else {
        // Default sorting by name
        $sql .= " ORDER BY name";
    }

    $sql .= isset($input['order']) && strtoupper($input['order']) == 'DESC' ? " DESC" : " ASC";

    // Apply limit
    if ($limit !== null) {
        $sql .= " LIMIT " . $limit;
    }

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $episodes = $result->fetch_all(MYSQLI_ASSOC);

    return ['status' => 'success', 'timestamp' => time(), 'data' => $episodes];
}




    

public function getCrewCast($input) {
    $allowedFields = ['id', 'show_id', 'season', 'number', 'name', 'airdate', 'runtime', 'summary', 'rating', 'image'];
    $columns = $allowedFields;
    $limit = null;

    // Handle the 'return' parameter
    if (isset($input['return'])) {
        if ($input['return'] === '*') {
            // Return all fields
            $columns = $allowedFields;
        } elseif (is_numeric($input['return']) && $input['return'] > 0) {
            // Return the specified number of records
            $limit = (int)$input['return'];
        } else {
            // Invalid 'return' parameter
            return ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid return parameter'];
        }
    }

    $sql = "SELECT " . implode(", ", $columns) . " FROM crew_cast";
    $conditions = [];
    $params = [];
    $types = '';

    // Apply search filters if provided
    if (isset($input['search']) && !empty($input['search'])) {
        $search = $input['search'];
        if (isset($search['show_id'])) {
            $conditions[] = "show_id = ?";
            $params[] = $search['show_id'];
            $types .= 'i';
        }
        if (isset($search['season'])) {
            $conditions[] = "season = ?";
            $params[] = $search['season'];
            $types .= 'i';
        }
        if (isset($search['number'])) {
            $conditions[] = "number = ?";
            $params[] = $search['number'];
            $types .= 'i';
        }
        if (isset($search['name'])) {
            $conditions[] = "name LIKE ?";
            $params[] = "%" . $search['name'] . "%";
            $types .= 's';
        }
        if (isset($search['airdate_after'])) {
            $conditions[] = "airdate >= ?";
            $params[] = $search['airdate_after'];
            $types .= 's';
        }
        if (isset($search['airdate_before'])) {
            $conditions[] = "airdate <= ?";
            $params[] = $search['airdate_before'];
            $types .= 's';
        }
        if (isset($search['runtime_min'])) {
            $conditions[] = "runtime >= ?";
            $params[] = $search['runtime_min'];
            $types .= 'i';
        }
        if (isset($search['runtime_max'])) {
            $conditions[] = "runtime <= ?";
            $params[] = $search['runtime_max'];
            $types .= 'i';
        }
        if (isset($search['rating_min'])) {
            $conditions[] = "rating >= ?";
            $params[] = $search['rating_min'];
            $types .= 'd';
        }
        if (isset($search['rating_max'])) {
            $conditions[] = "rating <= ?";
            $params[] = $search['rating_max'];
            $types .= 'd';
        }
    }

    // Append conditions to the SQL query
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // Handle sorting
    if (isset($input['sort']) && in_array($input['sort'], $allowedFields)) {
        $sql .= " ORDER BY " . $input['sort'];
    } else {
        // Default sorting by name
        $sql .= " ORDER BY name";
    }

    $sql .= isset($input['order']) && strtoupper($input['order']) == 'DESC' ? " DESC" : " ASC";

    // Apply limit
    if ($limit !== null) {
        $sql .= " LIMIT " . $limit;
    }

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $crew_cast = $result->fetch_all(MYSQLI_ASSOC);

    return ['status' => 'success', 'timestamp' => time(), 'data' => $crew_cast];
}




public function updateEpisodes($input) {
    $allowedFields = ['show_id', 'season', 'number', 'name', 'airdate', 'runtime', 'summary', 'rating', 'image'];
    $updateFields = [];
    $updateParams = [];
    $updateTypes = '';

    // Extract fields to be updated
    if (isset($input['update']) && is_array($input['update'])) {
        foreach ($input['update'] as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updateFields[] = "$field = ?";
                $updateParams[] = $value;
                $updateTypes .= $this->getType($value);
            } else {
                return ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid update fields'];
            }
        }
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to update'];
    }

    if (empty($updateFields)) {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to update'];
    }

    $sql = "UPDATE episodes SET " . implode(", ", $updateFields);
    $conditions = [];
    $params = [];
    $types = '';

    // Apply search filters if provided
    if (isset($input['search']) && !empty($input['search'])) {
        $search = $input['search'];
        if (isset($search['id'])) {
            $conditions[] = "id = ?";
            $params[] = $search['id'];
            $types .= 'i';
        }
        if (isset($search['show_id'])) {
            $conditions[] = "show_id = ?";
            $params[] = $search['show_id'];
            $types .= 'i';
        }
        if (isset($search['season'])) {
            $conditions[] = "season = ?";
            $params[] = $search['season'];
            $types .= 'i';
        }
        if (isset($search['number'])) {
            $conditions[] = "number = ?";
            $params[] = $search['number'];
            $types .= 'i';
        }
        if (isset($search['name'])) {
            $conditions[] = "name LIKE ?";
            $params[] = "%" . $search['name'] . "%";
            $types .= 's';
        }
    }

    // Append conditions to the SQL query
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    $allParams = array_merge($updateParams, $params);
    $allTypes = $updateTypes . $types;

    // Debugging: Ensure types and params match
    error_log("Types: $allTypes");
    error_log("Params: " . implode(", ", $allParams));

    if (count($allParams) !== strlen($allTypes)) {
        error_log("Mismatch between number of types and parameters");
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Mismatch between number of types and parameters'];
    }

    if ($allParams) {
        $stmt->bind_param($allTypes, ...$allParams);
    }
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return ['status' => 'success', 'timestamp' => time(), 'data' => 'Episode updated successfully'];
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No records updated'];
    }
}



public function updateSeasons($input) {
    $allowedFields = ['show_id', 'number', 'episode_count', 'premiere_date', 'end_date', 'summary'];
    $updateFields = [];
    $updateParams = [];
    $updateTypes = '';

    // Extract fields to be updated
    if (isset($input['update']) && is_array($input['update'])) {
        foreach ($input['update'] as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updateFields[] = "$field = ?";
                $updateParams[] = $value;
                $updateTypes .= $this->getType($value);
            } else {
                return ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid update fields'];
            }
        }
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to update'];
    }

    if (empty($updateFields)) {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to update'];
    }

    $sql = "UPDATE seasons SET " . implode(", ", $updateFields);
    $conditions = [];
    $params = [];
    $types = '';

    // Apply search filters if provided
    if (isset($input['search']) && !empty($input['search'])) {
        $search = $input['search'];
        if (isset($search['id'])) {
            $conditions[] = "id = ?";
            $params[] = $search['id'];
            $types .= 'i';
        }
        if (isset($search['show_id'])) {
            $conditions[] = "show_id = ?";
            $params[] = $search['show_id'];
            $types .= 'i';
        }
        if (isset($search['number'])) {
            $conditions[] = "number = ?";
            $params[] = $search['number'];
            $types .= 'i';
        }
    }

    // Append conditions to the SQL query
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    $allParams = array_merge($updateParams, $params);
    $allTypes = $updateTypes . $types;

    // Debugging: Ensure types and params match
    error_log("Types: $allTypes");
    error_log("Params: " . implode(", ", $allParams));

    if (count($allParams) !== strlen($allTypes)) {
        error_log("Mismatch between number of types and parameters");
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Mismatch between number of types and parameters'];
    }

    if ($allParams) {
        $stmt->bind_param($allTypes, ...$allParams);
    }
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return ['status' => 'success', 'timestamp' => time(), 'data' => 'Season updated successfully'];
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No records updated'];
    }
}




public function insertShow($input) {
    $allowedFields = ['name', 'language', 'genres', 'status', 'runtime', 'premiered', 'officialSite', 'summary', 'rating', 'image'];
    $fields = [];
    $placeholders = [];
    $params = [];
    $types = '';

    // Extract fields to be inserted
    if (isset($input['insert']) && is_array($input['insert'])) {
        foreach ($input['insert'] as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $fields[] = $field;
                $placeholders[] = '?';
                $params[] = $value;
                $types .= $this->getType($value);
            } else {
                return ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid insert fields'];
            }
        }
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to insert'];
    }

    if (empty($fields)) {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to insert'];
    }

    $sql = "INSERT INTO shows (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    // Debugging: Ensure types and params match
    error_log("Types: $types");
    error_log("Params: " . implode(", ", $params));

    if (count($params) !== strlen($types)) {
        error_log("Mismatch between number of types and parameters");
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Mismatch between number of types and parameters'];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return ['status' => 'success', 'timestamp' => time(), 'data' => 'Show inserted successfully', 'inserted_id' => $stmt->insert_id];
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to insert show'];
    }
}

public function insertSeason($input) {
    $allowedFields = ['show_id', 'number', 'episode_count', 'premiere_date', 'end_date', 'summary'];
    $fields = [];
    $placeholders = [];
    $params = [];
    $types = '';

    // Extract fields to be inserted
    if (isset($input['insert']) && is_array($input['insert'])) {
        foreach ($input['insert'] as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $fields[] = $field;
                $placeholders[] = '?';
                $params[] = $value;
                $types .= $this->getType($value);
            } else {
                return ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid insert fields'];
            }
        }
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to insert'];
    }

    if (empty($fields)) {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to insert'];
    }

    $sql = "INSERT INTO seasons (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    // Debugging: Ensure types and params match
    error_log("Types: $types");
    error_log("Params: " . implode(", ", $params));

    if (count($params) !== strlen($types)) {
        error_log("Mismatch between number of types and parameters");
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Mismatch between number of types and parameters'];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return ['status' => 'success', 'timestamp' => time(), 'data' => 'Season inserted successfully', 'inserted_id' => $stmt->insert_id];
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to insert season'];
    }
}

public function insertEpisode($input) {
    $allowedFields = ['show_id', 'season', 'number', 'name', 'airdate', 'runtime', 'summary', 'rating', 'image'];
    $fields = [];
    $placeholders = [];
    $params = [];
    $types = '';

    // Extract fields to be inserted
    if (isset($input['insert']) && is_array($input['insert'])) {
        foreach ($input['insert'] as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $fields[] = $field;
                $placeholders[] = '?';
                $params[] = $value;
                $types .= $this->getType($value);
            } else {
                return ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid insert fields'];
            }
        }
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to insert'];
    }

    if (empty($fields)) {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No fields to insert'];
    }

    $sql = "INSERT INTO episodes (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    // Debugging: Ensure types and params match
    error_log("Types: $types");
    error_log("Params: " . implode(", ", $params));

    if (count($params) !== strlen($types)) {
        error_log("Mismatch between number of types and parameters");
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Mismatch between number of types and parameters'];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return ['status' => 'success', 'timestamp' => time(), 'data' => 'Episode inserted successfully', 'inserted_id' => $stmt->insert_id];
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to insert episode'];
    }
}

public function deleteEpisode($input) {
    $conditions = [];
    $params = [];
    $types = '';

    // Apply search filters to determine which record(s) to delete
    if (isset($input['search']) && is_array($input['search'])) {
        $search = $input['search'];
        if (isset($search['id'])) {
            $conditions[] = "id = ?";
            $params[] = $search['id'];
            $types .= 'i';
        }
        if (isset($search['show_id'])) {
            $conditions[] = "show_id = ?";
            $params[] = $search['show_id'];
            $types .= 'i';
        }
        if (isset($search['season'])) {
            $conditions[] = "season = ?";
            $params[] = $search['season'];
            $types .= 'i';
        }
        if (isset($search['number'])) {
            $conditions[] = "number = ?";
            $params[] = $search['number'];
            $types .= 'i';
        }
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No criteria provided for deletion'];
    }

    if (empty($conditions)) {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No criteria provided for deletion'];
    }

    $sql = "DELETE FROM episodes WHERE " . implode(' AND ', $conditions);

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return ['status' => 'success', 'timestamp' => time(), 'data' => 'Episode deleted successfully'];
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No records deleted'];
    }
}

public function deleteSeason($input) {
    $conditions = [];
    $params = [];
    $types = '';

    // Apply search filters to determine which record(s) to delete
    if (isset($input['search']) && is_array($input['search'])) {
        $search = $input['search'];
        if (isset($search['id'])) {
            $conditions[] = "id = ?";
            $params[] = $search['id'];
            $types .= 'i';
        }
        if (isset($search['show_id'])) {
            $conditions[] = "show_id = ?";
            $params[] = $search['show_id'];
            $types .= 'i';
        }
        if (isset($search['number'])) {
            $conditions[] = "number = ?";
            $params[] = $search['number'];
            $types .= 'i';
        }
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No criteria provided for deletion'];
    }

    if (empty($conditions)) {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No criteria provided for deletion'];
    }

    $sql = "DELETE FROM seasons WHERE " . implode(' AND ', $conditions);

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return ['status' => 'success', 'timestamp' => time(), 'data' => 'Season deleted successfully'];
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No records deleted'];
    }
}

public function deleteShow($input) {
    $conditions = [];
    $params = [];
    $types = '';

    // Apply search filters to determine which record(s) to delete
    if (isset($input['search']) && is_array($input['search'])) {
        $search = $input['search'];
        if (isset($search['id'])) {
            $conditions[] = "id = ?";
            $params[] = $search['id'];
            $types .= 'i';
        }
        if (isset($search['name'])) {
            $conditions[] = "name LIKE ?";
            $params[] = "%" . $search['name'] . "%";
            $types .= 's';
        }
        if (isset($search['language'])) {
            $conditions[] = "language LIKE ?";
            $params[] = "%" . $search['language'] . "%";
            $types .= 's';
        }
        if (isset($search['genres'])) {
            $conditions[] = "genres LIKE ?";
            $params[] = "%" . $search['genres'] . "%";
            $types .= 's';
        }
        if (isset($search['status'])) {
            $conditions[] = "status LIKE ?";
            $params[] = "%" . $search['status'] . "%";
            $types .= 's';
        }
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No criteria provided for deletion'];
    }

    if (empty($conditions)) {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No criteria provided for deletion'];
    }

    $sql = "DELETE FROM shows WHERE " . implode(' AND ', $conditions);

    // Debugging: Output the constructed SQL query
    error_log("SQL Query: $sql");

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Debugging: Output the SQL error
        error_log("SQL Error: " . $this->db->error);
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'Failed to prepare SQL statement'];
    }

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return ['status' => 'success', 'timestamp' => time(), 'data' => 'Show deleted successfully'];
    } else {
        return ['status' => 'error', 'timestamp' => time(), 'data' => 'No records deleted'];
    }
}


    }


    



 








$database = Database::getInstance();
$db = $database->getConnection();


$database = Database::getInstance();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);

switch ($input['type']) {
    case 'getShows':
        $streams = new Streams($db);
        $response = $streams->getAllShows($input);
        break;
    case 'getSeasons':
        $streams = new Streams($db);
        $response = $streams->getAllSeasons($input);
        break;
        case 'getPeople':
            $streams = new Streams($db);
            $response = $streams->getPeople($input);
            break;   
        case 'getEpisodes':
            $streams = new Streams($db);
            $response = $streams->getEpisodes($input);
           break;
        case 'getCrewCast':
           $streams = new Streams($db);
           $response = $streams->getCrewCast($input);
           break;

           case 'updateShow':
            $streams = new Streams($db);
            $response = $streams->updateShow($input);
            break;
            case 'updateEpisodes':
                $streams = new Streams($db);
                $response = $streams->updateEpisodes($input);
                break;
            case 'updateSeasons':
                $streams = new Streams($db);
                $response = $streams->updateSeasons($input);
                break;
        case 'insertShow':
        $streams = new Streams($db);
        $response = $streams->insertShow($input);
        break;
        case 'insertSeason':
            $streams = new Streams($db);
            $response = $streams->insertSeason($input);
            break;
        case 'insertEpisode':
              $streams = new Streams($db);
              $response = $streams->insertEpisode($input);
                break;
                case 'deleteEpisode':
                    $streams = new Streams($db);
                    $response = $streams->deleteEpisode($input);
                    break;
                    case 'deleteSeason':
                        $streams = new Streams($db);
                        $response = $streams->deleteSeason($input);
                        break;

                        case 'deleteShow':
                            $streams = new Streams($db);
                            $response = $streams->deleteShow($input);
                            break;
    default:
        $response = ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid request type'];
        break;
}


echo json_encode($response);
?>
