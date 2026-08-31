<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$id = $_GET['id'] ?? '';
$mongoObjectId = 'mongo_object_id';
try { $oid = $mongoObjectId($id); } catch (Throwable $e) { $oid = null; }
if (!$oid) { flash_set('error', 'Gift not found.'); header('Location: ' . app_url('pages/history.php')); exit; }
$gift = col_gifts()->findOne(['_id' => $oid]);
if (!$gift) { flash_set('error', 'Gift not found.'); header('Location: ' . app_url('pages/history.php')); exit; }
$gift = gift_with_schema_defaults($gift);
$giftId = (string)$gift['_id'];
$unlockedKey = 'unlocked_' . $giftId;
$sealed = !empty($_SESSION[$unlockedKey]);
$unlockAt = new DateTimeImmutable((string)$gift['unlock_at']);
$now = new DateTimeImmutable('now', $unlockAt->getTimezone());
$timeLocked = $now < $unlockAt;

if (($gift['is_locked'] ?? false) && !$sealed && !$timeLocked && $_SERVER['REQUEST_METHOD'] === 'POST') {
	require_csrf();
	if (!can_attempt_unlock($giftId, 3, 30)) {
		flash_set('error', 'Please wait before trying again.');
	} else {
		$input = (string)($_POST['unlock_password'] ?? '');
		$accepted = (array)($gift['accepted_formats'] ?? default_password_formats());
		$ok = !empty($gift['lock_password_hash']) && password_verify($input, $gift['lock_password_hash']);
		$ok = $ok || is_password_valid_against_formats($input, $accepted);
		if ($ok) { $_SESSION[$unlockedKey] = true; clear_unlock_state($giftId); }
		else { register_unlock_failure($giftId, 3, 30); flash_set('error', 'Incorrect password.'); }
	}
	header('Location: ' . app_url('pages/view_gift.php?id=' . urlencode($giftId))); exit;
}

$settings = anniversary_settings();
$timeline = [
	['year' => 'Year 1', 'range' => '2022–2023', 'title' => 'The Beginning', 'snippet' => 'The first chapter of our forever.'],
	['year' => 'Year 2', 'range' => '2023–2024', 'title' => 'Growing Together', 'snippet' => 'Every ordinary day became ours.'],
	['year' => 'Year 3', 'range' => '2024–2025', 'title' => 'Overcoming Challenges', 'snippet' => 'We chose each other, again and again.'],
	['year' => 'Year 4', 'range' => '2025–2026', 'title' => '4 Years & Forever', 'snippet' => 'The best is still unfolding.'],
];
include __DIR__ . '/../includes/header.php';
?>
<div class="anniversary-page" data-gift-id="<?= htmlspecialchars($giftId) ?>" data-unlock-at="<?= htmlspecialchars($unlockAt->format(DateTimeInterface::ATOM)) ?>">
<?php if ($timeLocked || (($gift['is_locked'] ?? false) && !$sealed)): ?>
	<section class="lock-screen card-romance fade-in">
		<div class="lock-heart">♥</div><p class="eyebrow">A memory, waiting for its moment</p>
		<h1 class="romantic-title">For <?= htmlspecialchars($settings['partner_nickname']) ?></h1>
		<?php if ($timeLocked): ?>
			<p>This memory unlocks on our 4th Anniversary: September 14, 2026.</p><div class="precision-countdown" data-anniversary-countdown aria-live="polite">-- : -- : -- : --</div>
		<?php else: ?>
			<p>Our little secret is ready when you are.</p>
			<?php if ($msg = flash_get('error')): ?><div class="alert alert-danger rounded-4"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
			<form method="post" class="unlock-form"><?= csrf_field() ?><input class="form-control rounded-4" type="password" name="unlock_password" placeholder="Our special date" required><button class="btn btn-primary rounded-pill px-4">Unlock the letter</button></form>
			<details class="hint-toggle"><summary>Need a gentle hint?</summary><span><?= htmlspecialchars((string)($gift['lock_hint'] ?? 'Think of the day our story began.')) ?></span></details>
		<?php endif; ?>
	</section>
<?php else: ?>
	<section class="unboxing-stage fade-in" data-unboxing-stage><p class="eyebrow">September 14, 2026 · Chapter Four</p><h1 class="romantic-title">A love letter, sealed for you</h1>
		<div class="envelope-wrap"><div class="envelope" data-envelope><div class="envelope-letter"></div><div class="envelope-flap"></div><button class="wax-seal" type="button" data-seal aria-label="Break the wax seal">♥<span>4 Years</span></button></div></div><p class="text-muted">Tap the seal when you are ready.</p>
	</section>
	<section class="reveal-content" data-reveal-content hidden>
		<div class="row g-5 align-items-center"><div class="col-lg-5"><div class="polaroid"><img src="<?= htmlspecialchars((string)($gift['image'] ?? '')) ?>" alt="Our memory"><span><?= htmlspecialchars((string)$gift['title']) ?></span></div></div><div class="col-lg-7"><p class="eyebrow">To my favorite person</p><h2 class="romantic-title"><?= htmlspecialchars((string)$gift['title']) ?></h2><p class="love-letter" data-typewriter><?= htmlspecialchars((string)$gift['message']) ?></p><?php if (!empty($gift['voice_note_url'])): ?><div class="audio-card"><strong>A little piece of my voice</strong><audio controls src="<?= htmlspecialchars((string)$gift['voice_note_url']) ?>"></audio></div><?php endif; ?></div></div>
		<?php if (!empty($gift['music_url']) || !empty($gift['music'])): ?><div class="music-widget"><span>♪ Our song</span><audio data-background-music loop src="<?= htmlspecialchars((string)($gift['music_url'] ?: $gift['music'])) ?>"></audio><button type="button" data-music-toggle>Play</button><input data-music-volume type="range" min="0" max="1" step=".05" value=".35" aria-label="Volume"></div><?php endif; ?>
		<section class="timeline-section"><p class="eyebrow">Four chapters, one story</p><h2 class="romantic-title">Memory Lane</h2><div class="memory-timeline"><?php foreach ($timeline as $index => $chapter): ?><article class="timeline-node"><div class="timeline-dot"><?= $index + 1 ?></div><div><small><?= htmlspecialchars($chapter['year'] . ' · ' . $chapter['range']) ?></small><h3><?= htmlspecialchars($chapter['title']) ?></h3><p><?= htmlspecialchars($chapter['snippet']) ?></p></div></article><?php endforeach; ?></div></section>
		<section class="coupons-section"><p class="eyebrow">For whenever you need a little extra love</p><h2 class="romantic-title">Love Coupons</h2><div class="coupon-grid"><?php $coupons = $gift['coupons'] ?: [['id'=>1,'title'=>'Unlimited Cuddle Pass','icon'=>'♥'],['id'=>2,'title'=>'Late Night Food Trip Ticket','icon'=>'Food'],['id'=>3,'title'=>'1 Day No-Tampo Pass','icon'=>'Forever']]; foreach ($coupons as $coupon): ?><article class="scratch-card" data-scratch-card data-coupon-id="<?= (int)$coupon['id'] ?>"><canvas></canvas><div class="coupon-secret"><span><?= htmlspecialchars((string)$coupon['icon']) ?></span><strong><?= htmlspecialchars((string)$coupon['title']) ?></strong><button class="btn btn-sm btn-primary rounded-pill" data-redeem>Redeem</button></div></article><?php endforeach; ?></div></section>
		<section class="reply-section"><p class="eyebrow">Your turn, sweetheart</p><h2 class="romantic-title">Leave me a little reply</h2><form data-reply-form><div class="reaction-picker" role="radiogroup"><?php foreach (['❤️','🥺','🥰','💍'] as $reaction): ?><label><input type="radio" name="reaction" value="<?= htmlspecialchars($reaction) ?>" required><span><?= $reaction ?></span></label><?php endforeach; ?></div><textarea name="message" class="form-control rounded-4" rows="3" placeholder="Tell me what is in your heart..."></textarea><button class="btn btn-primary rounded-pill mt-3">Send my love</button><div data-reply-status class="small mt-3" aria-live="polite"></div></form></section>
	</section>
<?php endif; ?></div>
<script>window.MONTHSARY_CSRF = <?= json_encode(csrf_token()) ?>;</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>