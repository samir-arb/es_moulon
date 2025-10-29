<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Backoffice - ES Moulon</title>
    <!-- Boostrap Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!--css du back office -->
    <link rel="stylesheet" href="<?= asset('_back.css/backoffice.css') ?>">    
    <link rel="stylesheet" href="<?= asset('_back.css/club_structure.css') ?>">
    <link rel="stylesheet" href="<?= asset('_back.css/contacts.css') ?>">
    <link rel="stylesheet" href="<?= asset('_back.css/home.css') ?>">
    <link rel="stylesheet" href="<?= asset('_back.css/joueurs.css') ?>">
    <link rel="stylesheet" href="<?= asset('_back.css/medias.css') ?>">
    <link rel="stylesheet" href="<?= asset('_back.css/equipes.css') ?>">

</head>

<body>
    <div class="dashboard-container">