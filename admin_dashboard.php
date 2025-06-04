<?php
session_start();


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


$conn = new mysqli("localhost", "root", "", "health_care_project");
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}


try {
    $db = new PDO('sqlite:elderly_care.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("SQLite DB Connection failed: " . $e->getMessage());
}


$patientsQuery = $db->query("SELECT DISTINCT name FROM sensor_data ORDER BY name ASC");
$patients = $patientsQuery->fetchAll(PDO::FETCH_ASSOC);

$message = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $user_id = intval($_POST['user_id']);
    $patient_id = $_POST['patient_name'];
    
    
    $stmt = $conn->prepare("SELECT assigned_patient_ids FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_patients = $row['assigned_patient_ids'];

        
        $patients_array = $current_patients ? explode(",", $current_patients) : [];


        if (!in_array($patient_id, $patients_array)) {
            $patients_array[] = $patient_id;
            $new_patients = implode(",", $patients_array);

            $update_stmt = $conn->prepare("UPDATE users SET assigned_patient_ids = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_patients, $user_id);
            $update_result = $update_stmt->execute();

            $insert_stmt = $conn->prepare("INSERT INTO assignments(patient_name, user_id) VALUES (?, ?)");
            if ($insert_stmt === false) {
                die("Prepare failed: " . $conn->error);
                        }
            $insert_stmt->bind_param("si", $patient_id, $user_id);
            $insert_result = $insert_stmt->execute();

            if ($update_result && $insert_result) {
                $message = "Patient assigned successfully.";
            } else {
                $message = "Error assigning patient.";
            }
        } else {
            $message = "Patient already assigned to this user.";
        }
    } else {
        $message = "User not found.";
    }

    $stmt->close();
}

$users_result = $conn->query("SELECT id, email, role, assigned_patient_ids FROM users ORDER BY role, email");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
<h1>Admin Dashboard</h1>

<?php if ($message): ?>
    <p><b><?= htmlspecialchars($message) ?></b></p>
<?php endif; ?>

<h2>Users</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Email</th>
        <th>Role</th>
        <th>Assigned Patients (IDs)</th>
    </tr>
    <?php while ($user = $users_result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td><?= htmlspecialchars($user['assigned_patient_ids']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<h2>Assign Patient to User</h2>
<form method="POST" action="">
    <label for="user_name">Select User:</label>
    <select name="user_id" id="user_id" required>
        <option value="">Select User</option>
        <?php
        // Reset pointer to fetch users again for select box
        $users_result->data_seek(0);
        while ($user = $users_result->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($user['id']) . '">' . htmlspecialchars($user['email']) . " (" . htmlspecialchars($user['role']) . ")</option>";
        }
        ?>
    </select>

    <label for="patient_name">Select Patient:</label>
    <select name="patient_name" id="patient_name" required>
        <option value="">Select Patient</option>
        <?php foreach ($patients as $patient): ?>
            <option value="<?= htmlspecialchars($patient['name']) ?>">
                <?= htmlspecialchars($patient['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="assign">Assign Patient</button>
</form>

<p><a href="login.php">Logout</a></p>
</body>
</html>

<?php
$conn->close();
?>
