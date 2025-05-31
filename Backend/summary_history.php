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
    <title>Daily Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #aaa; padding: 10px; text-align: center; }
        th { background-color: #007bff; color: white; }
        .filter-form { margin-bottom: 20px; }
        .totals { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>

<h2>📊 Café Daily Summary</h2>

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
<table>
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

<!-- Totals Display -->
<div class="totals">
    Total Orders: <?= $totalOrders ?> |
    Total Revenue: Rs. <?= number_format($totalRevenue, 2) ?> |
    Total Bookings: <?= $totalBookings ?>
</div>

</body>
</html>
