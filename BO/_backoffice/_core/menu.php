<?php
$menu_items = [
    // Tableau de bord général
    ['id' => 'dashboard', 'icon' => '📊', 'label' => 'Tableau de bord', 'roles' => ['*']],

    // Bloc d’accueil (table home_blocks)
    ['id' => 'home', 'icon' => '🏡', 'label' => 'Accueil (Bloc)', 'roles' => ['ROLE_ADMIN']],

    // Actualités (table news)
    ['id' => 'news', 'icon' => '📰', 'label' => 'Actualités', 'roles' => ['ROLE_ADMIN','ROLE_EDITOR']],

    // Matchs & Résultats (table matches)
    ['id' => 'resultats', 'icon' => '🏆', 'label' => 'Matchs & Résultats', 'roles' => ['ROLE_ADMIN','ROLE_SPORT_MANAGER']],

    // Équipes (tables teams + teams_seasons + categories + seasons)
    ['id' => 'equipes', 'icon' => '👥', 'label' => 'Équipes', 'roles' => ['ROLE_ADMIN','ROLE_SPORT_MANAGER']],

    // Joueurs / Licenciés (table users + users_club_functions + club_functions)
    ['id' => 'joueurs', 'icon' => '⚽', 'label' => 'Joueurs & Licenciés', 'roles' => ['ROLE_ADMIN','ROLE_SPORT_MANAGER']],

    ['id' => 'staff',       'icon' => '👔', 'label' => 'Staff',        'link' => 'staff.php',       'roles' => ['ROLE_ADMIN','ROLE_EDITOR']],

    // Convocations des joueurs 
    //['id' => 'convocations', 'icon' => '📅', 'label' => 'Convocations', 'roles' => ['ROLE_ADMIN','ROLE_SPORT_MANAGER','ROLE_COACH']],

    
    // Médias (table medias)
    ['id' => 'medias', 'icon' => '🖼️', 'label' => 'Médiathèque', 'roles' => ['ROLE_ADMIN','ROLE_EDITOR']],

    // Partenaires (table partners)
    ['id' => 'partenaires', 'icon' => '🤝', 'label' => 'Partenaires', 'roles' => ['ROLE_ADMIN']],

    // Contacts (table contacts)
    ['id' => 'contacts', 'icon' => '✉️', 'label' => 'Contacts', 'roles' => ['ROLE_ADMIN']],

    // Utilisateurs & rôles du back-office (tables users + roles)
    ['id' => 'utilisateurs', 'icon' => '🛡️', 'label' => 'Utilisateurs (Back-Office)', 'roles' => ['ROLE_ADMIN']],


      ['id' => 'arbitres',    'icon' => '👨‍⚖️', 'label' => 'Arbitres',   'link' => 'arbitres.php',    'roles' => ['ROLE_ADMIN','ROLE_EDITOR']],


    // Modération (ex: commentaires futurs, si besoin)
    //['id' => 'moderations', 'icon' => '💬', 'label' => 'Modération', 'roles' => ['ROLE_ADMIN','ROLE_MODERATOR']],

    // Paramètres (technique, réservé admin)
    ['id' => 'parametres', 'icon' => '⚙️', 'label' => 'Paramètres', 'roles' => ['ROLE_ADMIN']]
];