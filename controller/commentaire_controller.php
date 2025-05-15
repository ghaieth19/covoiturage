<?php
require_once 'CommentaireModel.php';  // Inclusion du modèle pour les commentaires
require_once 'Database.php';  // Inclusion de la connexion PDO

class CommentaireController {
    private $commentaireModel;

    // Constructeur pour initialiser le modèle de commentaire
    public function __construct($pdo) {
        $this->commentaireModel = new CommentaireModel($pdo);
    }

    // Afficher les commentaires pour une publication
    public function afficherCommentaires($publication_id) {
        $commentaires = $this->commentaireModel->getCommentaires($publication_id);
        return $commentaires;
    }

    // Ajouter un commentaire
    public function ajouterCommentaire($publication_id, $utilisateur_cin, $commentaire) {
        // Vérifier si la publication existe
        if (!$this->commentaireModel->verifierPublicationExistante($publication_id)) {
            return "La publication n'existe pas.";
        }

        // Ajouter le commentaire
        $resultat = $this->commentaireModel->ajouterCommentaire($publication_id, $utilisateur_cin, $commentaire);
        return $resultat ? "Commentaire ajouté avec succès." : "Erreur lors de l'ajout du commentaire.";
    }

    // Modifier un commentaire
    public function modifierCommentaire($id, $commentaire) {
        $resultat = $this->commentaireModel->modifierCommentaire($id, $commentaire);
        return $resultat ? "Commentaire modifié avec succès." : "Erreur lors de la modification du commentaire.";
    }

    // Supprimer un commentaire
    public function supprimerCommentaire($id) {
        $resultat = $this->commentaireModel->supprimerCommentaire($id);
        return $resultat ? "Commentaire supprimé avec succès." : "Erreur lors de la suppression du commentaire.";
    }
}
?>
