<?php if (!empty($flashs['succes']) || !empty($flashs['erreur'])): ?>
    <div class="container mt-3" aria-live="polite">
        <?php foreach ($flashs['succes'] as $message): ?>
            <div class="auth-alert auth-alert--success mb-2" role="status">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>

        <?php foreach ($flashs['erreur'] as $message): ?>
            <div class="auth-alert auth-alert--error mb-2" role="alert">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
