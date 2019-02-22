<?php
require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

//first key: ck_92b9865fa54afc9ecb5908c236e4f4c316718753

//second key: cs_e565b9de9aaa2b4bdda1e7e4b9e7ba2d7782fda6

$woocommerce = new Client(
    'http://localhost/wordpress/',
    'ck_92b9865fa54afc9ecb5908c236e4f4c316718753',
    'cs_e565b9de9aaa2b4bdda1e7e4b9e7ba2d7782fda6',
    [
        'wp_api' => true,
        'version' => 'wc/v3'
    ]
);

createUser();

function createUser(){
    GLOBAL $woocommerce;

    echo "Starting";
    $data = [
        'email' => 'john.doe@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'username' => 'john.doe',
        'password' => 'create_password',
    ];
    
    print_r($woocommerce->post('customers', $data));
    echo "Finished";
}
?>