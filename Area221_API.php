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

    public function getAllShows($input) {
        $allowedFields = ['id', 'name', 'language', 'genres', 'status', 'runtime', 'premiered', 'officialSite', 'summary', 'rating', 'image'];
        $sql = "SELECT " . implode(", ", $allowedFields) . " FROM shows";
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
        $sql = "SELECT " . implode(", ", $allowedFields) . " FROM seasons";
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
    default:
        $response = ['status' => 'error', 'timestamp' => time(), 'data' => 'Invalid request type'];
        break;
}


echo json_encode($response);
?>
