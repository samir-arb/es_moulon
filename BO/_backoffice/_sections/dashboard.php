<h2>Bienvenue sur le Back-Office ES Moulon</h2>

<div class="stats-grid">
    <?php foreach ($stats as $stat): ?>
        <div class="stat-card" style="border-left:4px solid <?= $stat['color']; ?>">
            <span><?= $stat['icon']; ?></span>
            <div><?= $stat['label']; ?> : <strong><?= $stat['value']; ?></strong></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="activity-card">
    <h3>Activité récente</h3>
    <?php foreach ($activities as $activity): ?>
        <p>
            <strong><?= $activity['action']; ?></strong> - 
            <?= $activity['item']; ?> 
            <em>(<?= $activity['time']; ?>)</em>
        </p>
    <?php endforeach; ?>
</div>
