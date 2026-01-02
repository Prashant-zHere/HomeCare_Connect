<?php
    include '../../include/conn/conn.php';
    include '../../include/conn/session.php';
    
    // Check if user is logged in
    // if (!isset($_SESSION['user_id'])) {
    //     header("Location: ../../login.php");
    //     exit();
    // }
    
    $user_id = $_SESSION['user']['id'];
    
    // Fetch user data
    $user_query = "SELECT * FROM user WHERE id = $user_id";
    $user_result = mysqli_query($conn, $user_query);
    $user_data = mysqli_fetch_assoc($user_result);
    $user_name = $user_data['user_name'];
    
    // Fetch booking statistics
    $total_bookings_query = "SELECT COUNT(*) as total FROM bookings WHERE user_id = $user_id";
    $total_bookings_result = mysqli_query($conn, $total_bookings_query);
    $total_bookings_data = mysqli_fetch_assoc($total_bookings_result);
    $total_bookings = $total_bookings_data['total'];
    
    $active_bookings_query = "SELECT COUNT(*) as active FROM bookings WHERE user_id = $user_id AND status = 'confirmed'";
    $active_bookings_result = mysqli_query($conn, $active_bookings_query);
    $active_bookings_data = mysqli_fetch_assoc($active_bookings_result);
    $active_bookings = $active_bookings_data['active'];
    
    $pending_requests_query = "SELECT COUNT(*) as pending FROM bookings WHERE user_id = $user_id AND (status = 'pending' OR status = 'rescheduled')";
    $pending_requests_result = mysqli_query($conn, $pending_requests_query);
    $pending_requests_data = mysqli_fetch_assoc($pending_requests_result);
    $pending_requests = $pending_requests_data['pending'];
    
    $cancelled_requests_query = "SELECT COUNT(*) as cancelled FROM bookings WHERE user_id = $user_id AND status = 'cancelled'";
    $cancelled_requests_result = mysqli_query($conn, $cancelled_requests_query);
    $cancelled_requests_data = mysqli_fetch_assoc($cancelled_requests_result);

    $recent_bookings_query = "
        SELECT b.*, sp.user_name as provider_name, sp.title as service_title, sp.category 
        FROM bookings b 
        JOIN service_provider sp ON b.service_provider_id = sp.id 
        WHERE b.user_id = $user_id 
        ORDER BY b.date DESC 
        LIMIT 5
    ";
    $recent_bookings_result = mysqli_query($conn, $recent_bookings_query);
    
    $total_earnings = 0;
    $earnings_query = "
        SELECT SUM(sp.rate) as total_earnings 
        FROM bookings b 
        JOIN service_provider sp ON b.service_provider_id = sp.id 
        WHERE b.user_id = $user_id AND b.status = 'confirmed'
    ";
    $earnings_result = mysqli_query($conn, $earnings_query);
    if ($earnings_result && mysqli_num_rows($earnings_result) > 0) {
        $earnings_data = mysqli_fetch_assoc($earnings_result);
        $total_earnings = $earnings_data['total_earnings'] ? $earnings_data['total_earnings'] : 0;
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Owner Dashboard</title>
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
    
    .active-icon {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    
    .earnings-icon {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }
    
    .pending-icon {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
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
    
    .chart-container {
        height: 300px;
        margin-top: 20px;
        background: #f8fafc;
        border-radius: 8px;
        padding: 20px;
        border: 2px solid #e2e8f0;
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
    }
</style>
</head>
<body>
    <div class="sidebar">
        <h3>Home Owner</h3>
        <div class="sidebar-nav">
            <a href="#" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="services.php"><i class="fas fa-concierge-bell"></i> <span>Services</span></a>
            <a href="bookings.php"><i class="fas fa-calendar-alt"></i> <span>Bookings / History</span></a>
            <a href="profile.php"><i class="fas fa-user"></i> <span>Profile</span></a>
        </div>
        <form action="" method="post">
            <button type="submit" class="logout-btn" name="logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></button>
        </form>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div class="welcome">
                <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p>Here's what's happening with your service bookings today.</p>
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
                <div class="stat-icon active-icon">
                    <i class="fas fa-home"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $active_bookings; ?></h3>
                    <p>Active Bookings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon cross-icon">
                    <i class="fas fa-times"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $cancelled_requests_data['cancelled']; ?></h3>
                    <p>Cancelled Bookings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon pending-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $pending_requests; ?></h3>
                    <p>Pending Requests</p>
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
                                <h4><?php echo htmlspecialchars($booking['service_title']); ?> - <?php echo htmlspecialchars($booking['provider_name']); ?></h4>
                                <p><?php echo date("M j, Y", strtotime($booking['date'])); ?> at <?php echo date("g:i A", strtotime($booking['time'])); ?> • <?php echo htmlspecialchars($booking['category']); ?></p>
                            </div>
                            <div class="booking-status status-<?php echo htmlspecialchars($booking['status']); ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-bookings">
                        <i class="fas fa-calendar-times"></i>
                        <p>No bookings found. <a href="bookings.php">Book a service now!</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        
    </div>
</body>
</html>