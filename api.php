<?php
require_once 'Dummy.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    $db = new Database();

    if ($action == 'login') {
        // Handle login
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        $stmt = $db->prepare("SELECT password FROM Users WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->getConnection()->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();
            if (password_verify($password, $hashedPassword)) {
                echo json_encode(['message' => 'Login successful']);
            } else {
                echo json_encode(['error' => 'Invalid password']);
            }
        } else {
            // Return an error image or message if user is not found
            echo json_encode(['error' => 'User not registered', 'img' => 'path/to/error_image.jpg']);
        }
        $stmt->close();
    } else {
        // Handle registration (existing code with modifications for clarity)
        // Ensure all fields are provided
        if (!isset($input['username'], $input['password'], $input['email'], $input['location'], $input['age'])) {
            throw new Exception("Missing required fields");
        }

        // Continue with registration logic...
        // Sanitization, database insertion, etc.
    }

    $db->close();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
