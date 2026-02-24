<?php
$users = [];
if (file_exists("/tmp/users.json")) {
    $users = json_decode(file_get_contents("/tmp/users.json"), true);
}

$newUser = [
    "id" => count($users) + 1,
    "email" => "admin@mini-erp.com",
    "password" => password_hash("admin123", PASSWORD_DEFAULT),
    "name" => "Admin User",
    "created_at" => date("Y-m-d H:i:s")
];

$users[] = $newUser;
file_put_contents("/tmp/users.json", json_encode($users, JSON_PRETTY_PRINT));
echo "User created successfully\n";
