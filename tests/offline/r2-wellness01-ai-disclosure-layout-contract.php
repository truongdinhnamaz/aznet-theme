<?php
/** RED -> GREEN: AI photo disclosure in full-width Wellness pattern must keep a readable width. */
declare(strict_types=1);
$path = dirname(__DIR__, 2) . '/patterns/homepage-wellness-01.php';
$s = (string) file_get_contents($path);
$note = 'Ảnh minh họa được tạo bằng AI;';
if (substr_count($s, $note) !== 1) { fwrite(STDERR, "FAIL: explicit AI disclosure lost or repeated\n"); exit(1); }
$open = '<!-- wp:group {"align":"wide","className":"aznet-theme-wellness01__ai-note"';
$at = strpos($s, $open);
if ($at === false) { fwrite(STDERR, "RED: AI disclosure lacks a constrained Core Group\n"); exit(1); }
$end = strpos($s, '<!-- /wp:group -->', $at);
if ($end === false || strpos(substr($s, $at, $end-$at), $note) === false) {
  fwrite(STDERR, "FAIL: AI disclosure not contained inside constrained Group\n"); exit(1);
}
if (!str_contains($s, 'class="wp-block-group alignwide aznet-theme-wellness01__ai-note"')) {
  fwrite(STDERR, "FAIL: expected serialized Core Group HTML wrapper missing\n"); exit(1);
}
echo "PASS: Wellness AI disclosure constrained inside Core Group\n";
