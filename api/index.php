<?php
// ----------------- আপনার তথ্য -----------------
define('BOT_TOKEN', '8781583008:AAGYpzeograRiPkkBQfp3fsoMYE82YkTl6k');
define('SOURCE_GROUP_ID', -1003812433078);
define('ADMIN_ID', 8549397639);
// ----------------------------------------------

define('USER_FILE', '/tmp/users.txt');

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    echo "Bot is running on Vercel!";
    exit;
}

function load_users() {
    if (!file_exists(USER_FILE)) {
        return [];
    }
    $lines = file(USER_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return array_unique(array_map('intval', $lines));
}

function save_user($user_id) {
    $users = load_users();
    if (!in_array($user_id, $users)) {
        file_put_contents(USER_FILE, $user_id . PHP_EOL, FILE_APPEND);
    }
}

function telegramRequest($method, $data) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function sendMessage($chat_id, $text) {
    telegramRequest("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ]);
}

function copyMessage($chat_id, $from_chat_id, $message_id) {
    return telegramRequest("copyMessage", [
        'chat_id' => $chat_id,
        'from_chat_id' => $from_chat_id,
        'message_id' => $message_id
    ]);
}

if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $user_id = $message['from']['id'];
    $text = isset($message['text']) ? trim($message['text']) : '';

    if ($text === '/start') {
        save_user($user_id);
        
        $welcome_text = "স্বাগতম! আপনি বটে সফলভাবে নিবন্ধিত হয়েছেন।\n\n"
                      . "আমাদের তিনটি ভিডিও ওয়েবসাইট\n\n"
                      . "❑❑❑❑(১৯৮ টি ভিডিও)❑❑❑❑❑❑\n\n"
                      . "https://tinyurl.com/ycxc3yja\n\n"
                      . "❑❑❑❑(১০৮ টি ভিডিও)❑❑❑❑❑\n\n"
                      . "https://tinyurl.com/2texsmke\n\n"
                      . "❑❑❑❑❑(১৪৭ টি ভিডিও)❑❑❑❑❑\n\n"
                      . "https://tinyurl.com/bd8tjsb8\n\n"
                      . "এছাড়াও কিছু সময় পর পর আমাদের বটে  ভিডিও দেওয়া হবে , সবাই জয়েন থাকবেন ।";
                      
        sendMessage($chat_id, $welcome_text);
    }
    
    elseif ($text === '/stats') {
        if ($user_id == ADMIN_ID) {
            $users = load_users();
            $total_users = count($users);
            sendMessage($chat_id, "📊 আপনার বটটি মোট {$total_users} জন ইউজার স্টার্ট করেছেন।");
        } else {
            sendMessage($chat_id, "এটি দেখার অনুমতি আপনার নেই।");
        }
    }

    elseif ($chat_id == SOURCE_GROUP_ID) {
        $message_id = $message['message_id'];
        $users = load_users();
        
        foreach ($users as $u_id) {
            copyMessage($u_id, SOURCE_GROUP_ID, $message_id);
        }
    }
}
?>
