<?php
header('Content-Type: application/json');
require_once('includes/env_loader.php');
loadEnv(__DIR__ . '/.env');

// Credentials from .env
$token = $_ENV['HF_API_TOKEN'] ?? '';
$model = $_ENV['HF_MODEL'] ?? 'openai/gpt-oss-120b:groq';
$base_url = $_ENV['HF_BASE_URL'] ?? 'https://router.huggingface.co/v1';

$input_data = json_decode(file_get_contents('php://input'), true);
$user_message = $input_data['message'] ?? '';

if (empty($user_message)) {
    echo json_encode(['response' => "I'm Urban! Ready to help with your waste management questions. ♻️"]);
    exit();
}

// Hugging Face Router Chat Completions Endpoint
$api_url = rtrim($base_url, '/') . '/chat/completions';

$payload = [
    "model" => $model,
    "messages" => [
        ["role" => "system", "content" => "You are 'Urban', the witty digital mascot for UrbanFlow Smart City. You help with waste management and recycling. Keep responses under 3 sentences."],
        ["role" => "user", "content" => $user_message]
    ],
    "max_tokens" => 200,
    "temperature" => 0.7
];

$models = array_filter([$model, 'meta-llama/Llama-3.1-8B-Instruct', 'mistralai/Mistral-7B-Instruct-v0.3', 'microsoft/Phi-3-mini-4k-instruct']);
$bot_response = "";
$success = false;

foreach ($models as $current_model) {
    $payload["model"] = $current_model;
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . trim($token)
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Shorter timeout for each attempt
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_code === 200 && isset($result['choices'][0]['message']['content'])) {
        $bot_response = trim($result['choices'][0]['message']['content']);
        $success = true;
        break;
    } else {
        $error_msg = $result['error']['message'] ?? ($result['error'] ?? 'Unknown Error');
        file_put_contents('chatbot_debug.log', date('Y-m-d H:i:s') . " - Model: $current_model - Code: $http_code - Error: " . json_encode($error_msg) . "\n", FILE_APPEND);
    }
}

if (!$success) {
    $bot_response = "I'm currently optimizing my waste collection routes! 🚛 Please give me a second to reconnect with the cloud. ♻️";
}


echo json_encode(['response' => $bot_response]);
?>