<?php
    include '../../include/conn/conn.php';
    include '../../include/conn/session.php';
    
    // Check if user is logged in and get user ID
    $user_id = $_SESSION['user']['id'];
    
    // Fetch user data
    $user_query = "SELECT * FROM user WHERE id = $user_id";
    $user_result = mysqli_query($conn, $user_query);
    $user_data = mysqli_fetch_assoc($user_result);
    $user_name = $user_data['user_name'];
    $user_city = $user_data['city'];
    
    // Handle filters and search
    $category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
    $search_query = isset($_GET['search']) ? $_GET['search'] : '';
    $sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name';
    
    // Build the query
    $services_query = "
        SELECT * FROM service_provider 
        WHERE status = 'approved' 
    ";
    
    if ($category_filter !== 'all') {
        $services_query .= " AND category = '$category_filter'";
    }
    
    if (!empty($search_query)) {
        $services_query .= " AND (title LIKE '%$search_query%' OR service_description LIKE '%$search_query%' OR area LIKE '%$search_query%' OR user_name LIKE '%$search_query%')";
    }
    
    // Add sorting
    switch ($sort_by) {
        case 'price_low':
            $services_query .= " ORDER BY rate ASC";
            break;
        case 'price_high':
            $services_query .= " ORDER BY rate DESC";
            break;
        case 'name':
            $services_query .= " ORDER BY user_name ASC";
            break;
        default:
            $services_query .= " ORDER BY user_name ASC";
    }
    
    $services_result = mysqli_query($conn, $services_query);
    
    // Get unique categories for filter
    $categories_query = "SELECT DISTINCT category FROM service_provider WHERE status = 'approved'";
    $categories_result = mysqli_query($conn, $categories_query);
    
    // Handle service booking
    if (isset($_POST['book_service'])) {
        $service_provider_id = mysqli_real_escape_string($conn, $_POST['service_provider_id']);
        $date = mysqli_real_escape_string($conn, $_POST['date']);
        $time = mysqli_real_escape_string($conn, $_POST['time']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        
        // Validate required fields
        if (empty($service_provider_id) || empty($date) || empty($time)) {
            $booking_error = "Please fill all required fields.";
        } else {
            // Check if the service provider exists and is approved
            $provider_check = "SELECT id FROM service_provider WHERE id = '$service_provider_id' AND status = 'approved'";
            $provider_result = mysqli_query($conn, $provider_check);
            
            if (mysqli_num_rows($provider_result) > 0) {
                $insert_query = "
                    INSERT INTO bookings (user_id, service_provider_id, date, time, status, notes) 
                    VALUES ($user_id, '$service_provider_id', '$date', '$time', 'pending', '$notes')
                ";
                
                if (mysqli_query($conn, $insert_query)) {
                    $booking_success = "Booking request submitted successfully! The service provider will confirm your booking soon.";
                    // Clear form data
                    unset($_POST);
                } else {
                    $booking_error = "Error submitting booking request: " . mysqli_error($conn);
                }
            } else {
                $booking_error = "Invalid service provider or service not available.";
            }
        }
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
    <title>Services - HomeCare Connect</title>
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
    
    .filters-section {
        background-color: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(30, 58, 138, 0.1);
        margin-bottom: 30px;
        border-left: 4px solid #3b82f6;
    }
    
    .filters-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .filters-header h2 {
        color: #1e3a8a;
        font-size: 1.4rem;
        font-weight: 700;
    }
    
    .search-box {
        position: relative;
        width: 300px;
    }
    
    .search-box input {
        width: 100%;
        padding: 12px 45px 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 25px;
        font-size: 1rem;
        transition: all 0.3s;
        background: #f8fafc;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        background: white;
    }
    
    .search-btn {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        transition: color 0.3s;
    }
    
    .search-btn:hover {
        color: #3b82f6;
    }
    
    .filters-row {
        display: grid;
        grid-template-columns: auto auto 1fr;
        gap: 20px;
        align-items: center;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
    }
    
    .filter-group label {
        margin-bottom: 8px;
        color: #1e3a8a;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .filter-select {
        padding: 10px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background-color: white;
        font-size: 1rem;
        min-width: 150px;
        transition: all 0.3s;
    }
    
    .filter-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    
    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
    }
    
    .service-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(30, 58, 138, 0.1);
        transition: all 0.3s;
        border-left: 4px solid #3b82f6;
    }
    
    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(30, 58, 138, 0.15);
    }
    
    .service-image {
        height: 200px;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    
    .service-content {
        padding: 25px;
    }
    
    .service-header {
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
    
    .service-provider {
        color: #64748b;
        font-size: 1rem;
        font-weight: 500;
    }
    
    .service-price {
        font-size: 1.5rem;
        color: #059669;
        font-weight: 700;
    }
    
    .service-price span {
        font-size: 0.9rem;
        color: #64748b;
        font-weight: normal;
    }
    
    .service-description {
        color: #5a6c7d;
        margin-bottom: 20px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .service-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-top: 15px;
        border-top: 2px solid #e2e8f0;
    }
    
    .service-category {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .service-rating {
        color: #f59e0b;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .service-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-primary {
        flex: 1;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        border: none;
        padding: 12px 20px;
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
        padding: 12px 15px;
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
    
    .no-services {
        text-align: center;
        padding: 60px 30px;
        color: #64748b;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(30, 58, 138, 0.1);
        grid-column: 1 / -1;
    }
    
    .no-services i {
        font-size: 4rem;
        margin-bottom: 20px;
        color: #cbd5e1;
    }
    
    .no-services h3 {
        margin-bottom: 10px;
        color: #475569;
        font-weight: 600;
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
        padding: 20px;
    }
    
    .modal-content {
        background-color: white;
        padding: 30px;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
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
        margin: 0;
        font-weight: 700;
    }
    
    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #64748b;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.3s;
    }
    
    .close-modal:hover {
        color: #ef4444;
    }
    
    .provider-details {
        display: grid;
        gap: 20px;
    }
    
    .detail-section {
        margin-bottom: 20px;
    }
    
    .detail-section h4 {
        color: #1e3a8a;
        margin-bottom: 10px;
        font-size: 1.1rem;
        font-weight: 700;
    }
    
    .detail-section p {
        color: #5a6c7d;
        line-height: 1.6;
        font-weight: 500;
    }
    
    .areas-served {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }
    
    .area-tag {
        background: linear-gradient(135deg, #dbeafe, #93c5fd);
        color: #1e40af;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid #93c5fd;
    }
    
    .provider-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin: 20px 0;
    }
    
    .stat-item {
        text-align: center;
        padding: 15px;
        background: #f8fafc;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e3a8a;
        display: block;
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 5px;
        font-weight: 600;
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
        background: white;
    }
    
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 25px;
    }
    
    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
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
        
        .filters-row {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .search-box {
            width: 100%;
        }
    }
    
    @media (max-width: 768px) {
        .services-grid {
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
        
        .provider-stats {
            grid-template-columns: 1fr;
        }
        
        .modal-content {
            padding: 20px;
        }
        
        .service-header {
            flex-direction: column;
            gap: 10px;
        }
        
        .service-actions {
            flex-direction: column;
        }
    }
</style>
</head>
<body>
    <div class="sidebar">
        <h3>Home Owner</h3>
        <div class="sidebar-nav">
            <a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="#" class="active"><i class="fas fa-concierge-bell"></i> <span>Services</span></a>
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
                <h1>Find Services</h1>
                <p>Browse and book home services in your area</p>
            </div>
            <div class="date-display">
                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user_city); ?>
            </div>
        </div>
        
        <?php if(isset($booking_success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $booking_success; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($booking_error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $booking_error; ?>
            </div>
        <?php endif; ?>
        
        <div class="filters-section">
            <div class="filters-header">
                <h2>Available Services</h2>
                <form method="get" class="search-box">
                    <input type="text" name="search" placeholder="Search services..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <form method="get" class="filters-row">
                <div class="filter-group">
                    <label for="category">Category</label>
                    <select name="category" id="category" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $category_filter === 'all' ? 'selected' : ''; ?>>All Categories</option>
                        <?php 
                            mysqli_data_seek($categories_result, 0); // Reset pointer
                            while($category = mysqli_fetch_assoc($categories_result)): 
                        ?>
                            <option value="<?php echo htmlspecialchars($category['category']); ?>" 
                                <?php echo $category_filter === $category['category'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst(htmlspecialchars($category['category'])); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="sort">Sort By</label>
                    <select name="sort" id="sort" class="filter-select" onchange="this.form.submit()">
                        <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name</option>
                        <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
                
                <?php if(!empty($search_query) || $category_filter !== 'all'): ?>
                    <div style="display: flex; justify-content: flex-end; align-items: flex-end;">
                        <a href="services.php" class="btn-secondary" style="text-decoration: none; display: inline-block;">
                            Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="services-grid">
            <?php if(mysqli_num_rows($services_result) > 0): ?>
                <?php while($service = mysqli_fetch_assoc($services_result)): ?>
                    <div class="service-card">
                        <div class="service-image">
                            <?php 
                                $icons = [
                                    'electrical' => 'fas fa-bolt',
                                    'plumbing' => 'fas fa-faucet',
                                    'cleaning' => 'fas fa-broom',
                                    'painting' => 'fas fa-paint-roller',
                                    'carpentry' => 'fas fa-hammer',
                                    'gardening' => 'fas fa-leaf',
                                    'default' => 'fas fa-tools'
                                ];
                                $icon = $icons[strtolower($service['category'])] ?? $icons['default'];
                            ?>
                            <i class="<?php echo $icon; ?>"></i>
                        </div>
                        
                        <div class="service-content">
                            <div class="service-header">
                                <div>
                                    <h3 class="service-title"><?php echo htmlspecialchars($service['title']); ?></h3>
                                    <p class="service-provider">by <?php echo htmlspecialchars($service['user_name']); ?></p>
                                </div>
                                <div class="service-price">
                                    ₹<?php echo number_format($service['rate'], 2); ?>
                                    <span>/<?php echo htmlspecialchars($service['per']); ?></span>
                                </div>
                            </div>
                            
                            <p class="service-description">
                                <?php echo htmlspecialchars($service['service_description']); ?>
                            </p>
                            
                            <div class="service-meta">
                                <span class="service-category"><?php echo ucfirst(htmlspecialchars($service['category'])); ?></span>
                                <span class="service-rating">
                                    <i class="fas fa-star"></i> 4.5
                                </span>
                            </div>
                            
                            <div class="service-actions">
                                <button class="btn-primary" onclick="openBookingModal(
                                    '<?php echo $service['id']; ?>',
                                    '<?php echo htmlspecialchars(addslashes($service['title'])); ?>',
                                    '<?php echo htmlspecialchars(addslashes($service['user_name'])); ?>',
                                    <?php echo $service['rate']; ?>,
                                    '<?php echo htmlspecialchars($service['per']); ?>'
                                )">
                                    <i class="fas fa-calendar-plus"></i> Book Now
                                </button>
                                <button class="btn-secondary" onclick="openProviderModal(
                                    '<?php echo $service['id']; ?>',
                                    '<?php echo htmlspecialchars(addslashes($service['user_name'])); ?>',
                                    '<?php echo htmlspecialchars(addslashes($service['title'])); ?>',
                                    `<?php echo htmlspecialchars($service['service_description']); ?>`,
                                    `<?php echo htmlspecialchars($service['description'] ?? 'No description available.'); ?>`,
                                    '<?php echo htmlspecialchars($service['area']); ?>',
                                    '<?php echo htmlspecialchars($service['category']); ?>',
                                    <?php echo $service['rate']; ?>,
                                    '<?php echo htmlspecialchars($service['per']); ?>'
                                )">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-services">
                    <i class="fas fa-search"></i>
                    <h3>No Services Found</h3>
                    <p>Try adjusting your search filters or browse all categories.</p>
                    <a href="services.php" class="btn-primary" style="text-decoration: none; display: inline-block; margin-top: 15px;">
                        View All Services
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Booking Modal -->
    <div class="modal" id="bookingModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Book Service</h3>
                <button class="close-modal" onclick="closeBookingModal()">&times;</button>
            </div>
            <form method="post" id="bookingForm">
                <input type="hidden" name="service_provider_id" id="serviceProviderId">
                <input type="hidden" name="book_service" value="1">
                
                <div class="form-group">
                    <label>Service</label>
                    <input type="text" id="serviceTitle" readonly style="background-color: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label>Provider</label>
                    <input type="text" id="providerName" readonly style="background-color: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label>Rate</label>
                    <input type="text" id="serviceRate" readonly style="background-color: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label for="date">Preferred Date <span class="required">*</span></label>
                    <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="time">Preferred Time <span class="required">*</span></label>
                    <input type="time" id="time" name="time" required>
                </div>
                
                <div class="form-group">
                    <label for="notes">Additional Notes (Optional)</label>
                    <textarea id="notes" name="notes" placeholder="Any specific requirements or details..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeBookingModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Provider Details Modal -->
    <div class="modal" id="providerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Service Provider Details</h3>
                <button class="close-modal" onclick="closeProviderModal()">&times;</button>
            </div>
            
            <div class="provider-details">
                <div class="detail-section">
                    <h4 id="modalProviderName"></h4>
                    <p class="service-category" id="modalServiceCategory"></p>
                </div>
                
                <div class="provider-stats">
                    <div class="stat-item">
                        <span class="stat-value" id="modalServiceRate"></span>
                        <span class="stat-label" id="modalServicePer"></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">4.5</span>
                        <span class="stat-label">Rating</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">50+</span>
                        <span class="stat-label">Jobs Done</span>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Service Description</h4>
                    <p id="modalServiceDescription"></p>
                </div>
                
                <div class="detail-section">
                    <h4>About the Provider</h4>
                    <p id="modalProviderDescription"></p>
                </div>
                
                <div class="detail-section">
                    <h4>Areas Served</h4>
                    <div class="areas-served" id="modalAreasServed"></div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeProviderModal()">Close</button>
                <button type="button" class="btn-primary" id="bookFromProviderBtn">Book This Service</button>
            </div>
        </div>
    </div>
    
    <script>
        let currentServiceData = {};
        
        function openBookingModal(providerId, serviceTitle, providerName, rate, per) {
            document.getElementById('serviceProviderId').value = providerId;
            document.getElementById('serviceTitle').value = serviceTitle;
            document.getElementById('providerName').value = providerName;
            document.getElementById('serviceRate').value = '₹' + rate + ' / ' + per;
            
            // Reset form fields
            document.getElementById('date').value = '';
            document.getElementById('time').value = '';
            document.getElementById('notes').value = '';
            
            document.getElementById('bookingModal').style.display = 'flex';
            
            // Store current service data for switching from provider modal
            currentServiceData = {
                providerId: providerId,
                serviceTitle: serviceTitle,
                providerName: providerName,
                rate: rate,
                per: per
            };
        }
        
        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }
        
        function openProviderModal(providerId, providerName, serviceTitle, serviceDescription, providerDescription, areas, category, rate, per) {
            console.log('Opening modal for:', providerName, serviceTitle);
            
            document.getElementById('modalProviderName').textContent = providerName + ' - ' + serviceTitle;
            document.getElementById('modalServiceCategory').textContent = category.charAt(0).toUpperCase() + category.slice(1);
            document.getElementById('modalServiceRate').textContent = '₹' + rate;
            document.getElementById('modalServicePer').textContent = 'per ' + per;
            document.getElementById('modalServiceDescription').textContent = serviceDescription;
            document.getElementById('modalProviderDescription').textContent = providerDescription || 'No additional information provided.';
            
            // Parse and display areas
            const areasContainer = document.getElementById('modalAreasServed');
            areasContainer.innerHTML = '';
            if (areas && areas.trim() !== '') {
                const areaList = areas.split(',').map(area => area.trim());
                areaList.forEach(area => {
                    if (area) {
                        const areaTag = document.createElement('span');
                        areaTag.className = 'area-tag';
                        areaTag.textContent = area;
                        areasContainer.appendChild(areaTag);
                    }
                });
            } else {
                areasContainer.innerHTML = '<p>No specific areas listed.</p>';
            }
            
            // Store current service data for booking
            currentServiceData = {
                providerId: providerId,
                providerName: providerName,
                serviceTitle: serviceTitle,
                rate: rate,
                per: per
            };
            
            // Update the book button to use the current service data
            document.getElementById('bookFromProviderBtn').onclick = function() {
                closeProviderModal();
                openBookingModal(
                    currentServiceData.providerId,
                    currentServiceData.serviceTitle,
                    currentServiceData.providerName,
                    currentServiceData.rate,
                    currentServiceData.per
                );
            };
            
            document.getElementById('providerModal').style.display = 'flex';
        }
        
        function closeProviderModal() {
            document.getElementById('providerModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const bookingModal = document.getElementById('bookingModal');
            const providerModal = document.getElementById('providerModal');
            
            if (event.target === bookingModal) {
                closeBookingModal();
            }
            if (event.target === providerModal) {
                closeProviderModal();
            }
        }
        
        // Set minimum time to current time for today's date
        const today = new Date().toISOString().split('T')[0];
        const dateInput = document.getElementById('date');
        const timeInput = document.getElementById('time');
        
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
        
        // Initialize time min value based on today's date
        if (dateInput.value === today) {
            const now = new Date();
            const currentTime = now.getHours().toString().padStart(2, '0') + ':' + 
                              now.getMinutes().toString().padStart(2, '0');
            timeInput.min = currentTime;
        }
        
        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const date = document.getElementById('date').value;
            const time = document.getElementById('time').value;
            
            if (!date || !time) {
                e.preventDefault();
                alert('Please fill all required fields (Date and Time).');
                return false;
            }
            
            // Check if date is in the past
            const selectedDate = new Date(date + 'T' + time);
            const now = new Date();
            
            if (selectedDate < now) {
                e.preventDefault();
                alert('Please select a future date and time.');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>