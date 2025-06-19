<?php
/**
 * Messages Page
 *
 * Allows users to view and send messages to other users.
 */

// Include database connection and authentication
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Require user to be logged in
require_login();

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Initialize variables
$current_conversation = null;
$other_user = null;
$messages = [];
$errors = [];

// Get all conversations for the user
$conversations_query = "SELECT
                            m.*,
                            CASE
                                WHEN m.sender_id = ? THEN m.receiver_id
                                ELSE m.sender_id
                            END as other_user_id,
                            CASE
                                WHEN m.sender_id = ? THEN r.name
                                ELSE s.name
                            END as other_user_name,
                            p.title as product_title,
                            p.id as product_id
                        FROM messages m
                        JOIN users s ON m.sender_id = s.id
                        JOIN users r ON m.receiver_id = r.id
                        LEFT JOIN products p ON m.product_id = p.id
                        WHERE m.sender_id = ? OR m.receiver_id = ?
                        ORDER BY m.created_at DESC";

$stmt = mysqli_prepare($conn, $conversations_query);
mysqli_stmt_bind_param($stmt, "iiii", $user_id, $user_id, $user_id, $user_id);
mysqli_stmt_execute($stmt);
$conversations_result = mysqli_stmt_get_result($stmt);

// Group conversations by other user
$grouped_conversations = [];
while ($row = mysqli_fetch_assoc($conversations_result)) {
    $other_user_id = $row['other_user_id'];

    if (!isset($grouped_conversations[$other_user_id])) {
        $grouped_conversations[$other_user_id] = [
            'user_id' => $other_user_id,
            'user_name' => $row['other_user_name'],
            'last_message' => $row,
            'unread_count' => 0
        ];
    }

    // Count unread messages
    if ($row['receiver_id'] == $user_id && $row['read_status'] == 0) {
        $grouped_conversations[$other_user_id]['unread_count']++;
    }
}

// Check if compose mode is requested
$compose_mode = isset($_GET['compose']) && $_GET['compose'] == '1';
$compose_seller_id = isset($_GET['seller_id']) ? (int)$_GET['seller_id'] : null;
$compose_product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
$compose_product = null;

// If in compose mode, get seller and product details
if ($compose_mode && $compose_seller_id) {
    $seller_query = "SELECT id, name, email FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $seller_query);
    mysqli_stmt_bind_param($stmt, "i", $compose_seller_id);
    mysqli_stmt_execute($stmt);
    $seller_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($seller_result) > 0) {
        $other_user = mysqli_fetch_assoc($seller_result);
        $current_conversation = $compose_seller_id;
        
        // Get product details if provided
        if ($compose_product_id) {
            $product_query = "SELECT id, title FROM products WHERE id = ?";
            $stmt = mysqli_prepare($conn, $product_query);
            mysqli_stmt_bind_param($stmt, "i", $compose_product_id);
            mysqli_stmt_execute($stmt);
            $product_result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($product_result) > 0) {
                $compose_product = mysqli_fetch_assoc($product_result);
            }
        }
        
        // Still get existing messages if any
        $messages_query = "SELECT m.*,
                              s.name as sender_name,
                              r.name as receiver_name,
                              p.title as product_title,
                              p.id as product_id
                          FROM messages m
                          JOIN users s ON m.sender_id = s.id
                          JOIN users r ON m.receiver_id = r.id
                          LEFT JOIN products p ON m.product_id = p.id
                          WHERE (m.sender_id = ? AND m.receiver_id = ?)
                             OR (m.sender_id = ? AND m.receiver_id = ?)
                          ORDER BY m.created_at ASC";

        $stmt = mysqli_prepare($conn, $messages_query);
        mysqli_stmt_bind_param($stmt, "iiii", $user_id, $compose_seller_id, $compose_seller_id, $user_id);
        mysqli_stmt_execute($stmt);
        $messages_result = mysqli_stmt_get_result($stmt);

        while ($message = mysqli_fetch_assoc($messages_result)) {
            $messages[] = $message;

            // Mark messages as read if they were sent to the current user
            if ($message['receiver_id'] == $user_id && $message['read_status'] == 0) {
                $update_query = "UPDATE messages SET read_status = 1 WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "i", $message['id']);
                mysqli_stmt_execute($update_stmt);
            }
        }
    }
}
// Check if a specific conversation is selected
elseif (isset($_GET['user']) && !empty($_GET['user'])) {
    $other_user_id = (int)$_GET['user'];

    // Get other user details
    $user_query = "SELECT id, name, email FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "i", $other_user_id);
    mysqli_stmt_execute($stmt);
    $user_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($user_result) > 0) {
        $other_user = mysqli_fetch_assoc($user_result);
        $current_conversation = $other_user_id;

        // Get messages between the two users
        $messages_query = "SELECT m.*,
                              s.name as sender_name,
                              r.name as receiver_name,
                              p.title as product_title,
                              p.id as product_id
                          FROM messages m
                          JOIN users s ON m.sender_id = s.id
                          JOIN users r ON m.receiver_id = r.id
                          LEFT JOIN products p ON m.product_id = p.id
                          WHERE (m.sender_id = ? AND m.receiver_id = ?)
                             OR (m.sender_id = ? AND m.receiver_id = ?)
                          ORDER BY m.created_at ASC";

        $stmt = mysqli_prepare($conn, $messages_query);
        mysqli_stmt_bind_param($stmt, "iiii", $user_id, $other_user_id, $other_user_id, $user_id);
        mysqli_stmt_execute($stmt);
        $messages_result = mysqli_stmt_get_result($stmt);

        while ($message = mysqli_fetch_assoc($messages_result)) {
            $messages[] = $message;

            // Mark messages as read if they were sent to the current user
            if ($message['receiver_id'] == $user_id && $message['read_status'] == 0) {
                $update_query = "UPDATE messages SET read_status = 1 WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "i", $message['id']);
                mysqli_stmt_execute($update_stmt);
            }
        }
    }
}

// Process new message submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $subject = sanitize_input($_POST['subject']);
    $message_text = sanitize_input($_POST['message']);
    $product_id = isset($_POST['product_id']) && !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;

    // Validate input
    if (empty($subject)) {
        $errors['subject'] = "Subject is required";
    }

    if (empty($message_text)) {
        $errors['message'] = "Message is required";
    }

    if (empty($errors)) {
        // Insert message into database
        $insert_query = "INSERT INTO messages (sender_id, receiver_id, product_id, subject, message)
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "iiiss", $user_id, $receiver_id, $product_id, $subject, $message_text);

        if (mysqli_stmt_execute($stmt)) {
            // Redirect to refresh the page and show the new message
            header("Location: messages.php?user=$receiver_id&sent=1");
            exit;
        } else {
            $errors['db'] = "Failed to send message: " . mysqli_error($conn);
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Messages</h4>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    <!-- Conversations List -->
                    <div class="col-md-4 border-end">
                        <div class="list-group list-group-flush">
                            <?php if (empty($grouped_conversations)): ?>
                                <div class="text-center p-4">
                                    <i class="fas fa-envelope-open fa-3x text-muted mb-3"></i>
                                    <p>No messages yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($grouped_conversations as $conversation): ?>
                                    <a href="?user=<?php echo $conversation['user_id']; ?>"
                                       class="list-group-item list-group-item-action <?php echo $current_conversation == $conversation['user_id'] ? 'active' : ''; ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($conversation['user_name']); ?></h5>
                                            <small><?php echo date('M d', strtotime($conversation['last_message']['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1 text-truncate">
                                            <?php if ($conversation['last_message']['product_id']): ?>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($conversation['last_message']['product_title']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (isset($conversation['last_message']['subject'])): ?>
                                                <?php echo htmlspecialchars($conversation['last_message']['subject']); ?>
                                            <?php endif; ?>
                                        </p>
                                        <small class="text-truncate d-block">
                                            <?php echo htmlspecialchars($conversation['last_message']['message']); ?>
                                        </small>
                                        <?php if ($conversation['unread_count'] > 0): ?>
                                            <span class="badge bg-danger rounded-pill"><?php echo $conversation['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="col-md-8">
                        <?php if ($current_conversation): ?>
                            <!-- Message Header -->
                            <div class="p-3 border-bottom">
                                <h5><?php echo htmlspecialchars($other_user['name']); ?></h5>
                            </div>

                            <!-- Messages -->
                            <div class="message-container">
                                <?php foreach ($messages as $message): ?>
                                    <div class="mb-3">
                                        <div class="message-bubble <?php echo $message['sender_id'] == $user_id ? 'message-sent' : 'message-received'; ?>">
                            <?php if ($message['product_id']): ?>
                                <div class="mb-1">
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($message['product_title']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                                            <?php if (isset($message['subject'])): ?>
                                                <div class="fw-bold"><?php echo htmlspecialchars($message['subject']); ?></div>
                                            <?php endif; ?>
                                            <div><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                                            <div class="text-end mt-1">
                                                <small class="text-muted">
                                                    <?php echo date('M d, g:i a', strtotime($message['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Reply Form -->
                            <div class="p-3 border-top">
                                <?php if (isset($errors['db'])): ?>
                                    <div class="alert alert-danger"><?php echo $errors['db']; ?></div>
                                <?php endif; ?>

                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?user=' . $other_user['id']); ?>">
                                    <input type="hidden" name="receiver_id" value="<?php echo $other_user['id']; ?>">
                                    <?php if ($compose_mode && $compose_product): ?>
                                        <input type="hidden" name="product_id" value="<?php echo $compose_product['id']; ?>">
                                    <?php endif; ?>

                                    <?php if ($compose_mode && $compose_product): ?>
                                        <div class="mb-3">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> 
                                                <strong>Regarding product:</strong> <?php echo htmlspecialchars($compose_product['title']); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Subject</label>
                                        <input type="text" class="form-control <?php echo isset($errors['subject']) ? 'is-invalid' : ''; ?>"
                                               id="subject" name="subject" 
                                               value="<?php echo $compose_mode && $compose_product ? 'Inquiry about: ' . htmlspecialchars($compose_product['title']) : ''; ?>" 
                                               required>
                                        <?php if (isset($errors['subject'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['subject']; ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="message" class="form-label">Message</label>
                                        <textarea class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>"
                                                  id="message" name="message" rows="3" required><?php 
                                                  if ($compose_mode && $compose_product) {
                                                      echo "Hi " . htmlspecialchars($other_user['name']) . ",\n\nI'm interested in your product \"" . htmlspecialchars($compose_product['title']) . "\" and would like to discuss it further.\n\nThanks!";
                                                  }
                                                  ?></textarea>
                                        <?php if (isset($errors['message'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['message']; ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" name="send_message" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Send Message
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-5">
                                <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                                <h4>Select a conversation</h4>
                                <p class="text-muted">Choose a conversation from the list to view messages</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.message-bubble {
    padding: 12px 16px;
    border-radius: 18px;
    margin-bottom: 8px;
    max-width: 80%;
    word-wrap: break-word;
}

.message-sent {
    background-color: #007bff;
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}

.message-received {
    background-color: #f8f9fa;
    color: #333;
    margin-right: auto;
    border-bottom-left-radius: 4px;
    border: 1px solid #e9ecef;
}

.message-sent .badge {
    background-color: rgba(255,255,255,0.2) !important;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
}

.message-container {
    height: 400px;
    overflow-y: auto;
    padding: 20px;
}

/* Scrollbar styling */
.message-container::-webkit-scrollbar {
    width: 6px;
}

.message-container::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.message-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.message-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll to bottom of messages
    const messagesContainer = document.querySelector('.message-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>
