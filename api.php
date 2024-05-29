<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Database
{
    private static $instance = null;

    private $connection;

    private function __construct()
    {
        $this->connection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    //We do need to create a destructor to disconnect from the database


    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}

class API
{

    private $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance();
    }

    public function register($name, $surname, $email, $password)
    {
        header('Content-Type: application/json'); // Set correct content type for JSON response

        if (!$this->validateEmail($email) || !$this->validatePassword($password)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid email or password format"]);
            exit();
        }

        if ($this->userExists($email)) {
            http_response_code(409);
            echo json_encode(["status" => "error", "message" => "User already exists"]);
            exit();
        }

        // Attempt to generate a unique API key
        $apiKey = $this->generateApiKey();
        $hashedPassword = $this->hashPassword($password);

        $this->connection->begin_transaction(); // Start transaction

        try {
            $sql = "INSERT INTO users (name, surname, email, password, API_Key) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            }
            $stmt->bind_param("sssss", $name, $surname, $email, $hashedPassword, $apiKey);
            $stmt->execute();

            if ($this->connection->affected_rows === 0) {
                throw new Exception("No rows affected");
            }

            $this->connection->commit(); // Commit the transaction

            http_response_code(200);
            echo json_encode(["status" => "success", "timestamp" => time(), "data" => ["apikey" => $apiKey]]);
            exit();
        } catch (Exception $e) {
            $this->connection->rollback(); // Rollback on error
            error_log("Registration failed: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Registration failed: " . $e->getMessage()]);
            exit();
        }
    }

    private function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Verifying password
    private function verifyPassword($inputPassword, $storedPassword)
    {
        return password_verify($inputPassword, $storedPassword);
    }
    private function generateSalt()
    {
        return bin2hex(random_bytes(16));
    }

    private function generateApiKey()
    {
        return bin2hex(random_bytes(10));
    }

    public function login($name, $password)
    {
        /* if (!$this->validateEmail($email)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit();
    } */

        // Corrected SQL query without the extra comma
        $stmt = $this->connection->prepare("SELECT id, password, API_Key, email FROM users WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true); // Regenerate session ID
                $_SESSION['id'] = $user['id'];
                $_SESSION['apikey'] = $user['API_Key'];
                $_SESSION['email'] = $user['email'];

                echo json_encode([
                    "status" => "success",
                    "timestamp" => round(microtime(true) * 1000),
                    "data" => [
                        "apikey" => $user['API_Key'],
                        "id" => $user['id'],
                    ]
                ]);
                exit();
            } else {
                http_response_code(401);
                echo json_encode(["status" => "error", "message" => "Invalid password"]);
                exit();
            }
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Invalid email or password"]);
            exit();
        }
    }

    public function logout($apiKey)
    {
        // Verify that the API key belongs to the currently logged-in user
        if ($this->verifyApiKey($apiKey)) {
            $sql = "UPDATE users SET API_Key = NULL WHERE API_Key = ? AND id = ?";
            $stmt = $this->connection->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("si", $apiKey, $_SESSION['id']); // Assuming user_id is stored in the session
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "message" => "Logged out successfully"]);
                    exit();
                } else {
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "Failed to logout or API key not found."]);
                    exit();
                }
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Database error: " . $this->connection->error]);
                exit();
            }
        } else {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Unauthorized logout attempt."]);
            exit();
        }
    }

    private function verifyApiKey($apiKey)
    {
        // Logic to verify that the API key belongs to the logged-in user
        // This function should confirm the API key matches the user's key in the database
        return true; // Placeholder for actual implementation
    }

    private function validateEmail($email)
    {
        return preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/", $email);
    }

    private function validatePassword($password)
    {
        return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&^+-_#\/])[A-Za-z\d@$!%*?&^+-_#\/]{10,}$/", $password);
    }

    private function userExists($email)
    {
        $sql = "SELECT id FROM users WHERE email = ?";

        error_log("Query being run: " . $sql);
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    private function userApi($apiKey)
    {
        $sql = "SELECT id FROM users WHERE API_Key = ?";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            error_log('Prepare error: ' . $this->connection->error);
            return false;
        }
        $stmt->bind_param("s", $apiKey);

        if (!$stmt->execute()) {
            error_log('Execute error: ' . $stmt->error);
            return false;
        }

        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
}

$api = new API();
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($data['type'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing type in request"]);
        exit;
    }

    switch ($data['type']) {
        case 'Register':
            echo $api->register($data['name'], $data['surname'], $data['email'], $data['password']);
            break;
        case 'Login':
            echo $api->login($data['name'], $data['password']);
            break;
        case 'Logout':
            echo $api->logout($data['apikey']);
            break;
        default:
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Invalid request type"]);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "HTTP Method not allowed, use POST"]);
}
