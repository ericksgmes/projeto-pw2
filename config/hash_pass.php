<?php

$senhas = ['admin', 'senha456', 'senha789'];
$hashes = [];

foreach ($senhas as $senha) {
    $hashes[] = password_hash($senha, PASSWORD_BCRYPT);
}

foreach ($hashes as $index => $hash) {
    echo "Hash para senha" . ($index + 1) . ": " . $hash . PHP_EOL;
}
