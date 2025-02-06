<?php
session_start();
include "./database.php";

// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$current_user = $_SESSION['user_id'];

// ----------------------------------------------
// 1. Get distinct conversation partners for current user
// ----------------------------------------------
// This query gets all user IDs who have either sent or received a message with the current user.
$partnersQuery = "
    SELECT DISTINCT partner_id FROM (
        SELECT receiver_id AS partner_id 
        FROM messages 
        WHERE sender_id = $current_user
        UNION
        SELECT sender_id AS partner_id 
        FROM messages 
        WHERE receiver_id = $current_user
    ) AS conv
    WHERE partner_id != $current_user
";
$partnersResult = pg_query($db, $partnersQuery);

// Build an array of conversation partners (id and username)
$partners = array();
while ($row = pg_fetch_assoc($partnersResult)) {
    $partner_id = $row['partner_id'];
    // Get partner details from the users table
    $userQuery = "SELECT id, username FROM users WHERE id = $partner_id";
    $userResult = pg_query($db, $userQuery);
    if ($userRow = pg_fetch_assoc($userResult)) {
        $partners[] = $userRow;
    }
}

// ----------------------------------------------
// 2. Determine the conversation partner (if any)
// ----------------------------------------------
// Use a GET parameter 'with' to specify conversation partner ID.
// If not provided, use the first conversation partner in the list (if exists)
if (isset($_GET['with'])) {
    $conversationPartnerId = (int)$_GET['with'];
} elseif (!empty($partners)) {
    $conversationPartnerId = $partners[0]['id'];
} else {
    $conversationPartnerId = null;
}

// ----------------------------------------------
// 3. Fetch the conversation messages (if a partner is selected)
// ----------------------------------------------
$messages = array();
if ($conversationPartnerId) {
    // Get all messages between the current user and the selected partner
    $convQuery = "
        SELECT m.*, 
               (CASE 
                    WHEN m.sender_id = $current_user THEN 'You' 
                    ELSE u.username 
                END) AS sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = $current_user AND m.receiver_id = $conversationPartnerId)
           OR (m.sender_id = $conversationPartnerId AND m.receiver_id = $current_user)
        ORDER BY m.created_at ASC
    ";
    $convResult = pg_query($db, $convQuery);
    while ($row = pg_fetch_assoc($convResult)) {
        $messages[] = $row;
    }
}

// ----------------------------------------------
// 4. Handle sending a new message in the conversation
// ----------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['message']) && isset($_POST['partner_id'])) {
    $partner_id = (int)$_POST['partner_id'];
    $messageText = pg_escape_string($db, $_POST['message']);

    $insertQuery = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ($current_user, $partner_id, '$messageText')";
    $insertResult = pg_query($db, $insertQuery);

    // Redirect back to this conversation (to avoid form re-submission)
    header("Location: messages.php?with=$partner_id");
    exit;
}

// ----------------------------------------------
// 5. Prepare a dropdown of all users (for starting a new conversation)
// ----------------------------------------------
// We'll select all users except the current user
$allUsersQuery = "SELECT id, username FROM users WHERE id != $current_user ORDER BY username";
$allUsersResult = pg_query($db, $allUsersQuery);
$allUsers = array();
while ($row = pg_fetch_assoc($allUsersResult)) {
    $allUsers[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Messages</title>
    <link rel="stylesheet" href="css/messages.css">
    <!-- <style>
        /* Simple CSS for layout */
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            width: 100%;
        }

        .sidebar {
            width: 25%;
            border-right: 1px solid #ccc;
            padding: 10px;
        }

        .conversation {
            width: 75%;
            padding: 10px;
        }

        .message {
            margin: 5px 0;
            padding: 5px;
            border-bottom: 1px solid #eee;
        }

        .message span {
            font-weight: bold;
        }
    </style> -->
</head>

<body>
    <h2>Messages</h2>
    <div class="container">
        <!-- Sidebar: List of conversation partners -->
        <div class="sidebar">
            <h3>Conversations</h3>
            <?php if (!empty($partners)): ?>
                <ul>
                    <?php foreach ($partners as $partner): ?>
                        <li>
                            <a href="messages.php?with=<?php echo $partner['id']; ?>">
                                <?php echo htmlspecialchars($partner['username']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No conversations yet.</p>
            <?php endif; ?>

            <!-- Start a new conversation -->
            <h4>Start New Conversation</h4>
            <form method="GET" action="messages.php">
                <select name="with" required>
                    <option value="">Select a user</option>
                    <?php foreach ($allUsers as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Go</button>
            </form>
        </div>

        <!-- Conversation Panel -->
        <div class="conversation">
            <?php if ($conversationPartnerId):
                // Get conversation partner's username for display
                $partnerQuery = "SELECT username FROM users WHERE id = $conversationPartnerId";
                $partnerResult = pg_query($db, $partnerQuery);
                $partnerInfo = pg_fetch_assoc($partnerResult);
            ?>
                <h3>Conversation with <?php echo htmlspecialchars($partnerInfo['username']); ?></h3>
                <div class="chat-box">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message">
                                <span><?php echo htmlspecialchars($msg['sender_name']); ?>:</span>
                                <?php echo htmlspecialchars($msg['message']); ?>
                                <br><small><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No messages in this conversation yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Form to send a new message -->
                <form method="POST">
                    <input type="hidden" name="partner_id" value="<?php echo $conversationPartnerId; ?>">
                    <textarea name="message" placeholder="Type your message..." required style="width: 100%;"></textarea>
                    <button type="submit">Send</button>
                </form>
            <?php else: ?>
                <p>Select a conversation from the left or start a new one.</p>
            <?php endif; ?>
        </div>
    </div>
    <a href="dashboard.php">Back to Dashboard</a>
</body>

</html>