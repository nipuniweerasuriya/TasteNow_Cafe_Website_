<?php
include 'db_connect.php';

$sql = "
    SELECT 
        uf.message, 
        uf.submitted_at, 
        u.name AS user_name, 
        u.role 
    FROM user_feedback uf
    LEFT JOIN users u ON uf.user_id = u.id
    ORDER BY uf.submitted_at DESC
    LIMIT 10
";

$result = $conn->query($sql);
?>
    <!-- Feedback Section -->
    <div class="feedback-scroll-container" aria-label="User feedback scroll" id="feedback-section">
        <h2 class="feedback-heading">Customers Says</h2>
        <div class="feedback-scroll-track">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $userName = htmlspecialchars($row['user_name'] ?? 'Anonymous');
                    $role = htmlspecialchars($row['role'] ?? 'user');
                    $message = htmlspecialchars($row['message']);
                    $submittedAt = date('F j, Y, g:i a', strtotime($row['submitted_at']));

                    echo '<div class="feedback-card">';
                    echo "<strong>$userName</strong><br>";
                    echo "<p>\"$message\"</p>";
                    echo "<small>Submitted on: $submittedAt</small>";
                    echo '</div>';
                }
            } else {
                echo '<div class="feedback-card">No feedback submitted yet.</div>';
            }
            $conn->close();
            ?>
        </div>
    </div>
