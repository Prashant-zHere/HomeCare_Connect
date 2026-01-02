<?php
    include '../../include/conn/conn.php';
    include '../../include/conn/session.php';
    
    // Check if service provider is logged in
    $provider_id = $_SESSION['user']['id'];
    
    // Fetch service provider data
    $provider_query = "SELECT * FROM service_provider WHERE id = '$provider_id'";
    $provider_result = mysqli_query($conn, $provider_query);
    $provider_data = mysqli_fetch_assoc($provider_result);
    
    // Handle booking actions
    if(isset($_POST['confirm_booking'])) {
        $booking_id = $_POST['booking_id'];
        $update_query = "UPDATE bookings SET status = 'confirmed' WHERE id = '$booking_id' AND service_provider_id = '$provider_id'";
        mysqli_query($conn, $update_query);
        echo "<script>alert('Booking confirmed successfully');</script>";
        header("Location: bookings.php");
        exit();
    }
    
    if(isset($_POST['cancel_booking'])) {
        $booking_id = $_POST['booking_id'];
        $update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = '$booking_id' AND service_provider_id = '$provider_id'";
        mysqli_query($conn, $update_query);
        echo "<script>alert('Booking cancelled successfully');</script>";
        header("Location: bookings.php");
        exit();
    }
    
    // Fetch booking details for popup
    $booking_details = null;
    if(isset($_GET['view_booking'])) {
        $booking_id = $_GET['view_booking'];
        $details_query = "
            SELECT b.*, u.user_name, u.phone, u.address, u.email,
                   sp.title as service_title, sp.category, sp.rate, sp.per
            FROM bookings b 
            JOIN user u ON b.user_id = u.id 
            JOIN service_provider sp ON b.service_provider_id = sp.id
            WHERE b.id = '$booking_id' AND b.service_provider_id = '$provider_id'
        ";
        $details_result = mysqli_query($conn, $details_query);
        if($details_result && mysqli_num_rows($details_result) > 0) {
            $booking_details = mysqli_fetch_assoc($details_result);
        }
    }
    
    // Filter handling
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    $date_filter = isset($_GET['date']) ? $_GET['date'] : '';
    
    // Build query with filters
    $bookings_query = "
        SELECT b.*, u.user_name, u.phone, u.address, u.email 
        FROM bookings b 
        JOIN user u ON b.user_id = u.id 
        WHERE b.service_provider_id = '$provider_id'
    ";
    
    if($status_filter != 'all') {
        $bookings_query .= " AND b.status = '$status_filter'";
    }
    
    if(!empty($date_filter)) {
        $bookings_query .= " AND b.date = '$date_filter'";
    }
    
    $bookings_query .= " ORDER BY b.date DESC, b.time DESC";
    
    $bookings_result = mysqli_query($conn, $bookings_query);
    
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
    <title>Manage Bookings - HomeCare Connect</title>
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
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 600;
        }
        
        .filter-select, .filter-input {
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
            color: #2d3748;
            transition: all 0.3s;
        }
        
        .filter-select:focus, .filter-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .apply-filters {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s;
            align-self: flex-end;
        }
        
        .apply-filters:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .bookings-table th {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .bookings-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .bookings-table tr:hover {
            background-color: #f8fafc;
        }
        
        .booking-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            display: inline-block;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .status-confirmed {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .status-completed {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .status-cancelled {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .btn-confirm {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-cancel {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
        
        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .customer-name {
            font-weight: 600;
            color: #1e3a8a;
        }
        
        .customer-contact {
            font-size: 0.8rem;
            color: #64748b;
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: modalAppear 0.3s ease-out;
        }
        
        @keyframes modalAppear {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .close-modal:hover {
            transform: scale(1.1);
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .booking-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
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
        }
        
        .customer-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .customer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3b82f6;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .customer-details h4 {
            color: #1e3a8a;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .customer-details p {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .notes-section {
            margin: 20px 0;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        
        .notes-section h4 {
            color: #1e3a8a;
            margin-bottom: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .notes-content {
            color: #374151;
            line-height: 1.6;
            font-size: 0.95rem;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .no-notes {
            color: #9ca3af;
            font-style: italic;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .modal-btn-confirm {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .modal-btn-cancel {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .modal-btn-close {
            background: #64748b;
            color: white;
        }
        
        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
            .bookings-table {
                display: block;
                overflow-x: auto;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .apply-filters {
                align-self: stretch;
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
            
            .action-buttons {
                flex-direction: column;
            }
            
            .booking-details-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Service Provider</h3>
        <div class="sidebar-nav">
            <a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="#" class="active"><i class="fas fa-calendar-alt"></i> <span>Bookings</span></a>
            <!-- <a href="services.php"><i class="fas fa-concierge-bell"></i> <span>My Services</span></a> -->
            <a href="profile.php"><i class="fas fa-user"></i> <span>Profile</span></a>
            <!-- <a href="earnings.php"><i class="fas fa-chart-line"></i> <span>Earnings</span></a> -->
        </div>
        <form action="" method="post">
            <button type="submit" class="logout-btn" name="logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></button>
        </form>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div class="welcome">
                <h1>Manage Bookings</h1>
                <p>View and manage all your service bookings</p>
            </div>
            <div class="date-display">
                <i class="far fa-calendar-alt"></i> <?php echo date("l, F j, Y"); ?>
            </div>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="content-section">
            <div class="section-header">
                <h2>All Bookings</h2>
            </div>
            
            <form method="GET" action="bookings.php">
                <div class="filters">
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="filter-select">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="filter-input" value="<?php echo htmlspecialchars($date_filter); ?>">
                    </div>
                    
                    <button type="submit" class="apply-filters">Apply Filters</button>
                </div>
            </form>
            
            <?php if(mysqli_num_rows($bookings_result) > 0): ?>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Service Date</th>
                            <th>Service Time</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = mysqli_fetch_assoc($bookings_result)): ?>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name"><?php echo htmlspecialchars($booking['user_name']); ?></span>
                                        <span class="customer-contact"><?php echo htmlspecialchars($booking['phone']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo date("F j, Y", strtotime($booking['date'])); ?></td>
                                <td><?php echo date("g:i A", strtotime($booking['time'])); ?></td>
                                <td><?php echo htmlspecialchars($booking['address']); ?></td>
                                <td>
                                    <span class="booking-status status-<?php echo htmlspecialchars($booking['status']); ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="bookings.php?view_booking=<?php echo $booking['id']; ?>" class="btn btn-view">View</a>
                                        
                                        <?php if($booking['status'] == 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" name="confirm_booking" class="btn btn-confirm">Confirm</button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" name="cancel_booking" class="btn btn-cancel">Cancel</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-calendar-times"></i>
                    <p>No bookings found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Booking Details Modal -->
    <?php if(isset($_GET['view_booking']) && $booking_details): ?>
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Booking Details</h3>
                <a href="bookings.php" class="close-modal">&times;</a>
            </div>
            <div class="modal-body">
                <div class="customer-profile">
                    <div class="customer-avatar">
                        <?php echo strtoupper(substr($booking_details['user_name'], 0, 1)); ?>
                    </div>
                    <div class="customer-details">
                        <h4><?php echo htmlspecialchars($booking_details['user_name']); ?></h4>
                        <p><?php echo htmlspecialchars($booking_details['email']); ?></p>
                        <p><?php echo htmlspecialchars($booking_details['phone']); ?></p>
                    </div>
                </div>
                
                <div class="booking-details-grid">
                    <div class="detail-group">
                        <span class="detail-label">Service Type</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking_details['service_title']); ?></span>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Category</span>
                        <span class="detail-value"><?php echo ucfirst(htmlspecialchars($booking_details['category'])); ?></span>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Service Date</span>
                        <span class="detail-value"><?php echo date("F j, Y", strtotime($booking_details['date'])); ?></span>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Service Time</span>
                        <span class="detail-value"><?php echo date("g:i A", strtotime($booking_details['time'])); ?></span>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Service Rate</span>
                        <span class="detail-value">₹<?php echo number_format($booking_details['rate'], 2); ?> / <?php echo htmlspecialchars($booking_details['per']); ?></span>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Status</span>
                        <span class="detail-value">
                            <span class="booking-status status-<?php echo htmlspecialchars($booking_details['status']); ?>">
                                <?php echo ucfirst($booking_details['status']); ?>
                            </span>
                        </span>
                    </div>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Service Address</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking_details['address']); ?></span>
                </div>
                
                <!-- Notes Section -->
                <div class="notes-section">
                    <h4><i class="fas fa-sticky-note"></i> Customer Notes</h4>
                    <div class="notes-content">
                        <?php if(!empty($booking_details['notes'])): ?>
                            <?php echo nl2br(htmlspecialchars($booking_details['notes'])); ?>
                        <?php else: ?>
                            <span class="no-notes">No additional notes provided by the customer.</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if($booking_details['status'] == 'pending'): ?>
                <div class="modal-actions">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_details['id']; ?>">
                        <button type="submit" name="confirm_booking" class="modal-btn modal-btn-confirm">Confirm Booking</button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_details['id']; ?>">
                        <button type="submit" name="cancel_booking" class="modal-btn modal-btn-cancel">Cancel Booking</button>
                    </form>
                    <a href="bookings.php" class="modal-btn modal-btn-close">Close</a>
                </div>
                <?php else: ?>
                <div class="modal-actions">
                    <a href="bookings.php" class="modal-btn modal-btn-close">Close</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>