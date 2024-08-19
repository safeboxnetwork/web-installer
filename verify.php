<?php

require_once 'path/to/web3.php/src/Web3/Providers/HttpProvider.php';
require_once 'path/to/web3.php/src/Web3/RequestManagers/RequestManager.php';
require_once 'path/to/web3.php/src/Web3/Web3.php';
require_once 'path/to/web3.php/src/Web3/Eth.php';
require_once 'path/to/web3.php/src/Web3/Utils.php';

// https://github.com/sc0Vu/web3.php
// https://github.com/kornrunner/php-keccak
// https://github.com/web3p/ethereum-util 

// Include the necessary files
require_once 'path/to/Keccak.php'; // Adjust the path
require_once 'path/to/Util.php';   // Adjust the path

use kornrunner\Keccak;
use Web3p\EthereumUtil\Util;

function verifySignature($address, $signature, $message) {
    $util = new Util();
    
    // Hash the message
    $msgHash = Keccak::hash("\x19Ethereum Signed Message:\n" . strlen($message) . $message, 256);
    
    // Recover the public key from the signature
    $pubKey = $util->recoverPublicKey($msgHash, $signature);
    
    // Convert the public key to an Ethereum address
    $derivedAddress = $util->publicKeyToAddress($pubKey);
    
    // Compare the derived address with the given address
    return strtolower($address) === strtolower($derivedAddress);
}

// Fetch and decode the JSON POST request
$data = json_decode(file_get_contents('php://input'), true);
$account = $data['account'];
$signature = $data['signature'];
$message = $data['message'];

$verified = verifySignature($account, $signature, $message);

echo json_encode(['verified' => $verified]);

