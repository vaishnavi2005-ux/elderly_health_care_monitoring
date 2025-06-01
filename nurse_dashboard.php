<?php
session_start();

// Allow only logged-in nurses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'nurse') {
    header("Location: login.php");
    exit();
}

// Convert CSV patient IDs to array and filter empty
$patient_ids = array_filter(array_map('trim', explode(",", $_SESSION['assigned_patients'] ?? "")));

if (empty($patient_ids)) {
    die("No patients assigned.");
}

try {
    $db = new PDO('sqlite:elderly_care.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Prepare placeholders
$placeholders = implode(",", array_fill(0, count($patient_ids), '?'));

// Assuming patient names are unique and stored in 'name' column in sensor_data table
$sql = "SELECT * FROM sensor_data WHERE name IN ($placeholders)";
$stmt = $db->prepare($sql);

// Bind patient IDs (names) as strings
foreach ($patient_ids as $index => $patient_name) {
    $stmt->bindValue($index + 1, $patient_name, PDO::PARAM_STR);
}

$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Nurse Dashboard</title>
</head>
<body>
    <h1>Welcome, Nurse</h1>
    <p>Assigned Patient Records:</p>

    <?php if (count($results) === 0): ?>
        <p>No data available for your assigned patients.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Timestamp</th>
                <th>Name</th>
                <th>Age</th>
                <th>Heart Rate</th>
                <th>Steps</th>
                <th>Temperature</th>
                <th>Blood Pressure</th>
                <th>Medication Taken</th>
            </tr>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['timestamp']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['age']) ?></td>
                <td><?= htmlspecialchars($row['heart_rate']) ?></td>
                <td><?= htmlspecialchars($row['steps']) ?></td>
                <td><?= htmlspecialchars($row['body_temperature']) ?></td>
                <td><?= htmlspecialchars($row['blood_pressure']) ?></td>
                <td><?= htmlspecialchars($row['medication_taken']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <p><a href="login.php">Logout</a></p>
</body>
</html>
