<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

echo "Welcome, " . $_SESSION["user"];

if ($_SESSION["role"] == "doctor") {
    header("Location: doctor_dashboard.php");
} elseif ($_SESSION["role"] == "nurse") {
    echo "<p>Caregiver view: Movement and activity logs.</p>";
} elseif ($_SESSION["role"] == "admin") {
    echo "<p>Admin view: Manage users and system status.</p>";
}
?>
