
<?php
$roles = [
    'ROLE_ADMIN' => [
        'name' => 'Administrateur',
        'color' => '#dc2626',
        'permissions' => [
            'dashboard', 'articles', 'equipes', 'joueurs', 'medias',
            'actualites', 'utilisateurs', 'moderations', 'parametres'
        ]
    ],
    'ROLE_EDITOR' => [
        'name' => 'Rédacteur',
        'color' => '#2563eb',
        'permissions' => ['dashboard', 'articles', 'actualites', 'medias']
    ],
    'ROLE_SPORT_MANAGER' => [
        'name' => 'Responsable Sportif',
        'color' => '#16a34a',
        'permissions' => ['dashboard', 'equipes', 'joueurs', 'convocations', 'resultats']
    ],
    'ROLE_MODERATOR' => [
        'name' => 'Modérateur',
        'color' => '#9333ea',
        'permissions' => ['dashboard', 'moderations', 'articles']
    ],
    'ROLE_LICENSED' => [
        'name' => 'Licencié',
        'color' => '#6b7280',
        'permissions' => [] // aucun accès au back-office
    ]
];

// Vérification des permissions
function hasPermission($role, $section, $menu_items, $roles)
{
    foreach ($menu_items as $item) {
        if ($item['id'] === $section) {
            if (in_array('*', $item['roles'])) return true;
            return in_array($role, $item['roles']);
        }
    }
    return false;
}