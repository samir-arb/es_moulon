<aside id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <div>
            <h1>ES Moulon</h1>
            <p>Back-Office</p>
        </div>
        <!-- bouton hamburger -->
        <button id="menu-toggle" class="menu-toggle">☰</button>
    </div>

    <nav class="nav-menu">
        <ul class="nav-list">
            <?php foreach ($menu_items as $item): ?>
                <?php 
                // ✅ Ignorer les items cachés
                if (isset($item['hidden']) && $item['hidden'] === true) {
                    continue;
                }
                ?>
                <?php if (in_array('*', $item['roles']) || in_array($user_role, $item['roles'])): ?>
                    <li class="nav-item">
                        <a href="?section=<?= $item['id']; ?>" 
                           class="nav-link <?= ($current_section == $item['id']) ? 'active' : ''; ?>">
                            <span class="nav-icon"><?= $item['icon']; ?></span>
                            <span class="nav-label"><?= $item['label']; ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="user-section">
        <div class="user-info">
            <div class="user-avatar" style="background: <?= $roles[$user_role]['color']; ?>;">
                <?= $initiales; ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?= htmlspecialchars($user_prenom . ' ' . $user_nom); ?></div>
                <div class="user-role"><?= $roles[$user_role]['name']; ?></div>
            </div>
        </div>
        <a href="_backoffice/auth/logout.php" class="logout-btn">↪ Déconnexion</a>
    </div>
</aside>

<div class="main-content">
    <header class="topbar">
        <h1>
            <?php
            $current_menu = array_filter($menu_items, fn($item) => $item['id'] === $current_section);
            $current_menu = reset($current_menu);
            echo $current_menu ? $current_menu['label'] : 'Tableau de bord';
            ?>
        </h1>
    </header>
    <div class="content-area">