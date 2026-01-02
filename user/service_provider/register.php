<?php

include '../../include/conn/conn.php';
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeCare Connect - Service Provider Registration</title>
    <link rel="stylesheet" href="../../include/css/register.css">
    <script src="../../include/js/reload.js"></script>
    <style>
        .form-group label.required::after {
            content: "*";
            color: #dc3545;
            margin-left: 4px;
        }

        .register-container {
            background: #fff;
            padding: 40px 50px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            width: 700px;
            max-width: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin: 40px auto;
            margin-top: 450px;
        }

        @media (max-width: 768px) {
            .register-container {
                width: 90%;
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>HomeCare Connect</h2>
        <h4>Service Provider Registration</h4>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="required">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email" class="required">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="required">About You</label>
                <textarea id="description" name="description" rows="3" required style="width:100%;padding:14px;border:2px solid #e9ecef;border-radius:6px;font-size:16px;transition:all 0.3s ease;box-sizing:border-box;background:#fff;"></textarea>
            </div>
            <div class="form-group">
                <label for="area" class="required">Areas to Serve</label>
                <textarea id="area" name="area" rows="2" required style="width:100%;padding:14px;border:2px solid #e9ecef;border-radius:6px;font-size:16px;transition:all 0.3s ease;box-sizing:border-box;background:#fff;"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="identity_proof" class="required">Identity Proof</label>
                    <input type="file" id="identity_proof" name="identity_proof" accept=".jpg,.jpeg,.png,.pdf" required>
                </div>
                <div class="form-group">
                    <label for="photo">Profile Pic</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category" class="required">Service Category</label>
                    <select name="category" id="category" required style="width:100%;padding:14px;border:2px solid #e9ecef;border-radius:6px;font-size:16px;transition:all 0.3s ease;box-sizing:border-box;background:#fff;">
                        <option value="" selected disabled>Select Category</option>
                        <option value="Cleaning">Cleaning</option>
                        <option value="Plumber">Plumber</option>
                        <option value="Cook">Cook</option>
                        <option value="Electrician">Electrician</option>
                        <option value="Maid">Maid</option>
                        <option value="Gardener">Gardener</option>
                        <option value="Home Appliance Repair">Home Appliance Repair</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="service_name" class="required">Service Name/Title</label>
                    <input type="text" id="service_name" name="service_name" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="service_description" class="required">Service Description</label>
                    <textarea id="service_description" name="service_description" rows="5" required style="width:100%;padding:14px;border:2px solid #e9ecef;border-radius:6px;font-size:16px;transition:all 0.3s ease;box-sizing:border-box;background:#fff;"></textarea>
                </div>
            </div>


            <div class="form-row">
                <div class="form-group">
                    <label for="rate" class="required">Rate</label>
                    <input type="number" id="rate" name="rate" min="0" required>
                </div>
                <div class="form-group">
                    <label for="rate_basis" class="required">Rate Basis</label>
                    <select id="rate_basis" name="rate_basis" required style="width:100%;padding:14px;border:2px solid #e9ecef;border-radius:6px;font-size:16px;transition:all 0.3s ease;box-sizing:border-box;background:#fff;">
                        <option value="" selected disabled>Select Basis</option>
                        <option value="hourly">Service</option>
                        <option value="hourly">Hourly</option>
                        <option value="daily">Daily</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
            </div>

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
    $name = $_POST['name'];
    $email = $_POST['email'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $service_name = mysqli_real_escape_string($conn, $_POST['service_name']);
    $service_description = mysqli_real_escape_string($conn, $_POST['service_description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $rate = mysqli_real_escape_string($conn, $_POST['rate']);
    $rate_basis = mysqli_real_escape_string($conn, $_POST['rate_basis']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $area = mysqli_real_escape_string($conn, $_POST['area']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $identity_proof = $_FILES['identity_proof'];
    $photo = isset($_FILES['photo']) ? $_FILES['photo'] : null;

    $date = date("Y-m-d");
    $time = date("H:i:s");
    $id = "SP".str_replace('-', '',$date).str_replace(':', '', $time).rand(1,99);
    if($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match. Please try again.');
         window.location.href= './register.php'; 
        </script>";
        exit();
    }
    $identity_proof_name = $id . '_' . '0_' . basename($identity_proof['name']);
    $identity_proof_target = '../uploads/'.$identity_proof_name;
    
    if(!move_uploaded_file($identity_proof['tmp_name'], $identity_proof_target)) {
        echo "<script>alert('Failed to upload identity proof. Please try again.');
          window.location.href= './register.php';
        </script>";
        exit();
    }
    $new_photo_name = null;
    if($photo && $photo['name'] != "") {
        $new_photo_name = $id. '_' . '1_' . basename($photo['name']);
        $photo_target = '../uploads/' . $new_photo_name;
        if(!move_uploaded_file($photo['tmp_name'], $photo_target)) {
            echo "<script>alert('Failed to upload profile photo. Please try again.');
             window.location.href= './register.php'; 
            </script>";
            exit();
        }
    }
    else
        $new_photo_name = 'default.jpg';

    // $insertQuery = "INSERT INTO service_providers (id, name, email, description, identity_proof, photo, service_name, service_description, rate, rate_basis, password, status) VALUES

    $insertQuery = "INSERT INTO service_provider (id, user_name, email, description, area, file, photo, title, service_description, category, rate, per, password, status) VALUES
    ('$id', '$name', '$email', '$description', '$area', '$identity_proof_name', '$new_photo_name', '$service_name', '$service_description', '$category', '$rate', '$rate_basis', '$password', 'pending')";
    if(mysqli_query($conn, $insertQuery)) {
        
        $_SESSION['user'] = [
            'user_type' => 'sp',
            'id' => $id,
            'user_name' => $name,
            'email' => $email,
            'description' => $description,
            'file' => $identity_proof_name,
            'photo' => $new_photo_name,
            'status' => 'pending',
            'title' => $service_name,
            'service_description' => $service_description,
            'category' => $category,
            'rate' => $rate,
            'per' => $rate_basis,
            'area' => $area
        ];
        
        echo "<script>alert('Registration successful! You can now log in.'); 
        window.location.href='./index.php';</script>";
        exit();
    } else {
        echo "<script>alert('Registration failed. Please try again.');
        window.location.href= './register.php'; 
        </script>";
        exit();
    }


}
?>