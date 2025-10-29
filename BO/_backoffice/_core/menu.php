<?php
$menu_items = [
    // Tableau de bord général
    ['id' => 'dashboard', 'icon' => '📊', 'label' => 'Tableau de bord', 'roles' => ['*']],

    // Bloc d'accueil (table home_blocks)
    ['id' => 'home', 'icon' => '🏡', 'label' => 'Page d\'accueil', 'roles' => ['ROLE_ADMIN']],

    // Info-pratique (table cub_info)
    ['id' => 'club_info', 'icon' => 'ℹ️', 'label' => 'club-info', 'roles' => ['ROLE_ADMIN','ROLE_EDITOR']],

    // Actualités (table news)
    ['id' => 'news', 'icon' => '📰', 'label' => 'Actualités', 'roles' => ['ROLE_ADMIN','ROLE_EDITOR']],

    // Matchs & Résultats (table matches)
    ['id' => 'calendrier', 'icon' => '🏆', 'label' => 'Résultats & calendrier ', 'roles' => ['ROLE_ADMIN','ROLE_EDITOR','ROLE_SPORT_MANAGER']],

    // Équipes (tables teams + teams_seasons + categories + seasons)
    ['id' => 'equipes', 'icon' => '👥', 'label' => 'Les équipes', 'roles' => ['ROLE_ADMIN','ROLE_SPORT_MANAGER']],

    // Joueurs / Licenciés (table users + users_club_functions + club_functions)
    ['id' => 'joueurs', 'icon' => '⚽', 'label' => 'les joueurs ', 'roles' => ['ROLE_ADMIN','ROLE_SPORT_MANAGER', 'ROLE_COATCH']],

    ['id' => 'staff', 'icon' => '👔', 'label' => 'Staff & Administration', 'link' => 'staff.php', 'roles' => ['ROLE_ADMIN','ROLE_EDITOR']],

    // Convocations des joueurs 
    ['id' => 'convocations', 'icon' => '📅', 'label' => 'Convocations', 'roles' => ['ROLE_ADMIN','ROLE_SPORT_MANAGER','ROLE_COACH']],

    
    // Médias (table medias)
    ['id' => 'medias', 'icon' => '🖼️', 'label' => 'Médiathèque', 'roles' => ['ROLE_ADMIN','ROLE_EDITOR']],
    
    //  AJOUT : Reclasser les médias (sous-section cachée du menu)
    ['id' => 'reclasser_medias', 'icon' => '🗂️', 'label' => 'Reclasser les médias', 'roles' => ['ROLE_ADMIN','ROLE_EDITOR'], 'hidden' => true],

    // Partenaires (table partners)
    ['id' => 'partenaires', 'icon' => '🤝', 'label' => 'Partenaires', 'roles' => ['ROLE_ADMIN', 'ROLE_EDITOR']],

    // Organigramme (table club_structure)
    ['id' => 'club_structure', 'icon' => '📆', 'label' => 'Organigramme', 'roles' => ['ROLE_ADMIN','ROLE_EDITOR']],

    // Contacts (table contacts)
    ['id' => 'contacts', 'icon' => '✉️', 'label' => 'Contacts', 'roles' => ['ROLE_ADMIN']],

    // Utilisateurs & rôles du back-office (tables users + roles)
    ['id' => 'utilisateurs', 'icon' => '🛡️', 'label' => 'Utilisateurs (Back-Office)', 'roles' => ['ROLE_ADMIN']],

    ['id' => 'arbitres', 'icon' => '👨‍⚖️', 'label' => 'Arbitres', 'link' => 'arbitres.php', 'roles' => ['ROLE_ADMIN','ROLE_EDITOR']],


    // Paramètres (technique, réservé admin)
    //['id' => 'parametres', 'icon' => '⚙️', 'label' => 'Paramètres', 'roles' => ['ROLE_ADMIN']]
];