<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once '../../functions/ctrlSaisies.php';
require_once '../../functions/getExistPseudo.php';

session_start();

if(isset($_POST['g-recaptcha-response'])){
    $token = $_POST['g-recaptcha-response'];
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
    'secret' => '6LcWhcwqAAAAAHOwS1inexbMJLOV6mig9zXzMYGK',
    'response' => $token
    );
    $options = array(
    'http' => array (

    'header' => "Content-Type: application/x-www-form-
    urlencoded\r\n",

    'method' => 'POST',
    'content' => http_build_query($data)
    )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);
    /*
    - google response score is between 0.0 to 1.0
    - if score is 0.5, it's a human
    - if score is 0.0, it's a bot
    - google recommend to use score 0.5 for verify human
    */

    /*
    if(!($response["success"] ?? false)) {
        var_dump("mal");
        exit();
    } else {
        var_dump("bien");
        exit;
    }
    if ($response->success && $response->score >= 0.5) {
        //Le test est réussi, on peut inscrire la personne si le pseudo et le mot de passe sont bons
        var_dump(array('success' => true, "msg"=>"You are not a robot!",
        "response"=>$response));
    }else{
        var_dump(array('success' => false, "msg"=>"You are a robot!",
        "response"=>$response));
    }
    */
}

// Vérification AJAX pour le pseudo
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['pseudo'])) {
    $pseudo = ctrlSaisies($_GET['pseudo']);
    $exists = get_ExistPseudo($pseudo) > 0;
    echo json_encode(['exists' => $exists]);
    exit; // Stopper le script après avoir renvoyé la réponse AJAX
}

// Vérification que la requête est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Méthode non autorisée.");
}

$error = false;

// Vérification Nom et Prénom
if (empty($_POST['nomMemb']) || empty($_POST['prenomMemb'])) {
    die("Les champs nom et prénom sont obligatoires.");
}
$nom = ctrlSaisies($_POST['nomMemb']);
$prenom = ctrlSaisies($_POST['prenomMemb']);

// Vérification Pseudo
if (empty($_POST['pseudoMemb'])) {
    die("Le champ pseudo est obligatoire.");
}
$pseudo = ctrlSaisies($_POST['pseudoMemb']);
if (strlen($pseudo) < 6 || strlen($pseudo) > 70) {
    die("Le pseudo doit être compris entre 6 et 70 caractères.");
}
if (get_ExistPseudo($pseudo) > 0) {
    die("Le pseudo existe déjà.");
}

// Vérification Email
if (empty($_POST['email1']) || empty($_POST['email2'])) {
    die("Veuillez saisir des adresses email.");
}
$email1 = ctrlSaisies($_POST['email1']);
$email2 = ctrlSaisies($_POST['email2']);
$patternMail = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
if (!preg_match($patternMail, $email1) || !preg_match($patternMail, $email2)) {
    die("Veuillez saisir des adresses email valides.");
}
if ($email1 !== $email2) {
    die("Les adresses email ne correspondent pas.");
}

// Vérification Mot de passe
if (empty($_POST['passMemb1']) || empty($_POST['passMemb2'])) {
    die("Veuillez saisir un mot de passe.");
}
$mdp1 = $_POST['passMemb1'];
$mdp2 = $_POST['passMemb2'];
$patternMdp = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,15}$/';
if (!preg_match($patternMdp, $mdp1)) {
    die("Le mot de passe doit contenir entre 8 et 15 caractères, avec au moins une majuscule, une minuscule, un chiffre et un caractère spécial.");
}
if ($mdp1 !== $mdp2) {
    die("Les mots de passe ne correspondent pas.");
}
$mdpHash = password_hash($mdp1, PASSWORD_DEFAULT);

// Vérification Accord
if (!isset($_POST['accordMemb']) || $_POST['accordMemb'] !== '1') {
    die("Vous devez accepter que vos données soient conservées pour créer un compte.");
}

// Vérification Statut
if (!isset($_POST['numStat']) || !is_numeric($_POST['numStat'])) {
    die("Le statut du profil est invalide.");
}
$numStat = (int) $_POST['numStat'];

// Insertion dans la base de données
sql_insert('MEMBRE', 'nomMemb, prenomMemb, pseudoMemb, passMemb, eMailMemb, accordMemb, numStat', "'$nom', '$prenom', '$pseudo', '$mdpHash', '$email1', 1, '$numStat'");

// Création de la session
$_SESSION['logged_in'] = true;
$_SESSION['username'] = $pseudo;


header('Location: ../../views/backend/members/list.php');
exit();
?>
