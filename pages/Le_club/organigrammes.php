<?php
require_once __DIR__ . '/../../includes/tracker.php';
require_once __DIR__ . '/../../includes/config.php';

/**
 * Récupère les nœuds pour un type de structure et construit l’arbre.
 * $type accepte 'Administrative' / 'Administratif' ou 'Sportif' / 'Sportive'.
 */
function fetch_org_tree(mysqli $conn, string $type): array
{
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

    $build = function ($parentId) use (&$build, &$byParent) {
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
function render_tree(array $tree): void
{
    if (!$tree) return;
    echo '<ul>';
    foreach ($tree as $n) {
        echo '<li>';
        echo '<div class="org-node">';
        echo '<h3>' . htmlspecialchars($n['title']) . '</h3>';
        $person = $n['person'] !== '' ? $n['person'] : 'À pourvoir';
        echo '<p>' . htmlspecialchars($person) . '</p>';
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

?>

<style>
    /* ============================================
    ORGANIGRAMMES (Admin + Sportif) — ES Moulon
    ============================================ */

    :root {
        --green-primary: #009639;
        --green-light: #00b34a;
        --green-dark: #007a3d;
        --white: #ffffff;
        --grey-light: #f9f9f9;
        --noir: #1f2937;
        --vert-esm: #1c995a;
        --vert-fonce: #0b562b;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: white;
        color: black;
        margin: 0;
        line-height: 1.7;
    }

    .contains {
        max-width: 1200px;
        margin: 0 auto;
        padding: 28px 16px 60px;

    }

    /* ---------------- Hero ---------------- */

     /* HERO */
    .hero-history {
        background: linear-gradient(180deg, var(--vert-fonce), var(--vert-esm));
        color: white;
        text-align: center;
        padding: 60px 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-history h1, .hero-history p {
        opacity: 0;
        animation: fadeUp 1.2s ease-out forwards;
    }

    .hero-history h1 {
        font-size: clamp(30px, 5vw, 52px);
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .hero-history p {
        font-size: 1.2rem;
        opacity: 0.9;
        max-width: 750px;
        margin: 0 auto;
    }

    /* --------------- Bloc section --------------- */
  
  .org-block {
    position: relative;
    margin: 40px 0;
    border-radius: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    padding: 22px 14px 30px;
    overflow: hidden; /* évite les débordements internes */
  }

  /* Logo en fond, centré et doux */
  .org-block::before {
    content: "";
    position: absolute;
    inset: 0;
    background: url("../assets/img/logo_moulon.jpg") center/650px no-repeat;
    opacity: 0.10;
    z-index: 0;
  }

  .org-block * {
    position: relative;
    z-index: 1;
  }

  /* Titre */
  .org-title {
    display: inline-block;
    margin: 4px 0 20px 12px;
    padding: 10px 18px;
    font-size: clamp(16px, 2.2vw, 20px);
    font-weight: 800;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    border-left: 6px solid currentColor;
  }

  .org-title--admin { color: #1e3a8a; }
  .org-title--sport { color: #16a34a; }

  /* Wrapper du chart */
  .org-wrapper {
    overflow-x: auto;
    padding: 14px 10px 22px;
  }

  /* Thèmes */
  .chart--admin { --accent: #1e3a8a; }
  .chart--sport { --accent: #16a34a; }

  /* Structure UL/LI */
  .org-chart ul {
    padding-top: 40px;
    position: relative;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    margin: 0;
  }

  .org-chart ul::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    border-left: 2px solid var(--accent);
    height: 40px;
    transform: translateX(-50%);
  }

  .org-chart > ul::before {
    display: none;
  }

  .org-chart li {
    list-style: none;
    text-align: center;
    position: relative;
    padding: 20px 10px 0 10px;
  }

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
  .org-chart li::after { left: 50%; border-left: 2px solid var(--accent); }

  .org-chart li:only-child::before,
  .org-chart li:only-child::after { display: none; }

  .org-chart li:first-child::before { border: none; }
  .org-chart li:last-child::after { border: none; }

  /* Boîtes */
  .org-node {
    background: #fff;
    border: 3px solid var(--accent);
    border-radius: 10px;
    min-width: 180px;
    max-width: 250px;
    padding: 12px 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: transform .2s ease, box-shadow .2s ease;
    display: inline-block;
  }

  .org-node:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
  }

  .org-node h3 {
    background: var(--accent);
    color: #fff;
    margin: -12px -16px 10px -16px;
    padding: 8px;
    border-radius: 7px 7px 0 0;
    font-size: 0.95rem;
    font-weight: 800;
    letter-spacing: .3px;
  }

  .org-node p {
    margin: 0;
    color: #1f2937;
    font-weight: 600;
    font-size: 0.9rem;
  }

  /* Pas de données */
  .no-data {
    text-align: center;
    color: #6b7280;
    padding: 30px;
  }

  /* ===============================
    RESPONSIVE MOBILE
  =================================*/
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
      padding-top: 10px;
    }

    .org-node {
      width: 100%;
      max-width: 95%;
      font-size: 0.9rem;
    }

    .org-node h3 {
      font-size: 0.9rem;
      padding: 6px;
    }

    .org-node p {
      font-size: 0.85rem;
    }

    .org-block::before {
      background-size: 400px; /* logo plus petit */
      opacity: 0.07;
    }
  }

  /* Spécifique téléphone très petit (390px et moins) */
  @media (max-width: 420px) {
    .org-block {
      padding: 14px 10px 20px;
      margin: 25px 0;
    }

    .org-node {
      min-width: 150px;
      padding: 10px 12px;
    }

    .org-node h3 {
      font-size: 0.85rem;
    }

    .org-node p {
      font-size: 0.8rem;
    }
  }

</style>

<!-- HERO -->
<section class="hero-history">
    <h1>Organigramme du Club</h1>
    <p>Visualisez la structure Administrative et Sportive de l’ES Moulon.</p>
</section>

<section class="org-section">
    <div class="contains">

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

