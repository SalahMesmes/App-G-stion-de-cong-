<?php

echo "=== Génération des hash de mots de passe ===\n\n";

$passwords = [
    'Manager123!' => 'Manager',
    'Employe123!' => 'Employé'
];

foreach ($passwords as $password => $label) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "$label :\n";
    echo "  Mot de passe : $password\n";
    echo "  Hash : $hash\n\n";
}

echo "Copiez ces hash dans database/seed.sql\n";
echo "Remplacez les valeurs dans les INSERT INTO utilisateur\n";
