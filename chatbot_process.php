<?php
ob_start(); 
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
$api_url = 'http://localhost:11434/api/generate';

// ==========================================
// BƯỚC 1: BỘ LỌC THÔNG MINH BẰNG PHP (PRE-FILTERING)
// Đọc tin nhắn khách hàng và lọc Database TRƯỚC khi gửi cho AI
// ==========================================
$user_msg_lower = strtolower($user_message);
$sql_query = "SELECT products.Name, products.Price, products.MainAuthor, products.Publisher, category.CateName FROM products LEFT JOIN category ON products.CategoryId = category.CateId ";

// Mảng chứa các từ khóa thể loại để map với CategoryId trong DB của bạn
$category_keywords = [
    'action' => 'ACT', 'adventure' => 'ADV', 'comedy' => 'CMD', 
    'drama' => 'DR', 'horror' => 'HR', 'horor' => 'HR', 
    'history' => 'HS', 'novel' => 'NV', 'romance' => 'RM', 
    'school' => 'SCH', 'sci fi' => 'SF', 'sci-fi' => 'SF'
];

// Quét xem khách có nhắc đến thể loại nào không
foreach($category_keywords as $keyword => $cate_id) {
    if(strpos($user_msg_lower, $keyword) !== false) {
        // Nối thêm lệnh WHERE vào SQL giống hệt như trang search_page.php của bạn
        $sql_query .= " WHERE products.CategoryId = '$cate_id'";
        break; // Lọc 1 thể loại là đủ, thoát vòng lặp
    }
}

$available_books = "";
$query_books = mysqli_query($conn, $sql_query);

if($query_books) {
    while($row = mysqli_fetch_assoc($query_books)) {
        $cate_name = isset($row['CateName']) ? $row['CateName'] : 'Unknown';
        $author = isset($row['MainAuthor']) ? $row['MainAuthor'] : 'Unknown';
        // Chỉ gửi Tên, Thể loại và Tác giả cho AI cho nhẹ não
        $available_books .= "- " . $row['Name'] . " (Category: " . $cate_name . " | Author: " . $author . ")\n";
    }
}

// Nếu khách hỏi mà không có sách nào match trong DB
if(empty($available_books)){
    $available_books = "No books found for this specific request in the database.";
}

// ==========================================
// BƯỚC 2: THIẾT LẬP LUẬT CHƠI CHO AI
// ==========================================
$system_prompt = "You are a virtual online book assistant named 'Bookworm'. 
Here is the strict list of books you can recommend right now:
\n" . $available_books . "
CRITICAL RULE 1: You MUST ONLY recommend books from the list above. Do NOT invent books.
CRITICAL RULE 2: Write the book name naturally in your sentence (e.g., 'I recommend The Hobbit by...'). Do NOT use brackets in the middle of your sentence.
CRITICAL RULE 3: At the VERY END of your entire response, you MUST append the tag [PRODUCT: Exact Book Name] for each book you recommended.
CRITICAL RULE 4: DO NOT generate any URLs, links, or mention the price.
CRITICAL RULE 5: Stop generating text immediately after providing your answer. DO NOT simulate the customer's next reply.
Keep your response brief and friendly.";

$data = [
    "model" => "phi3",
    "prompt" => $system_prompt . "\n\nCustomer: " . $user_message . "\nAssistant:",
    "stream" => false,
    "options" => [
        // Danh sách các từ khóa mà nếu AI định gõ ra, nó sẽ bị buộc phải dừng ngay lập tức
        "stop" => ["Customer:", "Instruction:", "User:", "Assistant:"] 
    ]
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$bot_reply = $result['response'] ?? "Sorry, my local brain is sleeping. Please make sure Ollama is running!";

// ==========================================
// BƯỚC 3: HIỂN THỊ THẺ SẢN PHẨM HTML (Regex cực mạnh)
// ==========================================
if (preg_match_all('/\[\s*PRODUCT:\s*(.*?)\s*\]/i', $bot_reply, $matches)) {
    $html_cards = '<div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">';
    
    foreach ($matches[1] as $match) {
        $p_names = explode(',', $match);
        foreach ($p_names as $p_name) {
            
            $p_name = trim(preg_replace('/\(.*$/', '', $p_name)); 
            $p_name = mysqli_real_escape_string($conn, $p_name);
            
            $res = mysqli_query($conn, "SELECT * FROM products WHERE Name LIKE '%$p_name%' LIMIT 1");
            if ($res && $product_row = mysqli_fetch_assoc($res)) {
                
                $html_cards .= '
                <a href="products_details.php?product_id='.$product_row['Id'].'" style="display: flex; text-decoration: none; color: inherit; background: white; border: 1px solid #ddd; padding: 10px; border-radius: 8px; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <img src="image/'.$product_row['Image'].'" style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px; margin-right: 12px;">
                    <div style="flex: 1;">
                        <div style="font-weight: bold; font-size: 14px; color: #8e44ad;">'.$product_row['Name'].'</div>
                        <div style="color: #e74c3c; font-weight: bold;">$'.number_format($product_row['Price'], 0, ',', '.').'</div>
                    </div>
                    <div style="color: #8e44ad; font-weight: bold; font-size: 12px;">View ➔</div>
                </a>';
            }
        }
    }
    $html_cards .= '</div>';
    // Xóa thẻ [PRODUCT] ở cuối và nối HTML vào
    $bot_reply = preg_replace('/\[\s*PRODUCT:\s*(.*?)\s*\]/i', '', $bot_reply) . $html_cards;
}

ob_end_clean(); 
header('Content-Type: application/json');
echo json_encode(['reply' => $bot_reply]);
?>