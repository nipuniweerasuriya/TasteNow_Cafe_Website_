<?php
include '../Backend/db_connect.php';

$result = $conn->query("SELECT * FROM daily_summary_logs ORDER BY summary_date DESC");

// Totals
$totalOrders = 0;
$totalRevenue = 0;
$totalBookings = 0;

$summaries = [];
while ($row = $result->fetch_assoc()) {
    $summaries[] = $row;
    $totalOrders += (int)$row['total_orders'];
    $totalRevenue += (float)$row['total_revenue'];
    $totalBookings += (int)$row['total_bookings'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Daily Summary History</title>


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

    <!-- Custom CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">



    <style>
        body {
            font-family: Arial, sans-serif;
        }
        a.back-link {
            margin: 20px;
            display: inline-block;
            text-decoration: none;
            color: #333;
            font-size: 16px;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
        .filter-container {
            width: 90%;
            margin: 20px auto;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .filter-container select, .filter-container input {
            padding: 5px;
            font-size: 14px;
        }
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 0 auto 30px auto;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        tfoot td {
            font-weight: bold;
            background-color: #eaeaea;
        }
    </style>
</head>
<body>

<a href="admin_dashboard.php" class="back-link">🔙 Back to Dashboard</a>
<h2 style="text-align:center;">Past Daily Summaries</h2>

<!-- Filter Toggle Button -->
<div style="width: 90%; margin: 10px auto; text-align: right;">
    <button onclick="toggleFilters()" style="background: none; border: none; font-size: 20px; cursor: pointer;">
<span class="material-symbols-outlined">filter_list</span></button>
</div>

<!-- Hidden Filter Container -->
<div class="filter-container" id="filterContainer" style="display: none;">
    <select id="yearFilter">
        <option value="">Filter by Year</option>
        <?php
        $years = array_unique(array_map(fn($s) => date('Y', strtotime($s['summary_date'])), $summaries));
        rsort($years);
        foreach ($years as $year) echo "<option value=\"$year\">$year</option>";
        ?>
    </select>

    <select id="monthFilter">
        <option value="">Filter by Month</option>
        <?php
        for ($m = 1; $m <= 12; $m++) {
            $monthName = date('F', mktime(0, 0, 0, $m, 10));
            echo "<option value=\"$m\">$monthName</option>";
        }
        ?>
    </select>

    <input type="date" id="dateFilter">
    <button onclick="clearFilters()">Clear Filters</button>
</div>


<table id="summaryTable">
    <thead>
    <tr>
        <th>Date</th>
        <th>Total Orders</th>
        <th>Total Revenue</th>
        <th>Total Bookings</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($summaries as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['summary_date']) ?></td>
            <td><?= (int)$row['total_orders'] ?></td>
            <td>Rs. <?= number_format($row['total_revenue'], 2) ?></td>
            <td><?= (int)$row['total_bookings'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td>Total</td>
        <td><?= $totalOrders ?></td>
        <td>Rs. <?= number_format($totalRevenue, 2) ?></td>
        <td><?= $totalBookings ?></td>
    </tr>
    </tfoot>
</table>

<script>
    const yearFilter = document.getElementById('yearFilter');
    const monthFilter = document.getElementById('monthFilter');
    const dateFilter = document.getElementById('dateFilter');
    const rows = document.querySelectorAll('#summaryTable tbody tr');

    function filterTable() {
        const year = yearFilter.value;
        const month = monthFilter.value;
        const date = dateFilter.value;

        rows.forEach(row => {
            const rowDate = row.children[0].textContent.trim(); // YYYY-MM-DD
            const [rYear, rMonth, rDay] = rowDate.split('-');

            const matchYear = !year || rYear === year;
            const matchMonth = !month || parseInt(rMonth) === parseInt(month);
            const matchDate = !date || rowDate === date;

            if (matchYear && matchMonth && matchDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function clearFilters() {
        yearFilter.value = '';
        monthFilter.value = '';
        dateFilter.value = '';
        filterTable();
    }

    yearFilter.addEventListener('change', filterTable);
    monthFilter.addEventListener('change', filterTable);
    dateFilter.addEventListener('change', filterTable);



    function toggleFilters() {
        const filterBox = document.getElementById('filterContainer');
        filterBox.style.display = filterBox.style.display === 'none' ? 'flex' : 'none';
    }

</script>

</body>
</html>
