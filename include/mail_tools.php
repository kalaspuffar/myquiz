<?php
require_once(__DIR__ . '/../etc/config.php');

function dkim_quote($text) {
    $output = "";
    for ($i = 0;$i < strlen($text); $i++) {
        $ord = ord($text[$i]);

        if (0x21 <= $ord && $ord <= 0x3A) {
            $output .= $text[$i];
        } else if ($ord == 0x3C) {
            $output .= $text[$i];
        } else if (0x3E <= $ord && $ord <= 0x7E) {
            $output .= $text[$i];
        } else {
            $output .= "=".sprintf("%02X",$ord);
        }
    }
    return str_replace('|','=7C', $output);
}

function sign($text) {
    global $DKIM_PRIVATE_KEY;

    if (!openssl_sign($text, $signature, $DKIM_PRIVATE_KEY, OPENSSL_ALGO_SHA256)) {    
        echo "Not able to sign.";
        return false;
    }
    return base64_encode($signature);
}

function simple_body($message) {
    while (substr($message, strlen($message) - 2, 2) == "\n\n") {
        $message = substr($message, 0, strlen($message)-1);
    }
    if (!str_ends_with($message, "\n")) {
        $message .= "\n";
    }
    return str_replace("\n", "\r\n", $message);
}

function sign_email($from, $to, $subject, $message) {
    $from = trim($from);
    $to = trim($to);
    $subject = trim($subject);
    $message = simple_body($message);
    $len = strlen($message);

    $sign_header = "v=1; a=rsa-sha256; d=myquiz.app; s=dkim;\r\n" .
        "\tc=relaxed/simple; q=dns/txt;\r\n" .
        "\tt=" . time() . "; l=$len;\r\n" .
        "\th=From:To:Subject;\r\n" .
        "\tz=" . dkim_quote('From:' . $from) . "|" . dkim_quote('To:' . $to) . "|" . dkim_quote('Subject:' . $subject) . ";\r\n".
        "\tbh=" . base64_encode(pack("H*", hash("sha256", $message))) . ";\r\n" .
        "\tb=";
    
	$text_to_sign = "from:$from\r\nto:$to\r\nsubject:$subject\r\ndkim-signature:$sign_header";
    $text_to_sign = preg_replace("/\r\n\s+/", " ", $text_to_sign) ;
	$signed_text = sign($text_to_sign);
    return 'DKIM-Signature: ' . $sign_header . $signed_text . "\r\n";
}

function send_email($to, $subject, $message) {
    
    $headers = [
        'From: no-reply@myquiz.app',
        'Reply-To: no-reply@myquiz.app',
        'X-Mailer: PHP/' . phpversion()
    ];
 
    array_push($headers, sign_email("no-reply@myquiz.app", $to, $subject, $message));

    mail($to, $subject, $message, implode("\r\n", $headers));
}