<?php
    include '../../include/conn/conn.php';
    include '../../include/conn/session.php';
    
    $provider_id = $_SESSION['user']['id'];
    
    $provider_query = "SELECT * FROM service_provider WHERE id = '$provider_id'";
    $provider_result = mysqli_query($conn, $provider_query);
    $provider_data = mysqli_fetch_assoc($provider_result);
    
    
    if(isset($_POST['change_password'])) 
    {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if($new_password !== $confirm_password) {
            echo "<script>alert('New passwords do not match.');
            window.location.href= 'index.php';
            </script>";
            exit();
        } else {
            if($current_password !== $provider_data['password']) {
                echo "<script>alert('Current password is incorrect.');
                window.location.href='index.php';
                </script>";
                exit();
            } else {
                $update_query = "UPDATE service_provider SET password = '$new_password' WHERE id = '$provider_id'";
                if(mysqli_query($conn, $update_query)) {
                    echo "<script>alert('Password changed successfully.');
                    window.location.href= 'index.php';</script>
                    ";
                    exit();
                } else {
                    echo "<script>alert('Error updating password. Please try again.');
                    window.location.href= 'index.php';
                    </script>";
                    exit();
                }
            }
        }
    }
    

?>
<!DOCTYPE html>
<html lang="en">
<head>
                }
            }
        }
    }
    

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - HomeCare Connect</title>
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
            margin: 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);
        }
        
        .logout-btn:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
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
        
        .content-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.1);
            margin-bottom: 30px;
            border-left: 4px solid #3b82f6;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .section-header h2 {
            color: #1e3a8a;
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .profile-info h3 {
            color: #1e3a8a;
            font-size: 1.4rem;
            margin-bottom: 5px;
            font-weight: 700;
        }
        
        .profile-info p {
            color: #64748b;
            font-weight: 500;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .detail-label {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 600;
        }
        
        .detail-value {
            font-size: 1rem;
            color: #1e3a8a;
            font-weight: 500;
            padding: 12px 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .description-box {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            color: #1e3a8a;
            line-height: 1.6;
            min-height: 100px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .success-message {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        
        .error-message {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
            
            .profile-container {
                grid-template-columns: 1fr;
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
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Service Provider</h3>
        <div class="sidebar-nav">
            <a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="bookings.php"><i class="fas fa-calendar-alt"></i> <span>Bookings</span></a>
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
                <p>View your account information and service details</p>
            </div>
            <div class="date-display">
                <i class="far fa-calendar-alt"></i> <?php echo date("l, F j, Y"); ?>
            </div>
        </div>
        
        
        <div class="profile-container">
            <!-- Personal Information Section -->
            <div class="content-section">
                <div class="section-header">
                    <h2>Personal Information</h2>
                </div>
                
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo isset($provider_data['user_name']) ? strtoupper(substr($provider_data['user_name'], 0, 1)) : 'U'; ?>
                    </div>
                    <div class="profile-info">
                        <h3><?php echo isset($provider_data['user_name']) ? htmlspecialchars($provider_data['user_name']) : 'User'; ?></h3>
                        <p>Service Provider</p>
                    </div>
                </div>
                
                <div class="details-grid">
                    <div class="detail-group">
                        <span class="detail-label">Full Name</span>
                        <div class="detail-value">
                            <?php echo isset($provider_data['user_name']) ? htmlspecialchars($provider_data['user_name']) : 'Not set'; ?>
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Email Address</span>
                        <div class="detail-value">
                            <?php echo isset($provider_data['email']) ? htmlspecialchars($provider_data['email']) : 'Not set'; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content-section">
                <div class="section-header">
                    <h2>Service Information</h2>
                </div>
                
                <div class="details-grid">
                    <div class="detail-group">
                        <span class="detail-label">Service Title</span>
                        <div class="detail-value">
                            <?php echo isset($provider_data['title']) ? htmlspecialchars($provider_data['title']) : 'Not set'; ?>
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Category</span>
                        <div class="detail-value">
                            <?php 
                                if(isset($provider_data['category']) && !empty($provider_data['category'])) {
                                    echo ucfirst(htmlspecialchars($provider_data['category']));
                                } else {
                                    echo 'Not set';
                                }
                            ?>
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Service Rate</span>
                        <div class="detail-value">
                            <?php 
                                if(isset($provider_data['rate']) && !empty($provider_data['rate'])) {
                                    echo '₹' . number_format($provider_data['rate'], 2);
                                } else {
                                    echo 'Not set';
                                }
                            ?>
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Rate Per</span>
                        <div class="detail-value">
                            <?php 
                                if(isset($provider_data['per']) && !empty($provider_data['per'])) {
                                    echo ucfirst(htmlspecialchars($provider_data['per']));
                                } else {
                                    echo 'Not set';
                                }
                            ?>
                        </div>
                    </div>
                    
                    <div class="detail-group full-width">
                        <span class="detail-label">Service Areas</span>
                        <div class="detail-value">
                            <?php echo isset($provider_data['area']) ? htmlspecialchars($provider_data['area']) : 'Not set'; ?>
                        </div>
                    </div>
                    
                    <div class="detail-group full-width">
                        <span class="detail-label">Service Description</span>
                        <div class="description-box">
                            <?php 
                                if(isset($provider_data['service_description']) && !empty($provider_data['service_description'])) {
                                    echo nl2br(htmlspecialchars($provider_data['service_description']));
                                } else {
                                    echo 'No service description provided.';
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Password Change Section -->
            <div class="content-section">
                <div class="section-header">
                    <h2>Change Password</h2>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="password-toggle">
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                            <i class="fas fa-eye" onclick="togglePassword('current_password')"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="password-toggle">
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                            <i class="fas fa-eye" onclick="togglePassword('new_password')"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="password-toggle">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            <i class="fas fa-eye" onclick="togglePassword('confirm_password')"></i>
                        </div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-success">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentNode.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>