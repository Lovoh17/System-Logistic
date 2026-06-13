<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EmailJSService
{
    protected $publicKey;

    protected $serviceId;

    protected $templateId;

    public function __construct()
    {
        $this->publicKey = config('app.public_key');
        $this->serviceId = config('app.service_id');
        $this->templateId = config('app.template_id');
    }

    public function sendReceipt($toEmail, $toName, $data)
    {
        try {
            if (empty($toEmail)) {
                Log::error('Email de destino vacío');

                return false;
            }

            // Construir los parámetros exactamente como en tu Vue.js
            $templateParams = [
                'to_email' => $toEmail,
                'to_name' => $toName,
                'order_id' => $data['numero'],
                'order_date' => $data['fecha'],
                'cliente_nombre' => $data['cliente_nombre'],
                'store_location' => $data['sucursal'],
                'items_table' => $this->formatItemsHtml($data['items']),
                'subtotal' => number_format($data['subtotal'], 2),
                'tax' => number_format($data['impuesto'], 2),
                'total' => number_format($data['total'], 2),
                'payment_method' => $data['metodo_pago'] ?? 'Efectivo',
                'store_phone' => '2222-3333',
                'store_email' => 'ventas@agroalvarado.com',
            ];

            $postData = [
                'service_id' => $this->serviceId,
                'template_id' => $this->templateId,
                'user_id' => $this->publicKey,
                'template_params' => $templateParams,
            ];

            $ch = curl_init('https://api.emailjs.com/api/v1.0/email/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            Log::info('EmailJS Response:', [
                'code' => $httpCode,
                'response' => $response,
                'to_email' => $toEmail,
            ]);

            if ($httpCode === 200) {
                Log::info('Email enviado correctamente', ['pedido' => $data['numero']]);

                return true;
            }

            Log::error('Error al enviar email', [
                'pedido' => $data['numero'],
                'response' => $response,
                'code' => $httpCode,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Excepción al enviar email', [
                'pedido' => $data['numero'],
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function formatItemsHtml($items)
    {
        if (empty($items)) {
            return '<p>No hay productos</p>';
        }

        $html = '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr style="background-color: #f3f4f6;">';
        $html .= '<th style="padding: 8px; text-align: left;">Producto</th>';
        $html .= '<th style="padding: 8px; text-align: center;">Cantidad</th>';
        $html .= '<th style="padding: 8px; text-align: right;">Precio</th>';
        $html .= '<th style="padding: 8px; text-align: right;">Subtotal</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">'.htmlspecialchars($item['nombre']).'</td>';
            $html .= '<td style="padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb;">'.$item['cantidad'].'</td>';
            $html .= '<td style="padding: 8px; text-align: right; border-bottom: 1px solid #e5e7eb;">$'.number_format($item['precio'], 2).'</td>';
            $html .= '<td style="padding: 8px; text-align: right; border-bottom: 1px solid #e5e7eb;">$'.number_format($item['subtotal'], 2).'</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
