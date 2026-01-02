<?php
    include '../../include/conn/conn.php';
    include '../../include/conn/session.php';
    
    $user_id = $_SESSION['user']['id'];
    
    $user_query = "SELECT * FROM user WHERE id = $user_id";
    $user_result = mysqli_query($conn, $user_query);
    $user_data = mysqli_fetch_assoc($user_result);
    
    if(isset($_POST['update_profile'])) {
        $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $city = mysqli_real_escape_string($conn, $_POST['city']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        
        $update_query = "UPDATE user SET 
                        user_name = '$user_name',
                        email = '$email',
                        phone = '$phone',
                        city = '$city',
                        address = '$address'
                        WHERE id = $user_id";
        
        if(mysqli_query($conn, $update_query)) {
            echo "<script>alert('Profile updated successfully!');
            window.location.href = 'profile.php';</script>
            ";            
            $user_result = mysqli_query($conn, $user_query);
            $user_data = mysqli_fetch_assoc($user_result);
        } else {
            echo "<script>alert('Error updating profile: " . mysqli_error($conn) . "');
            window.location.href = 'profile.php';</script>";
        }
    }
    
    if(isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if($current_password !== $user_data['password']) {
            $password_error = "Current password is incorrect.";
        } elseif($new_password !== $confirm_password) {
            $password_error = "New passwords do not match.";
        } else {
            $update_password_query = "UPDATE user SET password = '$new_password' WHERE id = $user_id";
            if(mysqli_query($conn, $update_password_query)) {
                echo "<script>alert('Password changed successfully!');
                window.location.href = 'profile.php';</script>";
            } else {
                echo "<script>alert('Error changing password: " . mysqli_error($conn) . "');
                window.location.href = 'profile.php';</script>";
            }
        }
    }
    
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HomeCare Connect</title>
    <link rel="stylesheet" href="../../include/css/user_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            display: flex;
            background-color: #f8fafc;
            color: #2d3748;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #1e3a8a, #1e40af);
            color: white;
            height: 100vh;
            position: fixed;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            box-shadow: 3px 0 15px rgba(30, 58, 138, 0.2);
        }
        
        .sidebar h3 {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 1.5rem;
            color: white;
            font-weight: 600;
        }
        
        .sidebar-nav {
            flex: 1;
        }
        
        .sidebar-nav a {
            display: block;
            padding: 15px 25px;
            color: #e2e8f0;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            font-weight: 500;
        }
        
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid #60a5fa;
        }
        
        .sidebar-nav a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            color: #93c5fd;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 12px 20px;
            width: calc(100% - 40px);
            /* margin: 20px; */
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);
        }
        
        .logout-btn:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            /* transform: translateY(-2px); */
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
            background-color: #f8fafc;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.1);
            border-left: 4px solid #3b82f6;
        }
        
        .welcome h1 {
            color: #1e3a8a;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .welcome p {
            color: #64748b;
            margin-top: 5px;
            font-weight: 500;
        }
        
        .date-display {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            color: white;
            font-weight: 600;
        }
        
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .profile-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.1);
            border-left: 4px solid #3b82f6;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .section-header i {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 12px;
            border-radius: 10px;
            margin-right: 15px;
            font-size: 1.2rem;
        }
        
        .section-header h2 {
            color: #1e3a8a;
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        .profile-info {
            display: grid;
            gap: 20px;
        }
        
        .info-group {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        
        .info-group i {
            color: #3b82f6;
            margin-right: 15px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            color: #1e3a8a;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1e3a8a;
            font-weight: 600;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background: white;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(100, 116, 139, 0.3);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #475569, #374151);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(100, 116, 139, 0.4);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border: 2px solid #10b981;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border: 2px solid #ef4444;
        }
        
        .required {
            color: #ef4444;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            font-weight: 600;
            opacity: 0.9;
        }
        
        @media (max-width: 1200px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar h3, .sidebar-nav a span {
                display: none;
            }
            
            .sidebar-nav a {
                text-align: center;
                padding: 15px 0;
                border-left: none;
                border-bottom: 4px solid transparent;
            }
            
            .sidebar-nav a:hover, .sidebar-nav a.active {
                border-left: none;
                border-bottom: 4px solid #60a5fa;
            }
            
            .sidebar-nav a i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .logout-btn span {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .date-display {
                align-self: stretch;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .info-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .info-group i {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Home Owner</h3>
        <div class="sidebar-nav">
            <a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="services.php"><i class="fas fa-concierge-bell"></i> <span>Services</span></a>
            <a href="bookings.php"><i class="fas fa-calendar-alt"></i> <span>Bookings / History</span></a>
            <a href="#" class="active"><i class="fas fa-user"></i> <span>Profile</span></a>
        </div>
        <form action="" method="post">
            <button type="submit" class="logout-btn" name="logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></button>
        </form>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div class="welcome">
                <h1>My Profile</h1>
                <p>Manage your account information and preferences</p>
            </div>
            <div class="date-display">
                <i class="far fa-calendar-alt"></i> <?php echo date("l, F j, Y"); ?>
            </div>
        </div>
        
        <!-- <?php if(isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?> -->
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-container">
            <!-- Profile Information Section -->
            <div class="profile-section">
                <div class="section-header">
                    <i class="fas fa-user-circle"></i>
                    <h2>Profile Information</h2>
                </div>
                
                <div class="profile-info">
                    <div class="info-group">
                        <i class="fas fa-user"></i>
                        <div class="info-content">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($user_data['user_name']); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <i class="fas fa-envelope"></i>
                        <div class="info-content">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($user_data['email']); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <i class="fas fa-phone"></i>
                        <div class="info-content">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($user_data['phone']); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="info-content">
                            <div class="info-label">City</div>
                            <div class="info-value"><?php echo htmlspecialchars($user_data['city']); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <i class="fas fa-home"></i>
                        <div class="info-content">
                            <div class="info-label">Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($user_data['address']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="profile-section">
                <div class="section-header">
                    <i class="fas fa-edit"></i>
                    <h2>Edit Profile</h2>
                </div>
                
                <form method="post">
                    <div class="form-group">
                        <label for="user_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="user_name" name="user_name" value="<?php echo htmlspecialchars($user_data['user_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City <span class="required">*</span></label>
                        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user_data['city']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address <span class="required">*</span></label>
                        <textarea id="address" name="address" required><?php echo htmlspecialchars($user_data['address']); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="reset" class="btn-secondary">Cancel</button>
                        <button type="submit" name="update_profile" class="btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
            
            <!-- Change Password Section -->
            <div class="profile-section">
                <div class="section-header">
                    <i class="fas fa-lock"></i>
                    <h2>Change Password</h2>
                </div>
                
                <?php if(isset($password_success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $password_success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($password_error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $password_error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="form-group">
                        <label for="current_password">Current Password <span class="required">*</span></label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password <span class="required">*</span></label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="reset" class="btn-secondary">Clear</button>
                        <button type="submit" name="change_password" class="btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>