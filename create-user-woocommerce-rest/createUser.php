<?php
require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

$woocommerce = new Client(
    'https://example.com',
    'consumer_key',
    'consumer_secret',
    [
        'wp_api' => true,
        'version' => 'wc/v3'
    ]
);
?>


<?php
$data = [
    'email' => 'john.doe@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'username' => 'john.doe',
    'password' => 'create_password',
];

print_r($woocommerce->post('customers', $data));
?>