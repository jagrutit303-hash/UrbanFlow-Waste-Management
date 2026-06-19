<?php
include("config.php");
$conn->query("UPDATE users SET role = 'admin' WHERE email = 'admin@urbanflow.com'");
echo "Admin fixed\n";
