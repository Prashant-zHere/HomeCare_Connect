<?php

include './include/conn/conn.php';
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HomeCare Connect - Login</title>
    <link rel="stylesheet" href="./include/css/login.css">

    <script src="./include/js/reload.js"></script>

</head>
<body>
    <div class="login-container">
        <h2>HomeCare Connect</h2>
        <h4>Connecting Care. Empowering Homes.</h4>
        <?php if(isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="user-type">
                <label class="radio-label">
                    <input type="radio" name="user_type" value="admin" required>
                    <span class="radio-text">Admin</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="user_type" value="sp" required>
                    <span class="radio-text">Service Provider</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="user_type" value="user" required>
                    <span class="radio-text">Home Owner</span>
                </label>
            </div>
            
            <div class="form-group">
                <input type="text" id="id" placeholder="Enter Email address or ID" name="id" required>
            </div>
            
            <div class="form-group">
                <input type="password" id="password" placeholder="Enter Password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn" name="login">
                Sign in
            </button>
        </form>
        
        <a href="./user/service_provider/register.php" class="forgot-password">Register as Service Provider</a>
        <a href="./user/home_owner/register.php" class="forgot-password">Register as Home Owner</a>
    </div>
</body>
</html>


<?php

function showError() {
    echo "<script>alert('Invalid Admin ID/Email or Password.');</script>";
}

if(isset($_POST['login']))
{
    $user_type = $_POST['user_type'];
    $id = $_POST['id'];
    $password = $_POST['password'];

    // echo $user_type;

    if($user_type == 'admin') 
    {
        $query = "select * from admin WHERE (email='$id' OR id='$id') and password='$password'";
        $result = mysqli_query($conn, $query);
        if(mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['user'] = $row;
            header("Location: ./user/admin/");
            exit();
        } 
        else 
            showError();            
    } 
    else if($user_type == 'sp') 
    {
        $query = "select * from service_provider where (email='$id' or id='$id') and password='$password'";
        $result = mysqli_query($conn, $query);
        if(mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['user'] = $row;
            header("Location: ./user/service_provider/");
            exit();
        } 
        else 
            showError();  
    } 
    elseif($user_type == 'user') 
    {
        $query = "select * FROM user WHERE (email='$id' OR id='$id') and password='$password'";
        $result = mysqli_query($conn, $query);
        if(mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['user'] = $row;
            header("Location: ./user/home_owner/");
            exit();
        } 
        else 
            showError();  
    } 
    else 
    {
        echo "<script>alert('Please select a valid user type..');</script>";
    }
}

?>