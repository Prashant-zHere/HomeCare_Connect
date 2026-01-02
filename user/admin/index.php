<?php
include '../../include/conn/conn.php';
include '../../include/conn/session.php';

// Optional: Verify admin login
// if(!isset($_SESSION['admin'])) { header("Location: ../../login.php"); exit(); }

// Handle actions (approve, reject, delete)
if(isset($_GET['sp_id']) && isset($_GET['action'])) {
    $sp_id = mysqli_real_escape_string($conn, $_GET['sp_id']);
    $action = mysqli_real_escape_string($conn, $_GET['action']);
    
    // Validate action
    $valid_actions = ['approve', 'reject', 'delete'];
    if(in_array($action, $valid_actions)) {
        // Check if service provider exists
        $check_query = "SELECT * FROM service_provider WHERE id = '$sp_id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if(mysqli_num_rows($check_result) > 0) {
            $service_provider = mysqli_fetch_assoc($check_result);
            
            // Perform the requested action
            switch($action) {
                case 'approve':
                    $update_query = "UPDATE service_provider SET status = 'approved' WHERE id = '$sp_id'";
                    $message = "Service provider approved successfully!";
                    break;
                    
                case 'reject':
                    $update_query = "UPDATE service_provider SET status = 'rejected' WHERE id = '$sp_id'";
                    $message = "Service provider rejected successfully!";
                    break;
                    
                case 'delete':
                    // Delete uploaded file if exists
                    if(!empty($service_provider['file'])) {
                        $file_path = "../uploads/" . $service_provider['file'];
                        if(file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                    
                    $update_query = "DELETE FROM service_provider WHERE id = '$sp_id'";
                    $message = "Service provider deleted successfully!";
                    break;
            }
            
            // Execute the query
            if(mysqli_query($conn, $update_query)) {
                $_SESSION['message'] = $message;
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error performing action: " . mysqli_error($conn);
                $_SESSION['message_type'] = "error";
            }
        } else {
            $_SESSION['message'] = "Service provider not found!";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Invalid action!";
        $_SESSION['message_type'] = "error";
    }
    
    // Redirect back to avoid form resubmission
    header("Location: index.php");
    exit();
}

// === STATISTICS QUERIES ===
$total_sp_query = "SELECT COUNT(*) as total FROM service_provider";
$total_sp_result = mysqli_query($conn, $total_sp_query);
$total_sp = mysqli_fetch_assoc($total_sp_result)['total'];

// Assuming you have a 'users' table for regular users (non-service providers)
$total_users_query = "SELECT COUNT(*) as total FROM user"; // <-- Adjust table name if needed
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total'];

$pending_query = "SELECT COUNT(*) as pending FROM service_provider WHERE status = 'pending'";
$pending_result = mysqli_query($conn, $pending_query);
$pending_count = mysqli_fetch_assoc($pending_result)['pending'];

$rejected_query = "SELECT COUNT(*) as rejected FROM service_provider WHERE status = 'rejected'";
$rejected_result = mysqli_query($conn, $rejected_query);
$rejected_count = mysqli_fetch_assoc($rejected_result)['rejected'];

// Get search and sort parameters
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

// Validate sort parameters
$allowed_sort_columns = ['id', 'user_name', 'email', 'title', 'category', 'status', 'registration_date'];
$sort_by = in_array($sort_by, $allowed_sort_columns) ? $sort_by : 'id';
$sort_order = $sort_order === 'DESC' ? 'DESC' : 'ASC';

// Build the base query
$query = "SELECT * FROM service_provider";
$where_conditions = [];

// Add search conditions if search term exists
if (!empty($search_term)) {
    $search_term_clean = mysqli_real_escape_string($conn, $search_term);
    $where_conditions[] = "(user_name LIKE '%$search_term_clean%' OR email LIKE '%$search_term_clean%' OR id LIKE '%$search_term_clean%' OR title LIKE '%$search_term_clean%')";
}

// Add category filter if set
if (isset($_GET['category_filter']) && !empty($_GET['category_filter'])) {
    $category_filter = mysqli_real_escape_string($conn, $_GET['category_filter']);
    $where_conditions[] = "category = '$category_filter'";
}

// Add status filter if set
if (isset($_GET['status_filter']) && !empty($_GET['status_filter'])) {
    $status_filter = mysqli_real_escape_string($conn, $_GET['status_filter']);
    $where_conditions[] = "status = '$status_filter'";
}

// Combine all conditions
if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

// Add sorting
$query .= " ORDER BY $sort_by $sort_order";

// Execute the query
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Get unique categories and statuses for filters
$categories_query = "SELECT DISTINCT category FROM service_provider WHERE category IS NOT NULL AND category != '' ORDER BY category";
$categories_result = mysqli_query($conn, $categories_query);
$categories = [];
while($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row['category'];
}

$statuses_query = "SELECT DISTINCT status FROM service_provider WHERE status IS NOT NULL ORDER BY status";
$statuses_result = mysqli_query($conn, $statuses_query);
$statuses = [];
while($row = mysqli_fetch_assoc($statuses_result)) {
    $statuses[] = $row['status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Service Provider Registrations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../include/css/admin_dashboard.css">
    <style>
        /* Global */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header */
        .header {
            width: 100%;
            background: #1a237e;
            color: white;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #d32f2f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: #b71c1c;
        }

        /* Main Content */
        .dashboard-container {
            width: 95%;
            max-width: 1400px;
            padding: 30px;
            margin: 20px auto;
            background: #f0f2f5;
        }

        .dashboard-container h2 {
            color: #1a237e;
            margin-bottom: 30px;
            font-size: 24px;
        }

        /* Message Styles */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #555;
            font-weight: 500;
        }
        .stat-card p {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
            color: #1a237e;
        }

        /* Search and Filter Section */
        .search-filter-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .search-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-box button {
            padding: 10px 20px;
            background: #1a237e;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-box button:hover {
            background: #283593;
        }

        .filter-container {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 12px;
            color: #666;
            font-weight: bold;
        }

        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            min-width: 150px;
        }

        .reset-btn {
            align-self: flex-end;
            padding: 8px 15px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .reset-btn:hover {
            background: #5a6268;
        }

        /* Table Styles */
        .registrations-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .registrations-table th, .registrations-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
            font-size: 15px;
        }
        .registrations-table th {
            background: #e9ecef;
            color: #1a237e;
            cursor: pointer;
            position: relative;
        }
        .registrations-table th:hover {
            background: #dde1e6;
        }
        .registrations-table th.sortable:after {
            content: '↕';
            margin-left: 5px;
            font-size: 12px;
            color: #6c757d;
        }
        .registrations-table th.sorted-asc:after {
            content: '↑';
            color: #1a237e;
        }
        .registrations-table th.sorted-desc:after {
            content: '↓';
            color: #1a237e;
        }
        .registrations-table tr:last-child td {
            border-bottom: none;
        }

        /* Style for S.No column */
        .registrations-table td:first-child,
        .registrations-table th:first-child {
            text-align: center;
            width: 5%;
        }

        .action-btn {
            background: #1976d2;
            color: #fff;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
            margin-right: 5px;
            cursor: pointer;
            display: inline-block;
        }
        .action-btn:hover {
            background: #1565c0;
        }
        .approve-btn {
            background: #388e3c;
        }
        .approve-btn:hover {
            background: #2e7d32;
        }
        .reject-btn {
            background: #d32f2f;
        }
        .reject-btn:hover {
            background: #b71c1c;
        }
        .delete-btn {
            background: #f57c00;
        }
        .delete-btn:hover {
            background: #e65100;
        }
        .doc-link {
            color: #1976d2;
            text-decoration: underline;
        }

        /* Status badges */
        .status-pending { color: #f57c00; font-weight: bold; }
        .status-approved { color: #388e3c; font-weight: bold; }
        .status-rejected { color: #d32f2f; font-weight: bold; }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }

        .close:hover {
            color: #000;
        }

        .modal-details {
            margin: 20px 0;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .detail-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }

        .detail-value {
            flex: 1;
            color: #333;
        }

        .modal-actions {
            margin-top: 25px;
            text-align: right;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .results-info {
            margin: 10px 0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Admin Dashboard - Service Provider Registrations</h1>
        <form action="" method="post">
            <button type="submit" class="logout-btn" name="logout">Logout</button>
        </form>
    </div>

    <!-- Main Content -->
    <div class="dashboard-container">
        <!-- Message Display -->
        <?php if(isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type']; ?>">
                <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <h2>Dashboard Overview</h2>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Service Providers</h3>
                <p><?php echo $total_sp; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending Approvals</h3>
                <p><?php echo $pending_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Rejected Approvals</h3>
                <p><?php echo $rejected_count; ?></p>
            </div>
        </div>

        <h2>Service Provider Registrations</h2>

        <!-- Search and Filter Section -->
        <div class="search-filter-container">
            <form method="GET" action="">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search by name, email, ID, or service title..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit">Search</button>
                </div>
                <div class="filter-container">
                    <div class="filter-group">
                        <label>Sort By</label>
                        <select name="sort_by">
                            <option value="id" <?php echo $sort_by == 'id' ? 'selected' : ''; ?>>ID</option>
                            <option value="user_name" <?php echo $sort_by == 'user_name' ? 'selected' : ''; ?>>Name</option>
                            <option value="email" <?php echo $sort_by == 'email' ? 'selected' : ''; ?>>Email</option>
                            <option value="title" <?php echo $sort_by == 'title' ? 'selected' : ''; ?>>Service Title</option>
                            <option value="category" <?php echo $sort_by == 'category' ? 'selected' : ''; ?>>Category</option>
                            <option value="status" <?php echo $sort_by == 'status' ? 'selected' : ''; ?>>Status</option>
                            <option value="registration_date" <?php echo $sort_by == 'registration_date' ? 'selected' : ''; ?>>Registration Date</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Sort Order</label>
                        <select name="sort_order">
                            <option value="ASC" <?php echo $sort_order == 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                            <option value="DESC" <?php echo $sort_order == 'DESC' ? 'selected' : ''; ?>>Descending</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Filter by Category</label>
                        <select name="category_filter" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" 
                                    <?php echo (isset($_GET['category_filter']) && $_GET['category_filter'] == $category) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Filter by Status</label>
                        <select name="status_filter" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <?php foreach($statuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>" 
                                    <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == $status) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($status); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <a href="?" class="reset-btn">Reset Filters</a>
                </div>
            </form>
        </div>

        <div class="results-info">
            Showing <?php echo mysqli_num_rows($result); ?> result(s)
        </div>

        <table class="registrations-table">
            <tr>
                <th>S.No</th>
                <th class="sortable <?php echo $sort_by == 'id' ? 'sorted-' . strtolower($sort_order) : ''; ?>" 
                    onclick="sortTable('id', '<?php echo $sort_by == 'id' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>')">ID</th>
                <th class="sortable <?php echo $sort_by == 'user_name' ? 'sorted-' . strtolower($sort_order) : ''; ?>" 
                    onclick="sortTable('user_name', '<?php echo $sort_by == 'user_name' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>')">User Name</th>
                <th class="sortable <?php echo $sort_by == 'email' ? 'sorted-' . strtolower($sort_order) : ''; ?>" 
                    onclick="sortTable('email', '<?php echo $sort_by == 'email' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>')">Email</th>
                <th class="sortable <?php echo $sort_by == 'title' ? 'sorted-' . strtolower($sort_order) : ''; ?>" 
                    onclick="sortTable('title', '<?php echo $sort_by == 'title' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>')">Service Title</th>
                <th class="sortable <?php echo $sort_by == 'category' ? 'sorted-' . strtolower($sort_order) : ''; ?>" 
                    onclick="sortTable('category', '<?php echo $sort_by == 'category' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>')">Category</th>
                <th>Document</th>
                <th class="sortable <?php echo $sort_by == 'status' ? 'sorted-' . strtolower($sort_order) : ''; ?>" 
                    onclick="sortTable('status', '<?php echo $sort_by == 'status' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>')">Status</th>
                <th>Action</th>
            </tr>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php $sno = 1; ?>
                <?php while($sp = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $sno++; ?></td>
                        <td><?php echo htmlspecialchars($sp['id']); ?></td>
                        <td><?php echo htmlspecialchars($sp['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($sp['email']); ?></td>
                        <td><?php echo htmlspecialchars($sp['title']); ?></td>
                        <td><?php echo htmlspecialchars($sp['category']); ?></td>
                        <td>
                            <?php if(!empty($sp['file'])): ?>
                                <a class="doc-link" href="../uploads/<?php echo htmlspecialchars($sp['file']); ?>" target="_blank">View Document</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-<?php echo strtolower($sp['status']); ?>">
                                <?php echo ucfirst(htmlspecialchars($sp['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <button class="action-btn" onclick="openModal(<?php echo htmlspecialchars(json_encode($sp)); ?>)">View</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align:center;">No registrations found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Modal Popup -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Service Provider Details</h2>
            
            <div class="modal-details" id="modalDetails">
                <!-- Details will be populated by JavaScript -->
            </div>
            
            <div class="modal-actions" id="modalActions">
                <!-- Action buttons will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        function openModal(serviceProvider) {
            const modal = document.getElementById('detailsModal');
            const modalDetails = document.getElementById('modalDetails');
            const modalActions = document.getElementById('modalActions');
            
            // Populate details
            modalDetails.innerHTML = `
                <div class="detail-row">
                    <div class="detail-label">ID:</div>
                    <div class="detail-value">${serviceProvider.id}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">User Name:</div>
                    <div class="detail-value">${serviceProvider.user_name}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value">${serviceProvider.email}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Service Title:</div>
                    <div class="detail-value">${serviceProvider.title}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Category:</div>
                    <div class="detail-value">${serviceProvider.category}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Description:</div>
                    <div class="detail-value">${serviceProvider.description || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Document:</div>
                    <div class="detail-value">
                        ${serviceProvider.file ? 
                            `<a class="doc-link" href="../uploads/${serviceProvider.file}" target="_blank">View Document</a>` : 
                            'N/A'}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">${serviceProvider.status}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Registration Date:</div>
                    <div class="detail-value">${serviceProvider.registration_date || 'N/A'}</div>
                </div>
            `;
            
            // Populate action buttons based on status
            let actionButtons = '';
            const status = serviceProvider.status.toLowerCase();
            
            if (status === 'pending') {
                actionButtons = `
                    <a class="action-btn approve-btn" href="?sp_id=${serviceProvider.id}&action=approve" onclick="return confirm('Are you sure you want to approve this service provider?')">Approve</a>
                    <a class="action-btn reject-btn" href="?sp_id=${serviceProvider.id}&action=reject" onclick="return confirm('Are you sure you want to reject this service provider?')">Reject</a>
                    <a class="action-btn delete-btn" href="?sp_id=${serviceProvider.id}&action=delete" onclick="return confirm('Are you sure you want to delete this service provider? This action cannot be undone.')">Delete</a>
                `;
            } else if (status === 'rejected') {
                actionButtons = `
                    <a class="action-btn approve-btn" href="?sp_id=${serviceProvider.id}&action=approve" onclick="return confirm('Are you sure you want to approve this service provider?')">Approve</a>
                    <a class="action-btn delete-btn" href="?sp_id=${serviceProvider.id}&action=delete" onclick="return confirm('Are you sure you want to delete this service provider? This action cannot be undone.')">Delete</a>
                `;
            } else if (status === 'approved') {
                actionButtons = `
                    <a class="action-btn reject-btn" href="?sp_id=${serviceProvider.id}&action=reject" onclick="return confirm('Are you sure you want to reject this service provider?')">Reject</a>
                    <a class="action-btn delete-btn" href="?sp_id=${serviceProvider.id}&action=delete" onclick="return confirm('Are you sure you want to delete this service provider? This action cannot be undone.')">Delete</a>
                `;
            }
            
            modalActions.innerHTML = actionButtons;
            modal.style.display = 'block';
        }
        
        function closeModal() {
            const modal = document.getElementById('detailsModal');
            modal.style.display = 'none';
        }
        
        function sortTable(column, order) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort_by', column);
            url.searchParams.set('sort_order', order);
            window.location.href = url.toString();
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>