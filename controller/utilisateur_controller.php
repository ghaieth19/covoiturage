<?php
include_once "../config/Database.php";
include_once "../model/utilisateur_model.php";

include_once "../model/Utilisateur_view.php";       // la classe avec getters/setters

$database = new Database();
$db = $database->getConnection();

$utilisateurModel = new $utilisateurModel($db);

$action = $_GET['action'] ?? '';

if ($action === 'ajouter') {
    // Création d’un objet Utilisateur
    $utilisateur = new Utilisateur();

    $utilisateur->setNom($_POST['nom']);
    $utilisateur->setPrenom($_POST['prenom']);
    $utilisateur->setEmail($_POST['email']);
    $utilisateur->setMotDePasse($_POST['mot_de_passe']);
    $utilisateur->setTelephone($_POST['telephone']);
    $utilisateur->setPhotoPdp($_FILES['pdp']);
    $utilisateur->setPhotoVoiture($_FILES['photo_voiture']);

    // Ajout via le modèle
    $utilisateurModel->ajouterUtilisateur($utilisateur);

    header("Location: ../view/view_utilisateur.php");
    exit();
}

elseif ($action === 'supprimer') {
    $cin = $_GET['cin'] ?? null;
    if ($cin) {
        $utilisateurModel->supprimerUtilisateur($cin);
    }
    header("Location: ../view/view_utilisateur.php");
    exit();
}
?>

