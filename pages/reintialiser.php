<?php 
session_start();

$pageTitle = 'Nouveau mot de passe';
$rootPath = '../';
$currentPage = 'connexion';

require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$erreur = '';
$succes = '';
$tokenValide = false;
$token = trim($_GET['token'] ?? '');

if(!empty($token) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("
        SELECT prt.*, u.prenom
        FROM password_reset_tokens prt
        JOIN utilisateurs u ON u.utilisateur_id = prt.utilisateur_id
        WHERE prt.token = :token
        AND prt.used = 0
        AND prt.expire_at > NOW()
        LIMIT 1
        ");
        $stmt->execute([':token' => $token]);
        $resetData = $stmt->fetch(PDO::FETCH_ASSOC);

        if($resetData) {
            $tokenValide = true;
        } else {
            $erreur = 'Ce lien est invalide ou a expiré. Veuillez faire une nouvelle demande.';
        }
    } catch (Exception $e) {
        $erreur = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }elseif (!empty($token) && !isset($pdo)) {
        //Mode démo sans BDD
        $tokenValide = true;
        $resetData = ['prenom' => 'Utilisateur', 'utilisateur_id' => 0];
    } else {
        $erreur = 'Lien invalide. Veuillez refaire une demande de réintialisation.';
    }

    // TRAITEMENT DU FORMULAIRE POST
    if($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValide) {
        $mdp = $_POST['mot_de_passe'] ?? '';
        $mdp_conf = $_POST['mot_de_passe_conf'] ?? '';

        // Regex énoncé : 10 car min, 1 maj , 1min, 1 chiffre, 1 spécial
        $regexMdp = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/';

        if(empty($mdp) || empty($mdp_conf)) {
            $erreur = 'Veuillez remplir les deux champs.';
        } elseif (!preg_match($regexMdp, $mdp)) {
            $erreur = 'Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
        } elseif ($mdp !== $mdp_conf) {
            $erreur = 'Les mots de passe ne correspondent pas.';
        } elseif (isset($pdo)) {
            try {
                $hash = password_hash($mdp, PASSWORD_DEFAULT);

                //Mettre à jour le mot de passe
                $pdo->prepare("
                UPDATE utilisateurs SET mot_de_passe = :hash
                WHERE utilisateur_id = :uid
                ")->execute([
                    ':hash' => $hash,
                    ':uid' => $resetData['utilisateur_id'],
                ]);

                // Marquer le token comme utilisé
                $pdo->prepare("
                UPDATE password_reset_tokens SET used = 1
                WHERE token = :token
                ")->execute([':token' => $token]);

                $succes = 'Votre mot de passe a été mis à jour avec succès !';
                $tokenValide = false;
            } catch (Exception $e) {
                $erreur = 'Une erreur est survenue. Veuillez réessayer.';
            }
        } else {
            //Mode démo sans BDD
            $succes = 'Mode démo - mot de passe mis à jour avec succès !';
            $tokenValide = false;
        }
    }
?>

<section class="section-auth">
    <div class="auth-bg-deco"></div>

    <div class="container">
        <div class="auth-wrapper">

        <!-- LOGO -->
         <div class="auth-brand">
            <a href="<?= $rootPath ?>pages/accueil.php" class="auth-brand__link">
                <span class="auth-brand__name">Vite <span class="auth-brand__amp">&</span> Gourmand</span>
            </a>
            <p class="auth-brand_tagline">
                <?php if ($tokenValide): ?>
                    Choisissez un nouveau mot de passe sécurisé. 
                <?php else: ?>
                    Accédez à votre espace personnel.
                <?php endif; ?>
            </p>
         </div>

         <!-- CARTE -->
          <div class="auth-card">

          <div class="auth-card__header">
            <h1 class="auth-card__title">Nouveau mot de passe</h1>
            <?php if ($tokenValide): ?>
                <p class="auth-card__sub">
                    Bonjour <?= htmlspecialchars($resetData['prenom']) ?>, créez votre nouveau mot de passe ci-dessous. 
                </p>
            <?php else: ?>
                <p class="auth-card__sub">Réintialisation de votre accès</p>
            <?php endif; ?>
          </div>

          <!-- ALERTE ERREUR -->
           <?php if ($erreur): ?>
            <div class="auth-alert auth-alert--error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?= htmlspecialchars($erreur) ?>
            </div>
            <div class="auth-card__footer">
                <p>
                    <a href="<?= $rootPath ?>pages/mot-de-passe-oublie.php" class="auth-link">
                        ← Refaire une demande
                    </a>
                </p>
            </div>
            <?php endif; ?>

            <!-- SUCCÈS -->
             <?php if ($succes): ?>
                <div class="auth-alert auth-alert--success">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <?= htmlspecialchars($succes) ?>
                </div>
                <div class="auth-card__footer">
                    <p>
                        <a href="<?= $rootPath ?>pages/connexion.php" class="auth-btn">
                            Se connecter
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </a>
                    </p>
                </div>
                <?php endif; ?>

                <!-- FORMULAIRE (visible uniquement si token valide et pas encore soumis) -->
                <?php if ($tokenValide && empty($succes)): ?>
                    <form method="POST" action="?token=<?= htmlspecialchars($token) ?>" class="auth-form" novalidate>

                        <!-- INDICATEUR DE FORCE DU MOT DE PASSE -->
                         <div class="auth-field">
                            <label for="mot_de_passe" class="auth-label">
                                Nouveau mot de passe <span class="auth-required">*</span>
                            </label>
                            <div class="auth-input-wrap">
                                <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                <input type="password" id="mot_de_passe" name="mot_de_passe" class="auth-input" placeholder="••••••••••" required autocomplete="new-password" minlength="10" oninput="evaluerForce(this.value)">
                            </div>
                            <!-- BARRE DE FORCE -->
                             <div class="mdp-force-wrap" id="mdp-force-wrap">
                                <div class="mdp-force-bar">
                                    <div class="mdp-force-bar__fill" id="mdp-force-fill"></div>
                                </div>
                                <span class="mdp-force-label" id="mdp-force-label"></span>
                             </div>
                             <!-- RÈGLES -->
                              <ul class="mdp-regles" id="mdp-regles">
                                <li class="mdp-regle" id="regle-len">Au moins 10 caractères</li>
                                <li class="mdp-regle" id="regle-maj">Une lettre majuscule</li>
                                <li class="mdp-regle" id="regle-min">Une lettre minuscule</li>
                                <li class="mdp-regle" id="regle-num">Un chiffre</li>
                                <li class="mdp-regle" id="regle-spe">Un caractère spécial (!@#$...)</li>
                              </ul>
                         </div>

                         <div class="auth-field">
                            <label for="mot_de_passe_conf" class="auth-label">
                                Confirmer le mot de passe <span class="auth-required">*</span>
                            </label>
                            <div class="auth-input-wrap">
                                <svg class="auth-input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                <input type="password" id="mot_de_passe_conf" name="mot_de_passe_conf" class="auth-input" placeholder="••••••••••" required autocomplete="new-password" oninput="verifierCorrespondance()">
                         </div>
                         <span class="mdp-match-msg" id="mdp-match-msg"></span>
                         </div>
                         <button type="submit" class="auth-btn" id="btn-submit" disabled>
                            Enregistrez le mot de passe
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                         </button>

                    </form>
                    <?php endif; ?>
          </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

