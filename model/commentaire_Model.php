<?php
class CommentaireModel {
    private $pdo;
    private $publication_id;
    private $utilisateur_cin;
    private $commentaire;
    private $id;

    // Constructeur pour initialiser la connexion PDO
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Getters
    public function getPdo() {
        return $this->pdo;
    }

    public function getPublicationId() {
        return $this->publication_id;
    }

    public function getUtilisateurCin() {
        return $this->utilisateur_cin;
    }

    public function getCommentaire() {
        return $this->commentaire;
    }

    public function getId() {
        return $this->id;
    }

    // Setters
    public function setPdo($pdo) {
        $this->pdo = $pdo;
    }

    public function setPublicationId($publication_id) {
        $this->publication_id = $publication_id;
    }

    public function setUtilisateurCin($utilisateur_cin) {
        $this->utilisateur_cin = $utilisateur_cin;
    }

    public function setCommentaire($commentaire) {
        $this->commentaire = $commentaire;
    }

    public function setId($id) {
        $this->id = $id;
    }
    
    // Ajouter un commentaire
    public function ajouterCommentaire($publication_id, $utilisateur_cin, $commentaire) {
        // Vérification si la publication existe
        if (!$this->verifierPublicationExistante($publication_id)) {
            return false; // La publication n'existe pas
        }

        // Assignation des paramètres à l'objet
        $this->setPublicationId($publication_id);
        $this->setUtilisateurCin($utilisateur_cin);
        $this->setCommentaire($commentaire);

        $query = "INSERT INTO commentaires (publication_id, utilisateur_cin, commentaire) VALUES (:publication_id, :utilisateur_cin, :commentaire)";
        $stmt = $this->getPdo()->prepare($query);
        $stmt->bindParam(':publication_id', $this->getPublicationId());
        $stmt->bindParam(':utilisateur_cin', $this->getUtilisateurCin());
        $stmt->bindParam(':commentaire', $this->getCommentaire());
        return $stmt->execute();
    }

    // Récupérer tous les commentaires pour une publication
    public function getCommentaires($publication_id) {
        $query = "SELECT commentaires.id, commentaires.commentaire, commentaires.date_creation, utilisateurs.nom, utilisateurs.prenom 
                  FROM commentaires
                  JOIN utilisateurs ON commentaires.utilisateur_cin = utilisateurs.cin
                  WHERE publication_id = :publication_id
                  ORDER BY commentaires.date_creation DESC";
        $stmt = $this->getPdo()->prepare($query);
        $stmt->bindParam(':publication_id', $publication_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Modifier un commentaire
    public function modifierCommentaire($id, $commentaire) {
        $this->setId($id);
        $this->setCommentaire($commentaire);

        $query = "UPDATE commentaires SET commentaire = :commentaire WHERE id = :id";
        $stmt = $this->getPdo()->prepare($query);
        $stmt->bindParam(':id', $this->getId());
        $stmt->bindParam(':commentaire', $this->getCommentaire());
        return $stmt->execute();
    }

    // Supprimer un commentaire
    public function supprimerCommentaire($id) {
        $this->setId($id);
        $query = "DELETE FROM commentaires WHERE id = :id";
        $stmt = $this->getPdo()->prepare($query);
        $stmt->bindParam(':id', $this->getId());
        return $stmt->execute();
    }

    // Vérifier si une publication existe
    public function verifierPublicationExistante($publication_id) {
        $query = "SELECT COUNT(*) FROM publications WHERE id = :publication_id";
        $stmt = $this->getPdo()->prepare($query);  // Utilisation de $this->getPdo()
        $stmt->bindParam(':publication_id', $publication_id);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        
        // Si le résultat est supérieur à 0, la publication existe
        return $result > 0;
    }
}
?>
