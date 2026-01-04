<?php
header('Content-Type: application/json');

// 1. GET USER INPUT
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Please type a message.']);
    exit;
}

// 2. CONFIGURATION (PASTE YOUR KEY HERE)
$apiKey = "AIzaSyD1oDgcXZ12_IlOpW-4AtzaRf1_6jXHPBA"; 
$apiUrl = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash-lite:generateContent?key=" . $apiKey;

// 3. PREPARE DATA FOR GEMINI
$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "You are a helpful assistant for a tourism website called EduGreenTourism in Tanjung Malim. Keep answers short and helpful. User asks: " . $userMessage]
            ]
        ]
    ]
];

// 4. SEND REQUEST TO GOOGLE
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
curl_close($ch);

// 5. PROCESS RESPONSE
$responseData = json_decode($response, true);
$reply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn't understand that.";

echo json_encode(['reply' => $reply]);
?>