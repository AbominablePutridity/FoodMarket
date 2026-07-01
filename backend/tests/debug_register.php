<?php
$ch = curl_init("http://php:9000/api/auth/register");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(["email"=>"test@example.com","password"=>"pass123","role"=>"ROLE_USER"]),
    CURLOPT_HTTPHEADER => ["Content-Type: application/json", "Accept: application/json"],
    CURLOPT_RETURNTRANSFER => true,
]);
$result = curl_exec($ch);
echo "Result: " . ($result === false ? curl_error($ch) : $result) . "\n";
echo "HTTP: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
curl_close($ch);
