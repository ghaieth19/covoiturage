<?php
class Utilisateur {
    private $cin;
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $telephone;
    private $photo_pdp;
    private $photo_voiture;

    // Getters
    public function getCin() {
        return $this->cin;
    }
    public function getNom() {
        return $this->nom;
    }
    public function getPrenom() {
        return $this->prenom;
    }
    public function getEmail() {
        return $this->email;
    }
    public function getMotDePasse() {
        return $this->mot_de_passe;
    }
    public function getTelephone() {
        return $this->telephone;
    }
    public function getPhotoPdp() {
        return $this->photo_pdp;
    }
    public function getPhotoVoiture() {
        return $this->photo_voiture;
    }

    // Setters
    public function setCin($cin) {
        $this->cin = $cin;
    }
    public function setNom($nom) {
        $this->nom = $nom;
    }
    public function setPrenom($prenom) {
        $this->prenom = $prenom;
    }
    public function setEmail($email) {
        $this->email = $email;
    }
    public function setMotDePasse($mot_de_passe) {
        $this->mot_de_passe = $mot_de_passe;
    }
    public function setTelephone($telephone) {
        $this->telephone = $telephone;
    }
    public function setPhotoPdp($photo_pdp) {
        $this->photo_pdp = $photo_pdp;
    }
    public function setPhotoVoiture($photo_voiture) {
        $this->photo_voiture = $photo_voiture;
    }
}
?>
