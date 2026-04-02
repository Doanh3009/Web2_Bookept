<?php
// Ngăn lỗi "Network error" do các khoảng trắng dư thừa
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

// ĐỊA CHỈ API CỦA OLLAMA (Local AI)
$api_url = 'http://localhost:11434/api/generate';

// ==========================================
// BƯỚC 1: LẤY TOÀN BỘ THÔNG TIN SÁCH ĐỂ "DẠY" AI
// ==========================================
$available_books = "";
$query_books = mysqli_query($conn, "SELECT products.Name, products.Price, products.MainAuthor, products.Publisher, category.CateName FROM products LEFT JOIN category ON products.CategoryId = category.CateId");

if($query_books) {
    while($row = mysqli_fetch_assoc($query_books)) {
        $cate_name = isset($row['CateName']) ? $row['CateName'] : 'Unknown';
        $author = isset($row['MainAuthor']) ? $row['MainAuthor'] : 'Unknown';
        $publisher = isset($row['Publisher']) ? $row['Publisher'] : 'Unknown';
        $price = isset($row['Price']) ? $row['Price'] : '0';
        
        // Ép AI học chuỗi dữ liệu: Tên sách (Category | Author | Publisher | Price)
        $available_books .= "- " . $row['Name'] . " (Category: " . $cate_name . " | Author: " . $author . " | Publisher: " . $publisher . " | Price: $" . $price . ")\n";
    }
}

// ==========================================
// BƯỚC 2: THIẾT LẬP LUẬT CHƠI (ÉP KHUÔN 9 THỂ LOẠI CỦA WEB)
// ==========================================
$system_prompt = "You are a virtual online book assistant named 'Bookworm'. 
CRITICAL RULE 1: You can ONLY recommend books from this exact list:
\n" . $available_books . "
CRITICAL RULE 2: The ONLY valid categories in this store are: ADVENTURE, COMEDY, DRAMA, HOROR, HISTORY, NOVEL, ROMANCE, SCHOOL, SCI FI. 
CRITICAL RULE 3: STRICT MATCHING. If the customer asks for a category, you MUST ONLY match it with books that explicitly have that exact category name in their 'Category:' field from the list. Do NOT use general English definitions (e.g., do not treat all fiction as 'NOVEL', only books explicitly marked as 'Category: NOVEL'). If a user asks for 'horror', map it to the 'HOROR' category.
CRITICAL RULE 4: Whenever you mention a book, you MUST append: [PRODUCT: Exact Book Name].
CRITICAL RULE 5: DO NOT generate, invent, or include any URLs, links, or website addresses.
CRITICAL RULE 6: DO NOT mention the price of the book in your text response.
Keep your response brief and friendly in English.";
$data = [
    "model" => "phi3",
    "prompt" => $system_prompt . "\n\nCustomer: " . $user_message,
    "stream" => false 
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
// BƯỚC 3: HIỂN THỊ SẢN PHẨM (Có chữ 'i' để quét mọi loại chữ hoa/thường)
// ==========================================
if (preg_match_all('/\[PRODUCT:(.*?)\]/i', $bot_reply, $matches)) {
    $html_cards = '<div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">';
    
    foreach ($matches[1] as $match) {
        $p_names = explode(',', $match);
        foreach ($p_names as $p_name) {
            
            // Cắt bỏ phần dư thừa nếu AI lỡ gõ thêm "(Category: ...)" vào trong thẻ
            $p_name = trim(preg_replace('/\(.*$/', '', $p_name)); 
            $p_name = mysqli_real_escape_string($conn, $p_name);
            
            // Tìm sản phẩm trong DB
            $res = mysqli_query($conn, "SELECT * FROM products WHERE Name LIKE '%$p_name%' LIMIT 1");
            if ($res && $product_row = mysqli_fetch_assoc($res)) {
                
                // VẼ GIAO DIỆN THẺ HTML (Sửa link thành product_id và bỏ target="_blank")
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
    
    // Cắt mã [Product] đi và nối thẻ HTML vào
    $bot_reply = preg_replace('/\[PRODUCT:(.*?)\]/i', '', $bot_reply) . $html_cards;
}

ob_end_clean(); 
header('Content-Type: application/json');
echo json_encode(['reply' => $bot_reply]);
?>