<?php
/**
 * User Login Page
 *
 * Allows registered users to log in with their email and password.
 */

// Include database connection and authentication functions
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Initialize variables
$email = '';
$errors = [];

// Check if user is already logged in
if (is_logged_in()) {
    // Redirect to home page
    header("Location: ../index.php");
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    // Validate email
    if (empty($email)) {
        $errors['email'] = "Email is required";
    }

    // Validate password
    if (empty($password)) {
        $errors['password'] = "Password is required";
    }

    // If no validation errors, attempt to log in
    if (empty($errors)) {
        // Prepare SQL statement to get user by email
        $query = "SELECT id, name, email, password, role FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Verify password - support both hashed and plain text for testing
            $password_valid = false;
            if (password_verify($password, $user['password'])) {
                $password_valid = true; // Hashed password match
            } elseif ($password === $user['password']) {
                $password_valid = true; // Plain text password match (for testing)
            }
            
            if ($password_valid) {
                // Password is correct, start a new session
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // Set success message
                $_SESSION['message'] = "Welcome back, " . $user['name'] . "!";
                $_SESSION['message_type'] = "success";

                // Check if there's a redirect URL stored in session
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: $redirect");
                } else {
                    // Redirect based on user role
                    if ($_SESSION['user_role'] == 3) {
                        // Admin user - redirect to admin dashboard
                        header("Location: ../admin/dashboard.php");
                    } else {
                        // Regular user or seller - redirect to home page
                        header("Location: ../index.php");
                    }
                }
                exit;
            } else {
                // Password is incorrect
                $errors['login'] = "Invalid email or password";
            }
        } else {
            // User not found
            $errors['login'] = "Invalid email or password";
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
                <h4 class="mb-0">Login to Your Account</h4>
            </div>
            <div class="card-body">
                <?php if (isset($errors['login'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['login']; ?></div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                               id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                               id="password" name="password" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <div class="mb-2">Don't have an account? <a href="register.php">Register here</a></div>
                <!-- <div><a href="forgot_password.php">Forgot your password?</a></div> -->
            </div>
        </div>
    </div>
</div>

<script>
// Client-side validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');

    form.addEventListener('submit', function(event) {
        let isValid = true;

        // Validate email
        const email = document.getElementById('email');
        if (email.value.trim() === '') {
            setInvalid(email, 'Email is required');
            isValid = false;
        } else {
            setValid(email);
        }

        // Validate password
        const password = document.getElementById('password');
        if (password.value === '') {
            setInvalid(password, 'Password is required');
            isValid = false;
        } else {
            setValid(password);
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
