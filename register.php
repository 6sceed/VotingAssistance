<?php
// Set content type to JSON for API response
header('Content-Type: application/json');

// --- ⚠️ DATABASE CONFIGURATION: UPDATE THESE VALUES ⚠️ ---
$db_host = 'localhost';
$db_name = 'voter_assist_db';
$db_user = 'root';
$db_pass = '';
// ----------------------------------------------------

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// Get JSON input from the frontend
$data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';

// Basic input validation
if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}
if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit();
}
if (strlen($password) < 5) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 5 characters long.']);
    exit();
}

// Hash the password securely using the recommended PHP function
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Use prepared statement to prevent SQL injection
$stmt = $mysqli->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $password_hash);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Registration successful! Please log in.']);
} else {
    // 1062 is the error code for duplicate entry (unique constraint violation on email)
    if ($mysqli->errno === 1062) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
    } else {
        error_log("Registration DB Error: " . $mysqli->error);
        echo json_encode(['success' => false, 'message' => 'Registration failed due to a database error.']);
    }
}

$stmt->close();
$mysqli->close();
?>