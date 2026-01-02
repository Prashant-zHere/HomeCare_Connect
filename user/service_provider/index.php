<?php
    include '../../include/conn/conn.php';
    include '../../include/conn/session.php';
    
    // Check if service provider is logged in
    $provider_id = $_SESSION['user']['id'];
    
    // Fetch service provider data
    $provider_query = "SELECT * FROM service_provider WHERE id = '$provider_id'";
    $provider_result = mysqli_query($conn, $provider_query);
    $provider_data = mysqli_fetch_assoc($provider_result);
    
    // Fetch statistics
    $total_bookings_query = "SELECT COUNT(*) as total FROM bookings WHERE service_provider_id = '$provider_id'";
    $total_bookings_result = mysqli_query($conn, $total_bookings_query);
    $total_bookings = mysqli_fetch_assoc($total_bookings_result)['total'];
    
    $pending_bookings_query = "SELECT COUNT(*) as pending FROM bookings WHERE service_provider_id = '$provider_id' AND status = 'pending'";
    $pending_bookings_result = mysqli_query($conn, $pending_bookings_query);
    $pending_bookings = mysqli_fetch_assoc($pending_bookings_result)['pending'];
    
    $confirmed_bookings_query = "SELECT COUNT(*) as confirmed FROM bookings WHERE service_provider_id = '$provider_id' AND status = 'confirmed'";
    $confirmed_bookings_result = mysqli_query($conn, $confirmed_bookings_query);
    $confirmed_bookings = mysqli_fetch_assoc($confirmed_bookings_result)['confirmed'];
    
    $completed_bookings_query = "SELECT COUNT(*) as completed FROM bookings WHERE service_provider_id = '$provider_id' AND status = 'completed'";
    $completed_bookings_result = mysqli_query($conn, $completed_bookings_query);
    $completed_bookings = mysqli_fetch_assoc($completed_bookings_result)['completed'];
    
    // Fetch cancelled bookings count
    $cancelled_bookings_query = "SELECT COUNT(*) as cancelled FROM bookings WHERE service_provider_id = '$provider_id' AND status = 'cancelled'";
    $cancelled_bookings_result = mysqli_query($conn, $cancelled_bookings_query);
    $cancelled_bookings = mysqli_fetch_assoc($cancelled_bookings_result)['cancelled'];
    
    // Fetch recent bookings
    $recent_bookings_query = "
        SELECT b.*, u.user_name, u.phone, u.address 
        FROM bookings b 
        JOIN user u ON b.user_id = u.id 
        WHERE b.service_provider_id = '$provider_id' 
        ORDER BY b.date DESC, b.time DESC 
        LIMIT 5
    ";
    $recent_bookings_result = mysqli_query($conn, $recent_bookings_query);
    
    // Handle logout
    if(isset($_POST['logout'])) {
        session_destroy();
        header("Location: ../../login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Provider Dashboard - HomeCare Connect</title>
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
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.1);
            display: flex;
            align-items: center;
            transition: all 0.3s;
            border-left: 4px solid #3b82f6;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 1.5rem;
            color: white;
        }
        
        .bookings-icon {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }
        
        .pending-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .confirmed-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .cancelled-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .stat-info h3 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            color: #1e3a8a;
            font-weight: 700;
        }
        
        .stat-info p {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
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
        
        .view-all {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .view-all:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        
        .booking-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .booking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-radius: 8px;
            background: #f8fafc;
            transition: all 0.3s;
            border-left: 3px solid #3b82f6;
        }
        
        .booking-item:hover {
            background: #f1f5f9;
            transform: translateX(5px);
        }
        
        .booking-info h4 {
            color: #1e3a8a;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .booking-info p {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .booking-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-confirmed {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .status-cancelled {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .status-completed {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .no-bookings {
            text-align: center;
            padding: 40px 30px;
            color: #64748b;
        }
        
        .no-bookings i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #cbd5e1;
        }
        
        .no-bookings p {
            color: #64748b;
            font-weight: 500;
        }
        
        .service-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .info-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        
        .info-card h4 {
            color: #1e3a8a;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .info-card p {
            color: #64748b;
            font-weight: 500;
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
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .date-display {
                align-self: stretch;
                text-align: center;
            }
            
            .booking-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .booking-status {
                align-self: flex-start;
            }
            
            .service-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Service Provider</h3>
        <div class="sidebar-nav">
            <a href="#" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="bookings.php"><i class="fas fa-calendar-alt"></i> <span>Bookings</span></a>
            <!-- <a href="services.php"><i class="fas fa-concierge-bell"></i> <span>My Services</span></a> -->
            <a href="profile.php"><i class="fas fa-user"></i> <span>Profile</span></a>
        </div>
        <form action="" method="post">
            <button type="submit" class="logout-btn" name="logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></button>
        </form>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div class="welcome">
                <h1>Welcome, <?php echo htmlspecialchars($provider_data['user_name']); ?>!</h1>
                <p>Here's your service provider dashboard overview</p>
            </div>
            <div class="date-display">
                <i class="far fa-calendar-alt"></i> <?php echo date("l, F j, Y"); ?>
            </div>
        </div>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon bookings-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_bookings; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon pending-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $pending_bookings; ?></h3>
                    <p>Pending Requests</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon confirmed-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $confirmed_bookings; ?></h3>
                    <p>Confirmed Bookings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon cancelled-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $cancelled_bookings; ?></h3>
                    <p>Cancelled Bookings</p>
                </div>
            </div>
        </div>
        
        <div class="content-section">
            <div class="section-header">
                <h2>Recent Bookings</h2>
                <a href="bookings.php" class="view-all">View All</a>
            </div>
            <div class="booking-list">
                <?php if(mysqli_num_rows($recent_bookings_result) > 0): ?>
                    <?php while($booking = mysqli_fetch_assoc($recent_bookings_result)): ?>
                        <div class="booking-item">
                            <div class="booking-info">
                                <h4><?php echo htmlspecialchars($booking['user_name']); ?></h4>
                                <p><?php echo date("F j, Y", strtotime($booking['date'])); ?> at <?php echo date("g:i A", strtotime($booking['time'])); ?></p>
                                <p><?php echo htmlspecialchars($booking['address']); ?></p>
                            </div>
                            <div class="booking-status status-<?php echo htmlspecialchars($booking['status']); ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-bookings">
                        <i class="fas fa-calendar-times"></i>
                        <p>No bookings found. Your bookings will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="content-section">
            <div class="section-header">
                <h2>Service Information</h2>
            </div>
            <div class="service-info">
                <div class="info-card">
                    <h4>Service Details</h4>
                    <p><strong>Service Title:</strong> <?php echo htmlspecialchars($provider_data['title']); ?></p>
                    <p><strong>Category:</strong> <?php echo ucfirst(htmlspecialchars($provider_data['category'])); ?></p>
                    <p><strong>Rate:</strong> ₹<?php echo number_format($provider_data['rate'], 2); ?> / <?php echo htmlspecialchars($provider_data['per']); ?></p>
                </div>
                
                <div class="info-card">
                    <h4>Service Areas</h4>
                    <p><strong>Areas Served:</strong> <?php echo htmlspecialchars($provider_data['area']); ?></p>
                </div>
                
                <div class="info-card">
                    <h4>Service Description</h4>
                    <p><?php echo htmlspecialchars($provider_data['service_description']); ?></p>
                </div>
            </div>
        </div>
        
        <!-- <div class="content-section">
            <div class="section-header">
                <h2>Quick Actions</h2>
            </div>
            <div class="service-info">
                <div class="info-card" style="cursor: pointer; transition: all 0.3s;" onclick="window.location.href='bookings.php'">
                    <h4><i class="fas fa-calendar-plus"></i> Manage Bookings</h4>
                    <p>View and manage all your service bookings</p>
                </div>
                
                <div class="info-card" style="cursor: pointer; transition: all 0.3s;" onclick="window.location.href='services.php'">
                    <h4><i class="fas fa-edit"></i> Update Services</h4>
                    <p>Edit your service details and pricing</p>
                </div>
                
                <div class="info-card" style="cursor: pointer; transition: all 0.3s;" onclick="window.location.href='profile.php'">
                    <h4><i class="fas fa-user-cog"></i> Profile Settings</h4>
                    <p>Update your profile information</p>
                </div>
            </div>
        </div> -->
    </div>
</body>
</html>