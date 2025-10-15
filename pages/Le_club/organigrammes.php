<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';

/**
 * Récupère les nœuds pour un type de structure et construit l’arbre.
 * $type accepte 'Administrative' / 'Administratif' ou 'Sportif' / 'Sportive'.
 */
function fetch_org_tree(mysqli $conn, string $type): array {
    // robustesse : on tolère les 2 orthographes
    $map = [
        'Administrative' => ["Administrative", "Administratif"],
        'Sportif'        => ["Sportif", "Sportive"],
    ];
    $types = $map[$type] ?? [$type];

    $in = implode("','", array_map([$conn, 'real_escape_string'], $types));

    $sql = "
        SELECT 
            cs.id_structure      AS id,
            cs.parent_id         AS parent,
            cs.type_structure,
            cs.position_number,
            cf.function_name     AS title,
            CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.name,'')) AS person
        FROM club_structure cs
        LEFT JOIN club_functions cf ON cs.id_club_function = cf.id_club_function
        LEFT JOIN users u           ON cs.id_user = u.id_user
        WHERE cs.is_active = 1
          AND cs.type_structure IN ('{$in}')
        ORDER BY COALESCE(cs.parent_id, 0), cs.position_number, cs.id_structure
    ";

    $res = $conn->query($sql);
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

    // nettoie
    $nodes = [];
    foreach ($rows as $r) {
        $nodes[] = [
            'id'     => (int)$r['id'],
            'parent' => $r['parent'] !== null ? (int)$r['parent'] : null,
            'title'  => $r['title'] ?: '—',
            'person' => trim($r['person'] ?? ''),
        ];
    }

    // -> arbre
    $byParent = [];
    foreach ($nodes as $n) {
        $byParent[$n['parent']][] = $n;
    }

    $build = function($parentId) use (&$build, &$byParent) {
        $branch = [];
        if (!empty($byParent[$parentId])) {
            foreach ($byParent[$parentId] as $n) {
                $item = $n;
                $item['children'] = $build($n['id']);
                $branch[] = $item;
            }
        }
        return $branch;
    };

    return $build(null);
}

/** Rendu récursif UL/LI avec boîtes */
function render_tree(array $tree): void {
    if (!$tree) return;
    echo '<ul>';
    foreach ($tree as $n) {
        echo '<li>';
            echo '<div class="org-node">';
                echo '<h3>'.htmlspecialchars($n['title']).'</h3>';
                $person = $n['person'] !== '' ? $n['person'] : 'À pourvoir';
                echo '<p>'.htmlspecialchars($person).'</p>';
            echo '</div>';
            if (!empty($n['children'])) {
                render_tree($n['children']);
            }
        echo '</li>';
    }
    echo '</ul>';
}

// Data pour les 2 sections
$tree_admin = fetch_org_tree($conn, 'Administrative');
$tree_sport = fetch_org_tree($conn, 'Sportif');

// logo filigrane (mets l’image où tu veux)
$bg_logo = asset('assets/img/logo-esmoulon.png');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Organigramme — ES Moulon</title>
    <link rel="stylesheet" href="<?= asset('_front.css/organigramme.css') ?>">
    <style>
        /* on passe le logo au CSS via variable */
        :root { --org-bg-logo: url('<?= htmlspecialchars($bg_logo) ?>'); }
    </style>
</head>
<body>

<section class="org-section">
    <div class="container">
        <header class="org-header">
            <h1>🏗️ Organigramme du Club</h1>
            <p>Visualisez la structure Administrative et Sportive de l’ES Moulon.</p>
        </header>

        <!-- ================== ADMINISTRATIVE ================== -->

        <article class="org-block">
            <h2 class="org-title org-title--admin">Structure Administrative</h2>

            <div class="org-wrapper">
                <div class="org-chart chart--admin">
                    <?php if (!empty($tree_admin)): ?>
                        <?php render_tree($tree_admin); ?>
                    <?php else: ?>
                        <p class="no-data">Aucun poste administratif n’est enregistré.</p>
                    <?php endif; ?>
                </div>
            </div>
        </article>

        <!-- ====================== SPORTIVE ===================== -->

        <article class="org-block">
            <h2 class="org-title org-title--sport">Structure Sportive</h2>

            <div class="org-wrapper">
                <div class="org-chart chart--sport">
                    <?php if (!empty($tree_sport)): ?>
                        <?php render_tree($tree_sport); ?>
                    <?php else: ?>
                        <p class="no-data">Aucun poste sportif n’est enregistré.</p>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    </div>
</section>

</body>
</html>

<style>
    /* ============================================
    ORGANIGRAMMES (Admin + Sportif) — ES Moulon
    ============================================ */

    * { box-sizing: border-box; }

    body {
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background: #f3f4f6;
    color: #111827;
    }

    .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 28px 16px 60px;
    }

    /* ---------------- Header ---------------- */
    .org-header {
    text-align: center;
    margin-bottom: 32px;
    }
    .org-header h1 {
    margin: 0;
    font-size: clamp(22px, 3.2vw, 36px);
    font-weight: 900;
    color: #065f46;
    }
    .org-header p {
    margin: 8px 0 0;
    color: #6b7280;
    }

    /* --------------- Bloc section --------------- */
    .org-block {
    position: relative;
    margin: 34px 0 56px;
    background:
        linear-gradient(180deg, rgba(255,255,255,0.85) 0%, rgba(255,255,255,0.92) 100%),
        var(--org-bg-logo) center/42% no-repeat;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    padding: 22px 14px 30px;
    }

    /* Titre avec ligne et puce */
    .org-title {
    display: inline-block;
    margin: 4px 0 10px 12px;
    padding: 10px 18px;
    font-size: clamp(16px, 2.2vw, 20px);
    font-weight: 800;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    border-left: 6px solid currentColor;
    }

    .org-title--admin { color: #1e3a8a; } /* bleu */
    .org-title--sport { color: #16a34a; } /* vert */

    /* --------------- Wrapper du chart --------------- */
    .org-wrapper {
    overflow-x: auto; /* sécurité si c'est très large */
    padding: 14px 10px 22px;
    }

    /* ----------------------------------------------
    1) Thème (couleurs par section)
    ---------------------------------------------- */
    .chart--admin { --accent: #1e3a8a; } /* bleu */
    .chart--sport { --accent: #16a34a; } /* vert */

    /* ----------------------------------------------
    2) Graphe UL/LI avec traits auto
    ---------------------------------------------- */
    .org-chart ul {
    padding-top: 40px;
    position: relative;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    margin: 0;
    }

    .org-chart ul::before {
    /* trait vertical qui descend sur le niveau */
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    border-left: 2px solid var(--accent);
    height: 40px;
    transform: translateX(-50%);
    }

    .org-chart > ul::before { display: none; } /* racine */

    /* Un item */
    .org-chart li {
    list-style: none;
    text-align: center;
    position: relative;
    padding: 20px 10px 0 10px;
    }

    /* Traits horizontaux entre frères + verticaux */
    .org-chart li::before,
    .org-chart li::after {
    content: '';
    position: absolute;
    top: 0;
    width: 50%;
    height: 40px;
    border-top: 2px solid var(--accent);
    }

    .org-chart li::before { right: 50%; border-right: 2px solid var(--accent); }
    .org-chart li::after  { left: 50%;  border-left:  2px solid var(--accent); }

    /* pas de traits si enfant unique */
    .org-chart li:only-child::before,
    .org-chart li:only-child::after { display: none; }

    /* bords */
    .org-chart li:first-child::before { border: none; }
    .org-chart li:last-child::after   { border: none; }

    /* ----------------------------------------------
    3) Les boîtes de postes
    ---------------------------------------------- */
    .org-node {
    background: #fff;
    border: 3px solid var(--accent);
    border-radius: 10px;
    min-width: 190px;
    max-width: 260px;
    padding: 14px 18px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform .2s ease, box-shadow .2s ease;
    display: inline-block;
    }

    .org-node:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.12);
    }

    .org-node h3 {
    background: var(--accent);
    color: #fff;
    margin: -14px -18px 10px -18px; /* bandeau plein */
    padding: 10px;
    border-radius: 7px 7px 0 0;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: .3px;
    }

    .org-node p {
    margin: 0;
    color: #1f2937;
    font-weight: 600;
    font-size: .95rem;
    }

    /* --------------- No data --------------- */
    .no-data {
    text-align: center;
    color: #6b7280;
    padding: 30px;
    }

    /* ----------------------------------------------
    4) Responsive : on empile, on cache les traits
    ---------------------------------------------- */
    @media (max-width: 900px) {
    .org-chart ul::before,
    .org-chart li::before,
    .org-chart li::after {
        display: none;
    }

    .org-chart ul {
        flex-direction: column;
        align-items: center;
        padding-top: 0;
    }

    .org-chart li {
        padding-top: 14px;
    }

    .org-node {
        min-width: 180px;
        max-width: 92vw;
    }
    }

</style>
