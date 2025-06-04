<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


$conn = new mysqli("localhost", "root", "", "health_care_project");


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

  
    $stmt = $conn->prepare("SELECT id, password, role, assigned_patient_ids FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();


            if (password_verify($pass, $row['password'])) {
                $_SESSION['id'] = $row['id'];
                $_SESSION['email'] = $user;
                $_SESSION['role'] = $row['role'];
                $_SESSION['assigned_patients'] = $row['assigned_patient_ids'];
                print("logged in");
                // Redirect by role
                switch ($row['role']) {
                    case 'doctor':
                        header("Location: doctor_dashboard.php");
                        break;
                    case 'nurse':
                        header("Location: nurse_dashboard.php");
                        break;
                    case 'admin':
                        header("Location: admin_dashboard.php");
                        break;
                    default:
                        header("Location: dashboard.php");
                }
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "User not found.";
        }
        $stmt->close();
    } else {
        $error = "Database error: Failed to prepare statement.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post" action="">
        Username: <input type="text" name="username" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>

