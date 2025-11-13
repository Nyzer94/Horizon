<?php
// brevo-subscribe.php
// ⚠️ Place ce fichier sur ton serveur et protège-le avec .htaccess si nécessaire

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Remplace * par ton domaine en production
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit();
}

// ⚠️ REMPLACE CETTE CLÉ PAR LA TIENNE (stocke-la dans un fichier config séparé en production)
$BREVO_API_KEY = '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';
$BREVO_LIST_ID = 2; // Remplace par l'ID de ta liste Brevo

// Récupérer les données du formulaire
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email']) || !isset($input['prenom'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données manquantes']);
    exit();
}

$email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
$prenom = trim($input['prenom']);

if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Email invalide']);
    exit();
}

// Préparer les données pour Brevo
$data = [
    'email' => $email,
    'attributes' => [
        'PRENOM' => $prenom
    ],
    'listIds' => [$BREVO_LIST_ID],
    'updateEnabled' => true
];

// Appel à l'API Brevo
$ch = curl_init('https://api.brevo.com/v3/contacts');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'accept: application/json',
        'api-key: ' . $BREVO_API_KEY,
        'content-type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Gérer la réponse
if ($httpCode === 201 || $httpCode === 204) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Inscription réussie']);
} else {
    http_response_code($httpCode);
    echo json_encode(['error' => 'Erreur lors de l\'inscription', 'details' => json_decode($response, true)]);
}
?>