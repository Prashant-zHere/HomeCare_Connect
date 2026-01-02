<?php
// filepath: c:\xampp\htdocs\projects\projects\HomeCare_connect\user\service_provider\edit_profile.php

include '../../include/conn/conn.php';
include '../../include/conn/session.php';

$sp_id = $_SESSION['user']['id'];

// Fetch current provider data
$query = "SELECT * FROM service_provider WHERE id = '$sp_id'";
$result = mysqli_query($conn, $query);
$sp_profile = mysqli_fetch_assoc($result);

if(isset($_POST['update'])){
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $rate = mysqli_real_escape_string($conn, $_POST['rate']);
    $per = mysqli_real_escape_string($conn, $_POST['per']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $service_description = mysqli_real_escape_string($conn, $_POST['service_description']);
    
    // Update query (adjust field names as per your database)
    $update_query = "UPDATE service_provider 
                     SET user_name = '$user_name', email = '$email', title = '$title', category = '$category', rate = '$rate', per = '$per', description = '$description', service_description = '$service_description'
                     WHERE id = '$sp_id'";
    
    if(mysqli_query($conn, $update_query)){
        header("Location: profile.php");
        exit();
    } else {
        $error = "Error updating profile: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../include/css/sp_dashboard.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #1a237e;
            color: #fff;
            min-height: 100vh;
            padding: 40px 20px;
            position: fixed;
        }
        .sidebar h3 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 22px;
            font-weight: bold;
        }
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 40px;
        }
        .sidebar-nav a {
            color: #fff;
            text-decoration: none;
            font-size: 18px;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background-color: #283593;
        }
        .logout-btn {
            position: absolute;
            bottom: 200px;
            left: 20px;
            right: 20px;
            padding: 10px;
            background: #d32f2f;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background: #b71c1c;
        }
        .dashboard-container {
            margin-left: 270px;
            padding: 40px;
            width: calc(100% - 270px);
        }
        h2 {
            color: #1a237e;
            margin-bottom: 20px;
        }
        .edit-form {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-size: 16px;
        }
        .edit-form label {
            display: block;
            margin: 12px 0 4px;
        }
        .edit-form input[type="text"],
        .edit-form input[type="email"],
        .edit-form input[type="number"],
        .edit-form textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            font-size: 16px;
        }
        .edit-form textarea {
            height: 100px;
            resize: vertical;
        }
        .update-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            color: #fff;
            background: #4e5ad1;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .update-btn:hover {
            background: #283593;
        }
        .error {
            color: #d32f2f;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Service Provider</h3>
        <div class="sidebar-nav">
            <a href="index.php">Dashboard</a>
            <a href="bookings.php">Bookings</a>
            <a href="profile.php" class="active">Profile</a>
        </div>
        <form action="" method="post">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
    <div class="dashboard-container">
        <h2>Edit Your Profile</h2>
        <div class="edit-form">
            <form action="" method="POST">
                <label for="user_name">User Name:</label>
                <input type="text" id="user_name" name="user_name" value="<?php echo htmlspecialchars($sp_profile['user_name']); ?>" required>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($sp_profile['email']); ?>" required>
                
                <label for="title">Service Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($sp_profile['title']); ?>" required>
                
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($sp_profile['category']); ?>" required>
                
                <label for="rate">Rate:</label>
                <input type="number" id="rate" name="rate" step="0.01" value="<?php echo htmlspecialchars($sp_profile['rate']); ?>" required>
                
                <label for="per">Rate Basis:</label>
                <select id="per" name="per" required>
                    <option value="hour" <?php if($sp_profile['per'] == 'hour') echo 'selected'; ?>>Per Hour</option>
                    <option value="day" <?php if($sp_profile['per'] == 'day') echo 'selected'; ?>>Per Day</option>
                    <option value="monthly" <?php if($sp_profile['per'] == 'monthly') echo 'selected'; ?>>Per Month</option>
                    <option value="service" <?php if($sp_profile['per'] == 'service') echo 'selected'; ?>>Per Service</option>
                </select>

                <label for="description">Your Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($sp_profile['description']); ?></textarea>
                
                <label for="service_description">Service Description:</label>
                <textarea id="service_description" name="service_description" required><?php echo htmlspecialchars($sp_profile['service_description']); ?></textarea>
                
                <button type="submit" name="update" class="update-btn">Update Profile</button>
            </form>
            <?php if(isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>