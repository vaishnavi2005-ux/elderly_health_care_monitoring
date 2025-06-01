<?php
session_start();

// Simple role check (assuming you have user session management)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Connect to SQLite
try {
    $db = new PDO('sqlite:elderly_care.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Fetch all unique patient names for dropdown
$patientsQuery = $db->query("SELECT DISTINCT name FROM sensor_data ORDER BY name ASC");
$patients = $patientsQuery->fetchAll(PDO::FETCH_ASSOC);

$selected_patient = $_GET['patient'] ?? null;
$patient_stats = [];

if ($selected_patient) {
    // Prepare statement to avoid SQL injection
    $stmt = $db->prepare("SELECT * FROM sensor_data WHERE name = :name ORDER BY timestamp DESC LIMIT 10");
    $stmt->execute([':name' => $selected_patient]);
    $patient_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard - Patient Stats</title>
</head>
<body>
    <h1>Doctor Dashboard</h1>
    <form method="GET" action="">
        <label for="patient">Select Patient:</label>
        <select name="patient" id="patient" required>
            <option value="">Select Patient</option>
            <?php foreach ($patients as $patient): ?>
                <option value="<?= htmlspecialchars($patient['name']) ?>" <?= ($selected_patient === $patient['name']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($patient['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">View Stats</button>
    </form>

    <?php if ($selected_patient): ?>
        <h2>Last 10 Records for <?= htmlspecialchars($selected_patient) ?></h2>
        <?php if (count($patient_stats) > 0): ?>
            <table border="1" cellpadding="8" cellspacing="0">
                <tr>
                    <th>Timestamp</th>
                    <th>Age</th>
                    <th>Heart Rate</th>
                    <th>Steps</th>
                    <th>Body Temperature</th>
                    <th>Blood Pressure</th>
                    <th>Medication Taken</th>
                </tr>
                <?php foreach ($patient_stats as $stat): ?>
                    <tr>
                        <td><?= htmlspecialchars($stat['timestamp']) ?></td>
                        <td><?= htmlspecialchars($stat['age']) ?></td>
                        <td><?= htmlspecialchars($stat['heart_rate']) ?></td>
                        <td><?= htmlspecialchars($stat['steps']) ?></td>
                        <td><?= htmlspecialchars($stat['body_temperature']) ?></td>
                        <td><?= htmlspecialchars($stat['blood_pressure']) ?></td>
                        <td><?= $stat['medication_taken'] ? 'Yes' : 'No' ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No records found for this patient.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
