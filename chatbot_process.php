<?php
// Bật bộ đệm đầu ra để tránh lỗi "Network error"
ob_start(); 

// BẮT BUỘC: Kết nối database để lấy hình ảnh, giá, ID
include 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_POST['message'])) {
    ob_clean(); 
    header('Content-Type: application/json');
    echo json_encode(['reply' => 'No message received.']);
    exit;
}
$user_message = $_POST['message'];

// API KEY CỦA BẠN
$api_key = 'AIzaSyArPVTUhomlvtImGgnH0mgVkCg1op4VJw4'; 
$api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $api_key;

// ==========================================
// BƯỚC 1: LẤY DANH SÁCH SÁCH TỪ KHO ĐỂ DẠY AI
// ==========================================
$available_books = "";
$query_books = mysqli_query($conn, "SELECT Name FROM products");
if($query_books) {
    while($row = mysqli_fetch_assoc($query_books)) {
        $available_books .= "- " . $row['Name'] . "\n";
    }
}

// ==========================================
// BƯỚC 2: CẤP NHẬT LUẬT LỆ KHẮT KHE CHO AI
// ==========================================
$system_prompt = "You are a virtual online book assistant named 'Bookworm'. Your mission is to provide an excellent book shopping experience. 
CRITICAL RULE 1: You can ONLY recommend books from this exact list of available books in our store:\n" . $available_books . "
Do NOT recommend any books that are not on this list.
CRITICAL RULE 2: Whenever you recommend or mention these books, you MUST append a secret tag at the very end of your response in this exact format: [PRODUCT: Book Name]. 
If recommending multiple books, separate them by commas: [PRODUCT: Verity, Ugly Love].
Do NOT put the tag inside markdown code blocks. Briefly describe the book's genre and summary in your message.";

$data = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $system_prompt . "\n\nCustomer says: " . $user_message]
            ]
        ]
    ]
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['reply' => 'Server Connection Error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);

$result = json_decode($response, true);

if (isset($result['error'])) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['reply' => 'Google API Error: ' . $result['error']['message']]);
    exit;
}

$bot_reply = "Sorry, Bookworm is experiencing a connection issue. Please try again later.";
if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $bot_reply = $result['candidates'][0]['content']['parts'][0]['text'];
}

// ==========================================
// BƯỚC 3: QUÉT MÃ VÀ HIỂN THỊ SẢN PHẨM MẠNH MẼ HƠN
// ==========================================
// Dùng preg_match_all để bắt được mọi thẻ [PRODUCT]
if (preg_match_all('/\[PRODUCT:(.*?)\]/', $bot_reply, $matches)) {
    $html_cards = '<div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">';
    $found_any = false;
    
    foreach ($matches[1] as $match) {
        $product_names = explode(',', $match);
        
        foreach ($product_names as $p_name) {
            $p_name = mysqli_real_escape_string($conn, trim($p_name));
            
            // Tìm kiếm sản phẩm theo Name 
            $res = mysqli_query($conn, "SELECT * FROM products WHERE Name LIKE '%$p_name%' LIMIT 1");
            
            if ($res && $product_row = mysqli_fetch_assoc($res)) {
                $found_any = true;
                // Lấy đúng Id, Name, Price, Image (Viết hoa chữ cái đầu theo Database của bạn)
                $book_id = $product_row['Id']; 
                $book_name = $product_row['Name']; 
                $book_price = number_format($product_row['Price'], 0, ',', '.'); 
                $book_image = 'image/' . $product_row['Image']; 

                $html_cards .= '
                <a href="products_details.php?id='.$book_id.'" target="_blank" style="display: flex; text-decoration: none; color: inherit; background: white; border: 1px solid #ddd; padding: 10px; border-radius: 8px; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: background 0.2s; margin-bottom: 5px;">
                    <img src="'.$book_image.'" style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px; margin-right: 12px; border: 1px solid #eee;" onerror="this.src=\'image/10.jpeg\'">
                    <div style="flex: 1;">
                        <div style="font-weight: bold; font-size: 14px; color: #8e44ad; margin-bottom: 4px; line-height: 1.2;">'.$book_name.'</div>
                        <div style="color: #e74c3c; font-weight: bold; font-size: 13px;">$'.$book_price.'</div>
                    </div>
                    <div style="color: white; background-color: #8e44ad; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: bold;">View ➔</div>
                </a>';
            }
        }
    }
    $html_cards .= '</div>';
    
    // Cắt bỏ hết mấy chữ [PRODUCT:...] dư thừa ra khỏi văn bản chat
    $bot_reply = preg_replace('/\[PRODUCT:(.*?)\]/', '', $bot_reply);
    
    // Nếu quét thấy sách thật trong Database thì mới nối giao diện thẻ vào
    if($found_any) {
        $bot_reply .= $html_cards;
    }
}

// Xóa sạch bộ đệm và trả về JSON chuẩn
ob_end_clean(); 
header('Content-Type: application/json');
echo json_encode(['reply' => $bot_reply]);
?>