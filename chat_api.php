<?php
// chat_api.php - Handle all chat operations
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'send_message':
            sendMessage($conn);
            break;
            
        case 'get_messages':
            getMessages($conn);
            break;
            
        case 'get_user_chats':
            getUserChats($conn);
            break;
            
        case 'mark_read':
            markMessagesAsRead($conn);
            break;
            
        case 'get_unread_count':
            getUnreadCount($conn);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Chat API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Send a message
function sendMessage($conn) {
    $user_id = $_SESSION['user_id'] ?? null;
    $sender_type = $_POST['sender_type'] ?? 'user'; // 'user' or 'admin'
    $message = trim($_POST['message'] ?? '');
    
    if ($sender_type === 'admin') {
        $user_id = $_POST['user_id'] ?? null;
    }
    
    if (!$user_id || empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        return;
    }
    
    // Insert message
    $stmt = $conn->prepare("
        INSERT INTO chat_messages (user_id, sender_type, message, created_at)
        VALUES (?, ?, ?, NOW())
        RETURNING id, created_at
    ");
    $stmt->execute([$user_id, $sender_type, $message]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Update or create chat session
    $sessionStmt = $conn->prepare("
        INSERT INTO chat_sessions (user_id, last_message_at, unread_count, is_active)
        VALUES (?, NOW(), 1, TRUE)
        ON CONFLICT (user_id) 
        DO UPDATE SET 
            last_message_at = NOW(),
            unread_count = chat_sessions.unread_count + 1,
            is_active = TRUE
    ");
    $sessionStmt->execute([$user_id]);
    
    echo json_encode([
        'success' => true,
        'message_id' => $result['id'],
        'created_at' => $result['created_at']
    ]);
}

// Get messages for a user
function getMessages($conn) {
    $user_id = $_GET['user_id'] ?? $_SESSION['user_id'] ?? null;
    $limit = intval($_GET['limit'] ?? 50);
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'error' => 'User ID required']);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT 
            cm.id,
            cm.user_id,
            cm.sender_type,
            cm.message,
            cm.is_read,
            cm.created_at,
            u.name as user_name,
            u.avatar_url
        FROM chat_messages cm
        LEFT JOIN usertable u ON cm.user_id = u.id
        WHERE cm.user_id = ?
        ORDER BY cm.created_at ASC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
}

// Get all active user chats (for admin)
function getUserChats($conn) {
    $stmt = $conn->prepare("
        SELECT 
            cs.user_id,
            cs.last_message_at,
            cs.unread_count,
            u.name as user_name,
            u.email,
            u.avatar_url,
            (
                SELECT message 
                FROM chat_messages 
                WHERE user_id = cs.user_id 
                ORDER BY created_at DESC 
                LIMIT 1
            ) as last_message,
            (
                SELECT sender_type 
                FROM chat_messages 
                WHERE user_id = cs.user_id 
                ORDER BY created_at DESC 
                LIMIT 1
            ) as last_sender
        FROM chat_sessions cs
        LEFT JOIN usertable u ON cs.user_id = u.id
        WHERE cs.is_active = TRUE
        ORDER BY cs.last_message_at DESC
    ");
    $stmt->execute();
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'chats' => $chats
    ]);
}

// Mark messages as read
function markMessagesAsRead($conn) {
    $user_id = $_POST['user_id'] ?? $_SESSION['user_id'] ?? null;
    $reader_type = $_POST['reader_type'] ?? 'user'; // 'user' or 'admin'
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'error' => 'User ID required']);
        return;
    }
    
    // Mark messages as read (opposite of reader type)
    $sender_type = ($reader_type === 'admin') ? 'user' : 'admin';
    
    $stmt = $conn->prepare("
        UPDATE chat_messages 
        SET is_read = TRUE 
        WHERE user_id = ? 
        AND sender_type = ? 
        AND is_read = FALSE
    ");
    $stmt->execute([$user_id, $sender_type]);
    
    // Reset unread count in session
    if ($reader_type === 'admin') {
        $sessionStmt = $conn->prepare("
            UPDATE chat_sessions 
            SET unread_count = 0 
            WHERE user_id = ?
        ");
        $sessionStmt->execute([$user_id]);
    }
    
    echo json_encode(['success' => true]);
}

// Get unread message count
function getUnreadCount($conn) {
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        echo json_encode(['success' => true, 'count' => 0]);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM chat_messages 
        WHERE user_id = ? 
        AND sender_type = 'admin' 
        AND is_read = FALSE
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => intval($result['count'])
    ]);
}