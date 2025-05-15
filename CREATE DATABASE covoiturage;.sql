CREATE DATABASE covoiturage;

USE covoiturage;

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    cin VARCHAR(8) NOT NULL,
    email VARCHAR(100) NOT NULL,
    motdepasse VARCHAR(255) NOT NULL,
    telephone VARCHAR(10) NOT NULL,
    car_photo VARCHAR(255),
    profile_photo VARCHAR(255) NOT NULL,
    terms TINYINT(1) NOT NULL
);