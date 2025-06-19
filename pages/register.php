<?php
/**
 * Student Marketplace Registration Page
 * 
 * Educational Purpose: Demonstrates secure user registration with input validation,
 * password hashing, and proper error handling for a 3rd-year software engineering project.
 * 
 * Key Learning Concepts:
 * - Input validation and sanitization (preventing XSS and injection attacks)
 * - Password security using PHP's built-in hashing functions
 * - Prepared statements for SQL injection prevention
 * - Client-side and server-side validation (defense in depth)
 * - User experience design with meaningful error messages
 * - Unified user model (all users can buy AND sell)
 */

// Include database connection
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Initialize variables
$name = $email = $phone = '';
$errors = [];

// Process form submission (POST request handling)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize user input
    // Note: sanitize_input() is defined in auth.php - demonstrates separation of concerns
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = $_POST['password']; // Don't sanitize passwords - may contain special chars
    $confirm_password = $_POST['confirm_password'];
    
    // SERVER-SIDE VALIDATION
    // Educational Note: Always validate on server-side even with client-side validation
    // Client-side can be bypassed, server-side cannot
    
    // Name validation with comprehensive checks
    if (empty($name)) {
        $errors['name'] = "Full name is required for account creation";
    } elseif (strlen($name) < 3) {
        $errors['name'] = "Name must be at least 3 characters long";
    } elseif (strlen($name) > 100) {
        $errors['name'] = "Name cannot exceed 100 characters";
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    } else {
        // Check if email already exists
        $check_email = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_email);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors['email'] = "Email already exists";
        }
        mysqli_stmt_close($stmt);
    }
    
    // Validate phone (optional)
    if (!empty($phone) && !preg_match("/^[0-9]{10}$/", $phone)) {
        $errors['phone'] = "Phone number must be 10 digits";
    }
    
    // Password validation with modern security requirements
    if (empty($password)) {
        $errors['password'] = "Password is required";
    } elseif (strlen($password) < 8) {
        // Educational: Modern password requirements favor length over complexity
        $errors['password'] = "Password must be at least 8 characters long";
    } elseif (strlen($password) > 255) {
        $errors['password'] = "Password is too long";
    }
    
    // Validate password confirmation
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }
    
    // If no errors, insert user into database
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Default role is 1 (regular user)
        $role = 1;
        
        // Prepare and execute the SQL statement
        // Simplified insert - no location fields (using simplified database schema)
        $insert_query = "INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $hashed_password, $phone, $role);
        
        if (mysqli_stmt_execute($stmt)) {
            // Registration successful
            set_message("Registration successful! You can now log in.", "success");
            header("Location: login.php");
            exit;
        } else {
            // Registration failed
            $errors['db'] = "Registration failed: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Create an Account</h4>
            </div>
            <div class="card-body">
                <?php if (isset($errors['db'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['db']; ?></div>
                <?php endif; ?>
                
                <form id="registerForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
                    <!-- Name Field -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                               id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Phone Field (Optional) -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number (Optional)</label>
                        <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                               id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                        <?php if (isset($errors['phone'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                        <?php endif; ?>
                        <div class="form-text">Format: 10 digits without spaces or dashes</div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               id="password" name="password" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                        <div class="form-text">Must be at least 8 characters long</div>
                    </div>
                    
                    <!-- Confirm Password Field -->
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                               id="confirm_password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Validate name
        const name = document.getElementById('name');
        if (name.value.trim() === '') {
            setInvalid(name, 'Name is required');
            isValid = false;
        } else if (name.value.trim().length < 3) {
            setInvalid(name, 'Name must be at least 3 characters');
            isValid = false;
        } else {
            setValid(name);
        }
        
        // Validate email
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email.value.trim() === '') {
            setInvalid(email, 'Email is required');
            isValid = false;
        } else if (!emailRegex.test(email.value.trim())) {
            setInvalid(email, 'Invalid email format');
            isValid = false;
        } else {
            setValid(email);
        }
        
        // Validate phone (optional)
        const phone = document.getElementById('phone');
        const phoneRegex = /^[0-9]{10}$/;
        if (phone.value.trim() !== '' && !phoneRegex.test(phone.value.trim())) {
            setInvalid(phone, 'Phone number must be 10 digits');
            isValid = false;
        } else {
            setValid(phone);
        }
        
        // Validate password
        const password = document.getElementById('password');
        if (password.value === '') {
            setInvalid(password, 'Password is required');
            isValid = false;
        } else if (password.value.length < 8) {
            setInvalid(password, 'Password must be at least 8 characters');
            isValid = false;
        } else {
            setValid(password);
        }
        
        // Validate confirm password
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword.value === '') {
            setInvalid(confirmPassword, 'Please confirm your password');
            isValid = false;
        } else if (confirmPassword.value !== password.value) {
            setInvalid(confirmPassword, 'Passwords do not match');
            isValid = false;
        } else {
            setValid(confirmPassword);
        }
        
        if (!isValid) {
            event.preventDefault();
        }
    });
    
    // Helper functions for validation
    function setInvalid(element, message) {
        element.classList.add('is-invalid');
        element.classList.remove('is-valid');
        
        let feedback = element.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.classList.add('invalid-feedback');
            element.parentNode.insertBefore(feedback, element.nextSibling);
        }
        feedback.textContent = message;
    }
    
    function setValid(element) {
        element.classList.remove('is-invalid');
        element.classList.add('is-valid');
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>
