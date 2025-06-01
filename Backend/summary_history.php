<?php
include 'db_connect.php';

$filterType = $_GET['filter_type'] ?? 'all';
$filterValue = $_GET['filter_value'] ?? '';

$whereClause = "";
$params = [];
$paramTypes = "";

if ($filterType === 'date' && $filterValue) {
    $whereClause = "WHERE summary_date = ?";
    $params[] = $filterValue;
    $paramTypes .= "s";
} elseif ($filterType === 'month' && $filterValue) {
    $whereClause = "WHERE MONTH(summary_date) = ?";
    $params[] = (int)$filterValue;
    $paramTypes .= "i";
} elseif ($filterType === 'year' && $filterValue) {
    $whereClause = "WHERE YEAR(summary_date) = ?";
    $params[] = (int)$filterValue;
    $paramTypes .= "i";
}

$sql = "SELECT * FROM daily_summary $whereClause ORDER BY summary_date DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Totals
$totalOrders = 0;
$totalRevenue = 0;
$totalBookings = 0;

$data = [];
while ($row = $result->fetch_assoc()) {
    $totalOrders += $row['total_orders'];
    $totalRevenue += $row['total_revenue'];
    $totalBookings += $row['total_bookings'];
    $data[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary</title>

    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@300;400;500&display=swap"
          rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
          rel="stylesheet">

    <!-- Bootstrap and Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <!-- Custom CSS -->
    <link rel="stylesheet" href="../Frontend/css/styles.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body class="common-page" id="summary-page">

<div class="summary-history-container">
    <h3 class="summary-heading">Café Daily Summary</h3>
    <!-- Filter Form -->
    <form method="GET" class="filter-form">
        <label>Filter Type:
            <select name="filter_type" onchange="this.form.submit()">
                <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>All</option>
                <option value="date" <?= $filterType === 'date' ? 'selected' : '' ?>>Date</option>
                <option value="month" <?= $filterType === 'month' ? 'selected' : '' ?>>Month</option>
                <option value="year" <?= $filterType === 'year' ? 'selected' : '' ?>>Year</option>
            </select>
        </label>

        <?php if ($filterType === 'date'): ?>
            <input type="date" name="filter_value" value="<?= $filterValue ?>" onchange="this.form.submit()">
        <?php elseif ($filterType === 'month'): ?>
            <select name="filter_value" onchange="this.form.submit()">
                <option value="">-- Select Month --</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $filterValue == $m ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        <?php elseif ($filterType === 'year'): ?>
            <select name="filter_value" onchange="this.form.submit()">
                <option value="">-- Select Year --</option>
                <?php
                $yearStart = 2022;
                $yearNow = date('Y');
                for ($y = $yearNow; $y >= $yearStart; $y--): ?>
                    <option value="<?= $y ?>" <?= $filterValue == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        <?php endif; ?>
    </form>

    <!-- Summary Table -->
    <table id="summary-table">
        <thead>
        <tr>
            <th>Date</th>
            <th>Total Orders</th>
            <th>Total Revenue</th>
            <th>Total Bookings</th>
            <th>Recorded At</th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($data) > 0): ?>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['summary_date']) ?></td>
                    <td><?= htmlspecialchars($row['total_orders']) ?></td>
                    <td>Rs. <?= number_format($row['total_revenue'], 2) ?></td>
                    <td><?= htmlspecialchars($row['total_bookings']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">No summary data found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
<hr class="divider">
    <!-- Totals Display -->
    <div class="totals">
        Total Orders: <?= $totalOrders ?> |
        Total Revenue: Rs. <?= number_format($totalRevenue, 2) ?> |
        Total Bookings: <?= $totalBookings ?>
    </div>
</div>

</body>
</html>
