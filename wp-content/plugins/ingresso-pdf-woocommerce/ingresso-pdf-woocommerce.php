<?php

/**
 * Plugin Name: Ingresso PDF para WooCommerce
 * Description: Gera automaticamente um ingresso em PDF após o pagamento do pedido.
 * Version: 1.0
 * Author: Gabriel Poeta
 */

if (!defined('ABSPATH')) exit;

add_action('woocommerce_order_status_completed', 'gerar_ingresso_pdf', 10, 1);

// Carrega DomPDF
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function gerar_ingresso_pdf($order_id)
{
    $order = wc_get_order($order_id);

    if (!$order) return;

    $nome = $order->get_billing_first_name();
    $evento = '';
    $pagamento = $order->get_payment_method_title();

    foreach ($order->get_items() as $item) {
        $evento = $item->get_name();
        break;
    }

    // $html = "
    //     <h1>Ingresso para: {$evento}</h1>
    //     <p>Nome: {$nome}</p>
    //     <p>Método de pagamento: {$pagamento}</p>
    //     <p>Pedido: {$order_id}</p>
    //         ";


    $options = new Options;
    $options->setChroot(__DIR__);
    $dompdf = new Dompdf($options);

    $dompdf->setPaper('A4', 'portrait');

    $html = file_get_contents(__DIR__ . "/template.html");
    $html = str_replace(
        ["{{ evento }}", "{{ nome }}", "{{ pagamento }}", "{{ order_id }}"],
        [$evento, $nome, $pagamento, $order_id],
        $html
    );
    $dompdf->loadHtml($html);
    $dompdf->render();

    // Salvar em arquivo
    $upload_dir = wp_upload_dir();
    $pdf_path = $upload_dir['basedir'] . "/ingresso_{$order_id}.pdf";
    file_put_contents($pdf_path, $dompdf->output());

    // Enviar por email
    $to = $order->get_billing_email();
    $subject = 'Seu ingresso';
    $message = 'Segue em anexo seu ingresso para o evento';
    $headers = array('Content-type: text/html; charset=UTF-8');

    wp_mail($to, $subject, $message, $headers, array($pdf_path));
}
