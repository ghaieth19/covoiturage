<?php
class PointsRecompense {
    private $pdo;
    private $utilisateurCin;
    private $publicationId;

    public function __construct($pdo, $utilisateurCin, $publicationId) {
        $this->pdo = $pdo;
        $this->utilisateurCin = $utilisateurCin;
        $this->publicationId = $publicationId;
    }

    public function reserverTrajet() {
        if ($this->aDejaReserve()) {
            return ['success' => false, 'message' => "❌ Vous avez déjà réservé ce trajet."];
        }

        if (!$this->placesDisponibles()) {
            return ['success' => false, 'message' => "❌ Désolé, ce trajet est complet."];
        }

        if ($this->reservationAnnulee()) {
            $this->reactiverReservation();
        } else {
            $this->creerReservation();
        }

        $this->reduirePlaces();
        $this->ajouterPointsTrajet();

        return ['success' => true, 'message' => "✅ Réservation confirmée. 30 points ajoutés."];
    }

    private function aDejaReserve() {
        $stmt = $this->pdo->prepare("SELECT * FROM reservations WHERE utilisateur_cin = ? AND publication_id = ?");
        $stmt->execute([$this->utilisateurCin, $this->publicationId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res && $res['status'] === 'active';
    }

    private function reservationAnnulee() {
        $stmt = $this->pdo->prepare("SELECT status FROM reservations WHERE utilisateur_cin = ? AND publication_id = ?");
        $stmt->execute([$this->utilisateurCin, $this->publicationId]);
        $status = $stmt->fetchColumn();
        return $status === 'annulée';
    }

    private function reactiverReservation() {
        $stmt = $this->pdo->prepare("UPDATE reservations SET status = 'active' WHERE utilisateur_cin = ? AND publication_id = ?");
        $stmt->execute([$this->utilisateurCin, $this->publicationId]);
    }

    private function creerReservation() {
        $stmt = $this->pdo->prepare("INSERT INTO reservations (utilisateur_cin, publication_id, places, status) VALUES (?, ?, 1, 'active')");
        $stmt->execute([$this->utilisateurCin, $this->publicationId]);
    }

    private function placesDisponibles() {
        $stmt = $this->pdo->prepare("SELECT places FROM publications WHERE id = ?");
        $stmt->execute([$this->publicationId]);
        return $stmt->fetchColumn() > 0;
    }

    private function reduirePlaces() {
        $stmt = $this->pdo->prepare("UPDATE publications SET places = places - 1 WHERE id = ?");
        $stmt->execute([$this->publicationId]);
    }

    private function ajouterPointsTrajet() {
        $stmt = $this->pdo->prepare("UPDATE utilisateurs SET points_traject = points_traject + 30 WHERE cin = ?");
        $stmt->execute([$this->utilisateurCin]);
    }

    // Getters / Setters
    public function getUtilisateurCin() {
        return $this->utilisateurCin;
    }

    public function setUtilisateurCin($cin) {
        $this->utilisateurCin = $cin;
    }

    public function getPublicationId() {
        return $this->publicationId;
    }

    public function setPublicationId($id) {
        $this->publicationId = $id;
    }
}
