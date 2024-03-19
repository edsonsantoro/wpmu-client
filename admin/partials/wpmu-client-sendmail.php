<?php
// Verifica se o método de requisição é POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica se a função mail() está disponível
    if (!function_exists('mail')) {
        http_response_code(500);
        echo "Erro: A função mail() não está disponível neste servidor.";
        exit;
    }

    // Verifica se há dados no corpo da requisição POST
    $dados_formulario = $_POST;
    if (empty($dados_formulario)) {
        http_response_code(400);
        echo "Erro: Nenhum dado foi enviado no corpo da requisição.";
        exit;
    }

    // Verifica se o endereço de e-mail do destinatário está definido
    $destinatario = "destinatario@example.com";
    if (empty($destinatario)) {
        http_response_code(500);
        echo "Erro: Endereço de e-mail do destinatário não definido.";
        exit;
    }

    // Constrói o corpo do e-mail
    $corpo_email = "";
    foreach ($dados_formulario as $campo => $valor) {
        $corpo_email .= ucfirst($campo) . ": " . htmlspecialchars($valor) . "\n";
    }

    // Assunto padrão
    $assunto = "Formulário de Contato";

    // Envia o e-mail
    $envio_email = mail($destinatario, $assunto, $corpo_email);
    if ($envio_email) {
        http_response_code(200);
        echo "E-mail enviado com sucesso!";
    } else {
        http_response_code(500);
        echo "Erro ao enviar o e-mail.";
    }
} else {
    // Se o método de requisição não for POST, retorna um erro 405 (Method Not Allowed)
    http_response_code(405);
    echo "Este script só pode ser acessado via método POST.";
}

