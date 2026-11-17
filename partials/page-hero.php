<?php
/**
 * Reusable inner-page hero.
 *
 * $hero = [
 *   'kicker'  => string,
 *   'title'   => string,
 *   'lead'    => string,
 *   'actions' => [ ['label'=>, 'href'=>, 'variant'=>'filled|outlined|text'], ... ],
 * ]
 */
$hero    = $hero ?? [];
$kicker  = $hero['kicker'] ?? '';
$title   = $hero['title'] ?? '';
$lead    = $hero['lead'] ?? '';
$actions = $hero['actions'] ?? [];
?>
<section class="page-hero">
    <div class="wrap page-hero__inner">
        <?php if ($kicker !== ''): ?>
            <span class="kicker"><span class="kicker__dot" aria-hidden="true"></span><?= e($kicker) ?></span>
        <?php endif; ?>
        <h1 class="page-hero__title"><?= $title /* trusted markup */ ?></h1>
        <?php if ($lead !== ''): ?>
            <p class="page-hero__lead"><?= e($lead) ?></p>
        <?php endif; ?>
        <?php if ($actions): ?>
            <div class="page-hero__actions">
                <?php foreach ($actions as $action): ?>
                    <a class="m3-button m3-button--<?= e($action['variant'] ?? 'filled') ?> m3-button--lg" href="<?= e($action['href']) ?>"><?= e($action['label']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
