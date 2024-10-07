<?php
session_name('admin_session'); 
session_start();
$servername = "localhost";
$username = "root";
$dbpassword = "";
$database = "homeowner";

// Create connection
$conn = new mysqli($servername, $username, $dbpassword, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination settings
$limit = 10; // Number of requests per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $limit;

// Search functionality
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if ($search_query) {
    $search_condition = "WHERE h.email LIKE '%" . $conn->real_escape_string($search_query) . "%'";
}

// Count total requests
$count_sql = "SELECT COUNT(*) as total FROM password_reset_requests p JOIN homeowners h ON p.homeowner_id = h.id $search_condition";
$count_result = $conn->query($count_sql);
$total_requests = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_requests / $limit);

// Fetch requests with pagination and search
$sql = "SELECT p.id, h.email, p.reset_token, p.created_at 
        FROM password_reset_requests p 
        JOIN homeowners h ON p.homeowner_id = h.id 
        $search_condition
        ORDER BY p.created_at DESC
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Requests</title>
    <link rel="stylesheet" href="homeownercss.css">
</head>
<body>

    
    <div class="main-content">
        <h2>Password Reset Requests</h2>
        <br>
        
        <a class="btn btn-primary" href="homeowneradmin.php" role="button">Homeowners</a>

        <div class="container">
            <!-- Search Form -->
            <form method="GET" action="view_requests.php" class="search-form">
                <input type="text" name="search" placeholder="Search by email" value="<?= htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </form>

            <!-- Display message if no requests are found -->
            <?php if (!empty($_SESSION['message'])): ?>
                <p class="<?= $_SESSION['message']['status'] ?>">
                    <?= htmlspecialchars($_SESSION['message']['message']) ?>
                </p>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if ($result->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Reset Token</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop through password reset requests and display their info -->
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['reset_token']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Pagination controls -->
                <div id="pagination">
                    <?php
                    $total_pages = max($total_pages, 1); // Ensure there's at least 1 page
                    $input_page = $current_page; // Default to the current page for the input

                    // Previous button
                    if ($current_page > 1): ?>
                        <form method="GET" action="pass_reqs.php" style="display: inline;">
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search_query); ?>">
                            <input type="hidden" name="page" value="<?= $current_page - 1 ?>">
                            <button type="submit">&lt;</button>
                        </form>
                    <?php endif; ?>

                    <!-- Page input for user to change the page -->
                    <form method="GET" action="pass_reqs.php" style="display: inline;">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search_query); ?>">
                        <input type="number" name="page" value="<?= $input_page ?>" min="1" max="<?= $total_pages ?>" style="width: 50px;">
                    </form>

                    <!-- "of" text and last page link -->
                    <?php if ($total_pages > 1): ?>
                        <span>of</span>
                        <a href="?search=<?= urlencode($search_query); ?>&page=<?= $total_pages ?>" class="<?= ($current_page == $total_pages) ? 'active' : '' ?>"><?= $total_pages ?></a>
                    <?php endif; ?>

                    <!-- Next button -->
                    <?php if ($current_page < $total_pages): ?>
                        <form method="GET" action="pass_reqs.php" style="display: inline;">
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search_query); ?>">
                            <input type="hidden" name="page" value="<?= $current_page + 1 ?>">
                            <button type="submit">></button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No password reset requests found.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'sidebar.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
