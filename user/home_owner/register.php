<?php

include('../../include/conn/conn.php');
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeCare Connect - Home Owner Registration</title>
    <link rel="stylesheet" href="../../include/css/register.css">
    <script src="../../include/js/reload.js"></script>
</head>
<body>
    <div class="register-container">
        <h2>HomeCare Connect</h2>
        <h4>Home Owner Registration</h4>
        
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="fullname" class="required">Full Name</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                <div class="form-group">
                    <label for="email" class="required">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="required">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="city" class="required">City</label>
                    <input type="text" id="city" name="city" required>
                </div>
            </div>
            <div class="form-group">
                <label for="address" class="required">Address</label>
                <input type="text" id="address" name="address" required>
            </div>

            <!-- <div class="form-group">
                <label for="photo">Profile Pic</label>
                <input type="file" id="photo" name="photo">
            </div> -->

            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="required">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="required">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>

            <button type="submit" class="register-btn" name="register">Register</button>
        </form>

        <a href="../../index.php" class="login-link">Already have an account? Login here</a>
    </div>
</body>
</html>


<?php

if(isset($_POST['register']))
{
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match. Please try again.');</script>";
    } 
    else 
    {
        $checkQuery = "SELECT * FROM user WHERE email='$email'";
        $checkResult = mysqli_query($conn, $checkQuery);
        if(mysqli_num_rows($checkResult) > 0) {
            echo "<script>alert('Email already registered. Please use a different email.');</script>";
        } 
        else 
        {
            // if($photo && $photo['name'] != "")
            // {
            //     $photo_ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
            //     $allowed_ext = ['jpg', 'jpeg', 'png'];
            //     if(!in_array(strtolower($photo_ext), $allowed_ext) || $photo['size'] >= 5000000)
            //     {
            //         echo "<script>alert('Invalid photo format. Allowed formats: jpg, jpeg, png. and MAX size 5MB');</script>";
            //         exit();
            //     }
            //     $new_photo_name = $id.".". $photo_ext;
            //     move_uploaded_file($photo['tmp_name'], "../uploads/" . $new_photo_name);
            // }
            // else
            //     $new_photo_name = "default.jpg";

            $insertQuery = "INSERT INTO user (user_name, email, phone, city, address, password) VALUES ('$name', '$email', '$phone', '$city', '$address', '$password')";
            if(mysqli_query($conn, $insertQuery)) 
            {
                $_SESSION['user'] = [
                    'id' => $id,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'city' => $city,
                    'address' => $address,
                    'photo' => $new_photo_name
                ];
                echo "<script>alert('Registration successful! You can now log in.'); 
                window.location.href = './index.php';</script>";
                exit();
            } 
            else 
            {
                echo "<script>alert('Error during registration. Please try again later.');</script>";
            }
        }
    }

}

?>
