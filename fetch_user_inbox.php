<?php
session_name('user_session'); 
session_start();

if (!isset($_SESSION['homeowner_id'])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "homeowner";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully<br>"; // For debugging
}

$user_id = $_SESSION['homeowner_id']; // Assuming homeowner_id is stored in session
echo "Homeowner ID: $user_id<br>"; // Debugging output

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$messagesPerPage = 10; // Number of messages per page
$offset = ($page - 1) * $messagesPerPage;

$stmt = $conn->prepare("SELECT id, message, date FROM inbox WHERE homeowner_id = ? ORDER BY date DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $user_id, $messagesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$totalMessagesResult = $conn->prepare("SELECT COUNT(*) AS totalMessages FROM inbox WHERE homeowner_id = ?");
$totalMessagesResult->bind_param("i", $user_id);
$totalMessagesResult->execute();
$totalMessagesRow = $totalMessagesResult->get_result()->fetch_assoc();
$totalPages = ceil($totalMessagesRow['totalMessages'] / $messagesPerPage);

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Return the messages, total pages, and current page
echo json_encode([
    'messages' => $messages, 
    'totalPages' => $totalPages, 
    'currentPage' => $page
]);

$conn->close();
?>
