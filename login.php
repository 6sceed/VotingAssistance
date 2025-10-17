<?php
// Start the session (essential for tracking logged-in users)
session_start();

// Set content type to JSON for API response
header('Content-Type: application/json');

// --- ⚠️ DATABASE CONFIGURATION: UPDATE THESE VALUES ⚠️ ---
$db_host = 'localhost';
$db_name = 'voter_assist_db';
$db_user = 'root';
$db_pass = '';
// ----------------------------------------------------

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// Get JSON input from the frontend
$data = json_decode(file_get_contents('php://input'), true);

$email = $data['voterId'] ?? ''; // Renamed from voterId to better reflect email usage
$password = $data['password'] ?? '';

// Input validation
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter both email and password.']);
    exit();
}

// 1. Prepare and execute statement to retrieve user by email
$stmt = $mysqli->prepare("SELECT id, name, email, password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // 2. Verify password against the hash
    if (password_verify($password, $user['password_hash'])) {

        // 3. Login success: Store user data in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        echo json_encode([
            'success' => true,
            'message' => 'Login successful! Redirecting to dashboard...',
            'user' => ['name' => $user['name'], 'email' => $user['email']]
        ]);

    } else {
        // Password mismatch
        echo json_encode(['success' => false, 'message' => 'Invalid credentials. Please try again.']);
    }
} else {
    // User not found
    echo json_encode(['success' => false, 'message' => 'Invalid credentials. Please try again.']);
}

$stmt->close();
$mysqli->close();
?>