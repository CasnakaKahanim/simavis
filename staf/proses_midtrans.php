<?php
session_start();
include '../koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap   = trim($_POST['nama_lengkap'] ?? 'Pelanggan Walk-in');
    $no_whatsapp    = trim($_POST['no_whatsapp'] ?? '081234567890');
    $email          = trim($_POST['email'] ?? 'customer@simavis.com');
    $nama_paket     = trim($_POST['nama_paket'] ?? 'Paket Foto');
    $total_harga    = intval($_POST['total_harga'] ?? 0);
    $order_id       = 'SIM-' . date('ymd') . '-' . rand(100, 999);

    if ($total_harga <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Total harga tidak valid.']);
        exit;
    }

    // Pastikan Server Key terdefinisi dari koneksi.php
    $server_key = defined('MIDTRANS_SERVER_KEY') ? MIDTRANS_SERVER_KEY : '';

    if (empty($server_key) || strpos($server_key, 'xxxx') !== false) {
        echo json_encode(['status' => 'error', 'message' => 'Server Key Midtrans belum diisi di koneksi.php']);
        exit;
    }

    // Payload Transaksi Midtrans
    $params = [
        'transaction_details' => [
            'order_id'     => $order_id,
            'gross_amount' => $total_harga,
        ],
        'customer_details' => [
            'first_name' => $nama_lengkap,
            'email'      => $email,
            'phone'      => $no_whatsapp,
        ],
        'item_details' => [
            [
                'id'       => 'PAKET-STUDIO',
                'price'    => $total_harga,
                'quantity' => 1,
                'name'     => substr($nama_paket, 0, 50),
            ]
        ]
    ];

    // Endpoint Midtrans
    $is_production = defined('MIDTRANS_IS_PRODUCTION') ? MIDTRANS_IS_PRODUCTION : false;
    $endpoint = $is_production 
        ? 'https://app.midtrans.com/snap/v1/transactions' 
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Mematikan validasi SSL agar tidak error di XAMPP Localhost
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($server_key . ':')
    ]);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'cURL Error: ' . $curl_error
        ]);
        exit;
    }

    $result = json_decode($response, true);

    if (isset($result['token'])) {
        echo json_encode([
            'status'     => 'success',
            'snap_token' => $result['token'],
            'order_id'   => $order_id
        ]);
    } else {
        $msg = isset($result['error_messages']) ? implode(', ', $result['error_messages']) : 'Gagal memproses Midtrans Token';
        echo json_encode([
            'status'  => 'error',
            'message' => $msg
        ]);
    }
}
?>