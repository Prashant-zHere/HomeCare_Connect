<?php
    include '../../include/conn/conn.php';
    include '../../include/conn/session.php';
    
    // Check if user is logged in
    $user_id = $_SESSION['user']['id'];
    
    // Fetch user data
    $user_query = "SELECT * FROM user WHERE id = $user_id";
    $user_result = mysqli_query($conn, $user_query);
    $user_data = mysqli_fetch_assoc($user_result);
    $user_name = $user_data['user_name'];
    
    // Handle booking status filter
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    // Build the query based on filter
    if ($status_filter === 'all') {
        $bookings_query = "
            SELECT b.*, sp.user_name as provider_name, sp.title as service_title, sp.category, sp.rate, sp.photo
            FROM bookings b 
            JOIN service_provider sp ON b.service_provider_id = sp.id 
            WHERE b.user_id = $user_id 
            ORDER BY b.date DESC, b.time DESC
        ";
    } else {
        $bookings_query = "
            SELECT b.*, sp.user_name as provider_name, sp.title as service_title, sp.category, sp.rate, sp.photo
            FROM bookings b 
            JOIN service_provider sp ON b.service_provider_id = sp.id 
            WHERE b.user_id = $user_id AND b.status = '$status_filter'
            ORDER BY b.date DESC, b.time DESC
        ";
    }
    
    $bookings_result = mysqli_query($conn, $bookings_query);
    
    // Handle booking actions (cancel, complete, reschedule)
    if (isset($_POST['action'])) {
        $booking_id = $_POST['booking_id'];
        $action = $_POST['action'];
        
        switch ($action) {
            case 'cancel':
                $update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = $booking_id AND user_id = $user_id";
                if(mysqli_query($conn, $update_query)) {
                    $success_message = "Booking cancelled successfully!";
                } else {
                    $error_message = "Error cancelling booking: " . mysqli_error($conn);
                }
                break;
                
            case 'complete':
                $update_query = "UPDATE bookings SET status = 'completed' WHERE id = $booking_id AND user_id = $user_id";
                if(mysqli_query($conn, $update_query)) {
                    $success_message = "Booking marked as completed!";
                } else {
                    $error_message = "Error updating booking: " . mysqli_error($conn);
                }
                break;
                
            case 'reschedule':
                $new_date = $_POST['new_date'];
                $new_time = $_POST['new_time'];
                $update_query = "UPDATE bookings SET date = '$new_date', time = '$new_time', status = 'rescheduled' WHERE id = $booking_id AND user_id = $user_id";
                if(mysqli_query($conn, $update_query)) {
                    $success_message = "Booking rescheduled successfully!";
                } else {
                    $error_message = "Error rescheduling booking: " . mysqli_error($conn);
                }
                break;
        }
        
        // Refresh the page
        header("Location: bookings.php?status=$status_filter");
        exit();
    }
    
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
    <title>My Bookings - HomeCare Connect</title>
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
        
        .bookings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.1);
        }
        
        .bookings-header h2 {
            color: #1e3a8a;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .filters {
            display: flex;
            gap: 10px;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            background-color: #e2e8f0;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
        }
        
        .filter-btn.active, .filter-btn:hover {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .bookings-container {
            display: grid;
            gap: 20px;
        }
        
        .booking-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-left: 4px solid #3b82f6;
            transition: all 0.3s;
        }
        
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.15);
        }
        
        .booking-info {
            flex: 1;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .service-title {
            font-size: 1.3rem;
            color: #1e3a8a;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .provider-name {
            color: #64748b;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .booking-price {
            font-size: 1.2rem;
            color: #059669;
            font-weight: 700;
            margin-top: 5px;
        }
        
        .booking-status {
            padding: 8px 16px;
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
        
        .status-rescheduled {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .detail-item i {
            color: #3b82f6;
            width: 16px;
        }
        
        .booking-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 140px;
            justify-content: center;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
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
            box-shadow: 0 2px 10px rgba(100, 116, 139, 0.3);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #475569, #374151);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(100, 116, 139, 0.4);
        }
        
        .no-bookings {
            text-align: center;
            padding: 60px 30px;
            color: #64748b;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.1);
        }
        
        .no-bookings i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #cbd5e1;
        }
        
        .no-bookings h3 {
            margin-bottom: 10px;
            color: #475569;
            font-weight: 600;
        }
        
        .new-booking-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .new-booking-btn:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            color: white;
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
        
        .action-buttons-container {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
            margin-top: 15px;
        }
        
        .action-buttons-container h4 {
            color: #1e3a8a;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(30, 58, 138, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(30, 58, 138, 0.3);
            border: 2px solid #3b82f6;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .modal-header h3 {
            color: #1e3a8a;
            font-weight: 700;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
            transition: color 0.3s;
        }
        
        .close-modal:hover {
            color: #ef4444;
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
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
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
            .booking-card {
                flex-direction: column;
            }
            
            .booking-actions {
                margin-top: 20px;
                justify-content: flex-start;
            }
            
            .bookings-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .filters {
                flex-wrap: wrap;
            }
            
            .booking-actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Home Owner</h3>
        <div class="sidebar-nav">
            <a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="services.php"><i class="fas fa-concierge-bell"></i> <span>Services</span></a>
            <a href="#" class="active"><i class="fas fa-calendar-alt"></i> <span>Bookings / History</span></a>
            <a href="profile.php"><i class="fas fa-user"></i> <span>Profile</span></a>
        </div>
        <form action="" method="post">
            <button type="submit" class="logout-btn" name="logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></button>
        </form>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div class="welcome">
                <h1>My Bookings</h1>
                <p>Manage your service appointments and history</p>
            </div>
            <div class="date-display">
                <i class="far fa-calendar-alt"></i> <?php echo date("l, F j, Y"); ?>
            </div>
        </div>
        
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="bookings-header">
            <h2>Service Bookings</h2>
            <div class="filters">
                <a href="?status=all" class="filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>">All</a>
                <a href="?status=pending" class="filter-btn <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Pending</a>
                <a href="?status=confirmed" class="filter-btn <?php echo $status_filter === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
                <a href="?status=completed" class="filter-btn <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">Completed</a>
                <a href="?status=cancelled" class="filter-btn <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
            </div>
        </div>
        
        <div class="bookings-container">
            <?php if(mysqli_num_rows($bookings_result) > 0): ?>
                <?php while($booking = mysqli_fetch_assoc($bookings_result)): ?>
                    <div class="booking-card">
                        <div class="booking-info">
                            <div class="booking-header">
                                <div>
                                    <h3 class="service-title"><?php echo htmlspecialchars($booking['service_title']); ?></h3>
                                    <p class="provider-name">by <?php echo htmlspecialchars($booking['provider_name']); ?></p>
                                    <p class="booking-price">₹<?php echo number_format($booking['rate'], 2); ?></p>
                                </div>
                                <div class="booking-status status-<?php echo htmlspecialchars($booking['status']); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </div>
                            </div>
                            
                            <div class="booking-details">
                                <div class="detail-item">
                                    <i class="fas fa-calendar-day"></i>
                                    <span><?php echo date("F j, Y", strtotime($booking['date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo date("g:i A", strtotime($booking['time'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-tag"></i>
                                    <span><?php echo htmlspecialchars($booking['category']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Booking ID: #<?php echo $booking['id']; ?></span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons Section -->
                            <div class="action-buttons-container">
                                <h4>Manage Booking</h4>
                                <div class="booking-actions">
                                    <?php if($booking['status'] === 'pending' || $booking['status'] === 'confirmed'  || $booking['status'] === 'rescheduled'): ?>
                                        <!-- Cancel Service Button -->
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="action-btn btn-danger" onclick="return confirm('Are you sure you want to cancel this service? This action cannot be undone.')">
                                                <i class="fas fa-times-circle"></i> Cancel Service
                                            </button>
                                        </form>
                                        
                                        <!-- Complete Service Button -->
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="action" value="complete">
                                            <button type="submit" class="action-btn btn-success" onclick="return confirm('Mark this service as completed? Please ensure the service has been fully delivered.')">
                                                <i class="fas fa-check-circle"></i> Complete Service
                                            </button>
                                        </form>
                                        
                                        <!-- Reschedule Service Button -->
                                        <button class="action-btn btn-primary" onclick="openRescheduleModal(<?php echo $booking['id']; ?>)">
                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                        </button>
                                        
                                    <?php elseif($booking['status'] === 'completed'): ?>
                                        
                                        <a href="services.php" class="action-btn btn-secondary" style="text-decoration: none;">
                                            <i class="fas fa-redo"></i> Book Again
                                        </a>
                                        
                                    <?php elseif($booking['status'] === 'cancelled'): ?>
                                        <!-- Actions for Cancelled Bookings -->
                                        <a href="services.php" class="action-btn btn-success" style="text-decoration: none;">
                                            <i class="fas fa-plus-circle"></i> Book New Service
                                        </a>
                                        
                                    <?php else: ?>
                                        <span class="action-btn btn-secondary" style="cursor: default;">
                                            <i class="fas fa-info-circle"></i> No actions available
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Bookings Found</h3>
                    <p>You haven't made any service bookings yet.</p>
                    <a href="services.php" class="new-booking-btn">Book a Service Now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Reschedule Modal -->
    <div class="modal" id="rescheduleModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reschedule Service</h3>
                <button class="close-modal" onclick="closeRescheduleModal()">&times;</button>
            </div>
            <form method="post" id="rescheduleForm">
                <input type="hidden" name="booking_id" id="rescheduleBookingId">
                <input type="hidden" name="action" value="reschedule">
                
                <div class="form-group">
                    <label for="new_date">New Date</label>
                    <input type="date" id="new_date" name="new_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="new_time">New Time</label>
                    <input type="time" id="new_time" name="new_time" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="action-btn btn-secondary" onclick="closeRescheduleModal()">Cancel</button>
                    <button type="submit" class="action-btn btn-primary">Reschedule Service</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openRescheduleModal(bookingId) {
            document.getElementById('rescheduleBookingId').value = bookingId;
            document.getElementById('rescheduleModal').style.display = 'flex';
        }
        
        function closeRescheduleModal() {
            document.getElementById('rescheduleModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('rescheduleModal');
            if (event.target === modal) {
                closeRescheduleModal();
            }
        }
        
        // Set minimum time to current time for today's date
        const today = new Date().toISOString().split('T')[0];
        const dateInput = document.getElementById('new_date');
        const timeInput = document.getElementById('new_time');
        
        dateInput.addEventListener('change', function() {
            if (this.value === today) {
                const now = new Date();
                const currentTime = now.getHours().toString().padStart(2, '0') + ':' + 
                                  now.getMinutes().toString().padStart(2, '0');
                timeInput.min = currentTime;
            } else {
                timeInput.min = '00:00';
            }
        });
    </script>
</body>
</html>