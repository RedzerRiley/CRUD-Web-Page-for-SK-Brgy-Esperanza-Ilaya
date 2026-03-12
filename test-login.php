<?php
$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

$tests = ['password', 'admin123', 'admin', 'Password'];

foreach ($tests as $test) {
    $result = password_verify($test, $hash) ? '✅ MATCH' : '❌ No match';
    echo "$test → $result <br>";
}
?>