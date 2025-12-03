<?php
// Oculta erros para produção
error_reporting(0);

// --- CONFIGURAÇÕES DO TELEGRAM ---
// Seu Token do Bot
$bot_token = '8177677770:AAEYC1O2h8ye5J4BEfA0ruZoKJjj1H3v-wc';
// Seu ID do Chat/Canal
$chat_id = '5641710847';
// ---------------------------------

// Verifica se a requisição é POST e se a mensagem está presente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    
    $message = $_POST['message'];
    
    // Formata a mensagem para o Telegram (Markdown é usado para negrito e código)
    // O objetivo é ter uma mensagem destacada no Telegram
    $texto_telegram = "✅ *NOVA APROVADA* ✅\n\n`" . $message . "`";
    
    // URL da API do Telegram
    $url = "https://api.telegram.org/bot" . $bot_token . "/sendMessage";
    
    // Dados a serem enviados
    $data = [
        'chat_id' => $chat_id,
        'text' => $texto_telegram,
        'parse_mode' => 'Markdown' // Interpreta `*` para negrito e `` ` `` para código
    ];
    
    // Inicializa o cURL para fazer a requisição HTTP
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    // Envia os dados como um array associativo, o cURL formata automaticamente
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna a resposta ao invés de imprimi-la
    
    // Executa a requisição
    $response = curl_exec($ch);
    
    // Fecha o cURL
    curl_close($ch);
    
    // Você pode logar o $response aqui para depuração
    if ($response) {
        // Envio bem-sucedido (não é necessário retornar nada para o front-end, mas é bom para feedback)
        http_response_code(200);
        echo "Mensagem enviada com sucesso.";
    } else {
        // Erro no envio
        http_response_code(500);
        echo "Erro ao enviar mensagem para o Telegram.";
    }
    
} else {
    // Caso o script seja acessado de forma incorreta
    http_response_code(400);
    echo "Método inválido ou dados ausentes.";
}
?>