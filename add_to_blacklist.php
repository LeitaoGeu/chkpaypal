<?php
// Oculta erros para produção
error_reporting(0);

// Caminho para o arquivo da Blacklist
$blacklist_file = 'blacklist.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cc'])) {
    $cc_to_add = trim($_POST['cc']);

    // Validação básica do formato (CC|MM|AA|CVV)
    if (!preg_match('/^\d{15,16}\|\d{1,2}\|\d{2,4}\|\d{3,4}$/', $cc_to_add)) {
        http_response_code(400);
        echo "Formato de cartão inválido.";
        exit;
    }

    // Tenta obter um lock para escrita para evitar corrupção de arquivo
    $file_handle = fopen($blacklist_file, 'a');
    if ($file_handle) {
        if (flock($file_handle, LOCK_EX)) { // Tenta obter o lock exclusivo
            
            // Verifica se o cartão já está no arquivo para evitar duplicatas, 
            // embora a verificação principal deva ocorrer na API.
            $content = file_get_contents($blacklist_file);
            if (strpos($content, $cc_to_add) === false) {
                fwrite($file_handle, $cc_to_add . "\n");
                $response_message = "Cartão adicionado à Blacklist.";
            } else {
                $response_message = "Cartão já está na Blacklist.";
            }

            flock($file_handle, LOCK_UN); // Libera o lock
            http_response_code(200);
            echo $response_message;
        } else {
            http_response_code(500);
            echo "Erro: Não foi possível obter o lock do arquivo de blacklist.";
        }
        fclose($file_handle);
    } else {
        http_response_code(500);
        echo "Erro: Não foi possível abrir o arquivo de blacklist para escrita. Verifique as permissões (chmod 777).";
    }
} else {
    http_response_code(400);
    echo "Método inválido ou dados ausentes.";
}
?>