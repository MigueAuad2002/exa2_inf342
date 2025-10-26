<?php
$plain = '12345678';
$hash = password_hash($plain, PASSWORD_DEFAULT); // o PASSWORD_BCRYPT
echo $hash . PHP_EOL;