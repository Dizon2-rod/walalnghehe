<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/pet_service.php';
require_login();

$pets = [];
$petError = null;
try { $pets = (new \PetService())->all(); } catch (Throwable $error) { $petError = 'Import xampp_schema.sql and start MySQL to wake the sanctuary.'; }
$guardiansReady = count($pets) === 3 && count(array_filter($pets, static fn(array $pet): bool => min((int)$pet['hunger'], (int)$pet['hygiene'], (int)$pet['happiness'], (int)$pet['energy']) > 70)) === 3;
include __DIR__ . '/../includes/header.php';
?>
<div class="sanctuary" data-sanctuary>
    <section class="sanctuary-hero hero-gradient"><div><p class="eyebrow">The Monthsary Sanctuary</p><h1 class="romantic-title">Our little family, glowing together.</h1><p class="text-muted">Care for Molly, Mitch, and Raica while we count down to four years of forever.</p></div><div class="anniversary-countdown" data-countdown>—</div></section>
    <?php if ($petError): ?><div class="alert alert-warning rounded-4"><?= htmlspecialchars($petError) ?></div><?php endif; ?>
    <section class="pet-room" data-pet-room><canvas class="bubble-canvas" data-bubble-canvas></canvas><div class="gift-altar"><div class="gift-box">♥<span>14 · 09 · 26</span></div><p>4th Anniversary</p><div class="guardian-status <?= $guardiansReady ? 'ready' : '' ?>"><?= $guardiansReady ? 'All three guardians are ready' : 'Care for all three guardians to unlock the altar' ?></div><a class="btn btn-primary rounded-pill" href="<?= htmlspecialchars(app_url('pages/gallery.php')) ?>">Open memories</a></div>
        <div class="pets-grid">
        <?php foreach ($pets as $pet): $type = (string)$pet['breed_type']; ?>
            <article class="pet-card" data-pet-id="<?= htmlspecialchars((string)$pet['id']) ?>" data-pet-type="<?= htmlspecialchars($type) ?>"><div class="pet-stage"><div class="pet-avatar pet-<?= htmlspecialchars($type) ?>" data-pet-avatar><i class="pet-ear ear-left"></i><i class="pet-ear ear-right"></i><i class="pet-eye eye-left"></i><i class="pet-eye eye-right"></i><i class="pet-nose"></i><i class="pet-bow"></i></div><div class="pet-status-float" data-pet-feedback></div></div><div class="d-flex justify-content-between align-items-center"><h2 class="romantic-title mb-0"><?= htmlspecialchars((string)$pet['name']) ?></h2><span class="pet-mood" data-pet-mood><?= htmlspecialchars((string)$pet['mood']) ?></span></div><div class="small text-muted mb-2">Level <span data-stat="level"><?= (int)$pet['level'] ?></span> · <span data-stat="exp"><?= (int)$pet['exp'] ?></span> XP</div><?php foreach (['hunger'=>'Hunger','hygiene'=>'Hygiene','happiness'=>'Happiness','energy'=>'Energy'] as $key=>$label): ?><div class="stat-line"><span><?= $label ?></span><div class="stat-track"><i data-stat-bar="<?= $key ?>" style="width:<?= (int)$pet[$key] ?>%"></i></div><b data-stat="<?= $key ?>"><?= (int)$pet[$key] ?></b></div><?php endforeach; ?><div class="pet-actions"><button class="btn btn-sm btn-primary rounded-pill" data-pet-action="feed">Feed</button><button class="btn btn-sm btn-outline-dark rounded-pill" data-pet-action="bath">Bath</button><button class="btn btn-sm btn-outline-dark rounded-pill" data-pet-action="pet">Scratch</button><button class="btn btn-sm btn-outline-dark rounded-pill" data-pet-action="sleep">Nap</button></div></article>
        <?php endforeach; ?>
        </div>
    </section>
</div>
<script>window.MONTHSARY_CSRF = <?= json_encode(csrf_token()) ?>; window.MONTHSARY_BASE_PATH = <?= json_encode(getenv('APP_BASE_PATH') ?: '/walalnghehe') ?>;</script>
<script src="<?= htmlspecialchars(app_url('public/assets/js/pet_game.js')) ?>"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>