<?php
class Trajet {
    private $depart;
    private $destination;
    private $date;
    private $heure;
    private $places;
    private $marque_voiture;
    private $telephone;
    private $prix_par_passager;

    public function __construct($depart = null, $destination = null, $date = null, $heure = null, $places = null, $marque_voiture = null, $telephone = null, $prix_par_passager = null) {
        $this->depart = $depart;
        $this->destination = $destination;
        $this->date = $date;
        $this->heure = $heure;
        $this->places = $places;
        $this->marque_voiture = $marque_voiture;
        $this->telephone = $telephone;
        $this->prix_par_passager = $prix_par_passager;
    }

    public function getDepart() {
        return $this->depart;
    }

    public function setDepart($depart) {
        $this->depart = $depart;
    }

    public function getDestination() {
        return $this->destination;
    }

    public function setDestination($destination) {
        $this->destination = $destination;
    }

    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function getHeure() {
        return $this->heure;
    }

    public function setHeure($heure) {
        $this->heure = $heure;
    }

    public function getPlaces() {
        return $this->places;
    }

    public function setPlaces($places) {
        $this->places = $places;
    }

    public function getMarqueVoiture() {
        return $this->marque_voiture;
    }

    public function setMarqueVoiture($marque_voiture) {
        $this->marque_voiture = $marque_voiture;
    }

    public function getTelephone() {
        return $this->telephone;
    }

    public function setTelephone($telephone) {
        $this->telephone = $telephone;
    }

    public function getPrixParPassager() {
        return $this->prix_par_passager;
    }

    public function setPrixParPassager($prix_par_passager) {
        $this->prix_par_passager = $prix_par_passager;
    }
}
?>
