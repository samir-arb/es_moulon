<?php
// _core/dashboard_data.php

// Stats fictives selon rôle
$stats = [];
switch ($user_role) {
    case 'ROLE_ADMIN':
        $stats = [
            ['label' => 'Articles publiés', 'value' => '24', 'color' => '#3b82f6', 'icon' => '📄'],
            ['label' => 'Équipes actives', 'value' => '8', 'color' => '#10b981', 'icon' => '👥'],
            ['label' => 'Joueurs licenciés', 'value' => '256', 'color' => '#f59e0b', 'icon' => '⚽'],
            ['label' => 'En attente', 'value' => '3', 'color' => '#ef4444', 'icon' => '⏳']
        ];
        break;
    case 'ROLE_EDITOR':
        $stats = [
            ['label' => 'Mes articles', 'value' => '12', 'color' => '#3b82f6', 'icon' => '📄'],
            ['label' => 'Brouillons', 'value' => '5', 'color' => '#6b7280', 'icon' => '📝'],
            ['label' => 'Photos ajoutées', 'value' => '48', 'color' => '#10b981', 'icon' => '🖼️']
        ];
        break;
    case 'ROLE_SPORT_MANAGER':
        $stats = [
            ['label' => 'Mon équipe', 'value' => 'U15', 'color' => '#10b981', 'icon' => '👥'],
            ['label' => 'Joueurs', 'value' => '18', 'color' => '#3b82f6', 'icon' => '⚽'],
            ['label' => 'Matchs à venir', 'value' => '3', 'color' => '#f59e0b', 'icon' => '📅'],
            ['label' => 'Victoires', 'value' => '7', 'color' => '#059669', 'icon' => '🏆']
        ];
        break;
    case 'ROLE_MODERATOR':
        $stats = [
            ['label' => 'Commentaires', 'value' => '15', 'color' => '#9333ea', 'icon' => '💬'],
            ['label' => 'À modérer', 'value' => '4', 'color' => '#ef4444', 'icon' => '⚠️'],
            ['label' => 'Validés', 'value' => '11', 'color' => '#10b981', 'icon' => '✅']
        ];
        break;
}

// Activités récentes fictives
$activities = [
    ['action' => 'Article publié', 'item' => 'Victoire 3-1 contre FC Blois', 'time' => 'Il y a 2h'],
    ['action' => 'Équipe modifiée', 'item' => 'U15 - Nouveau joueur ajouté', 'time' => 'Il y a 5h'],
    ['action' => 'Photo ajoutée', 'item' => 'Album match du 25/09', 'time' => 'Hier']
];
