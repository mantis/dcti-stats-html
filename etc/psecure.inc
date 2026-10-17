<?php
// Participant login via emailed "magic link". No passwords, no cookies, nothing stored server-side.
//
// A link carries a token "<id>.<expiry>.<hmac>" in ?t=. The HMAC covers the id, the expiry and the
// participant's current email, so changing the email invalidates every outstanding link. Pages
// pass the token along (see $token) in their links and as a hidden "t" form field.
//
// Needs MAGIC_SECRET in etc/config.inc. Optional: MAGIC_TTL (seconds, default 3600),
// MAGIC_BASE_URL (default https://stats.distributed.net), MAGIC_FROM.
//
// On success sets $gpart (authorised Participant), $id and $token. Otherwise renders the
// login form and exits.

include_once "participant.php";
// Needed for safe_display() etc. below - include_once so it's a no-op for
// callers (pedit.php, pjointeam.php, pretire.php) that already load it.
include_once "modules.inc";

if (!defined('MAGIC_TTL'))      define('MAGIC_TTL', 3600);
if (!defined('MAGIC_BASE_URL')) define('MAGIC_BASE_URL', 'https://stats.distributed.net');
if (!defined('MAGIC_FROM'))     define('MAGIC_FROM', 'passmail@distributed.net');

function magic_sign($id, $expiry, $email)
{
	return hash_hmac('sha256', (int)$id . '|' . (int)$expiry . '|' . strtolower($email), MAGIC_SECRET);
}

function magic_token($part)
{
	$expiry = time() + MAGIC_TTL;
	return (int)$part->get_id() . '.' . $expiry . '.' . magic_sign($part->get_id(), $expiry, $part->get_email());
}

// Returns the authorised Participant for a token, or null if it is bad, expired or the account is ineligible.
function magic_verify($token)
{
	global $gdb, $gproj;
	$bits = explode('.', (string)$token);
	if (count($bits) != 3 || !preg_match('/^\d+$/', $bits[0]) || !preg_match('/^\d+$/', $bits[1]) || $bits[1] < time()) return null;

	$part = new Participant($gdb, $gproj, (int)$bits[0]);
	if ($part->get_id() != (int)$bits[0] || $part->get_email() == "") return null;
	if (!hash_equals(magic_sign($bits[0], $bits[1], $part->get_email()), $bits[2])) return null;
	if ($part->get_retire_to() > 0 || $part->get_list_mode() > 7) return null;

	$part->authorize();
	return $part;
}

// Emails a login link for $email if it belongs to an eligible participant. Says nothing either way.
function magic_send($email)
{
	global $gdb, $gproj;
	if (!preg_match('/^[^@\s]+@[^@\s]+$/', $email)) return;

	$part = new Participant($gdb, $gproj, $email);
	if ($part->get_id() == 0 || strtolower($part->get_email()) != strtolower($email)) return;
	if ($part->get_retire_to() > 0 || $part->get_list_mode() > 7) return;

	$query = $_GET;
	unset($query['t']);
	$query['t'] = magic_token($part);
	$url = MAGIC_BASE_URL . $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($query);

	send_mail($part->get_email(), MAGIC_FROM, "Your distributed.net stats login link",
		"Click this link to log in to the distributed.net stats pages:\n\n$url\n\n" .
		"The link works for " . round(MAGIC_TTL / 60) . " minutes. If you did not ask for it, ignore this message.\n");
}

/**
 * Renders the on-page login form and exits. Query-string fields already on the request
 * (eg. pjointeam.php's team id) are kept in the form action so they survive the round trip
 * and end up in the emailed link.
 */
function render_login_form($message = '', $is_error = true)
{
	// header.inc/footer.inc are included from function scope, so import the globals they read.
	global $title, $debug, $gdb, $log_timing, $gproj, $project_id, $project_filter, $myname, $lastupdate, $other, $source;
	$title = 'Log In Required';

	$field_cls = "w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500";
	$label_cls = "block text-sm font-medium text-slate-700 mb-1";
	$query = $_GET;
	unset($query['t']);
	$action = basename($_SERVER['SCRIPT_NAME']) . ($query ? '?' . http_build_query($query) : '');

	include __DIR__ . "/../templates/header.inc";
	?>
	<div class="mx-auto max-w-sm">
	  <h1 class="phead mb-4 text-center">Log In</h1>
<? if ($message != '') { ?>
	  <div class="mb-4 rounded-lg border p-3 text-center text-sm <?=$is_error ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'?>"><?=safe_display($message)?></div>
<? } ?>
	  <form method="post" action="<?=safe_display($action)?>" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
	    <div>
	      <label class="<?=$label_cls?>" for="psecure_email">Email address</label>
	      <input id="psecure_email" name="login_email" type="email" class="<?=$field_cls?>" autofocus required>
	      <p class="mt-1 text-xs text-slate-500">We'll email you a link that logs you in. No password needed.</p>
	    </div>
	    <div class="text-center">
	      <input type="submit" value="Email me a login link" class="cursor-pointer rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-500">
	    </div>
	  </form>
	</div>
	<?
	include __DIR__ . "/../templates/footer.inc";
	exit;
}

if (!defined('MAGIC_SECRET') || MAGIC_SECRET == '') {
	trigger_error("Login is not configured: MAGIC_SECRET is missing from config.inc", E_USER_ERROR);
}

$token = $_REQUEST['t'] ?? '';
$gpart = $token != '' ? magic_verify($token) : null;

if (!$gpart) {
	if (isset($_POST['login_email'])) {
		magic_send(trim($_POST['login_email']));
		render_login_form("If that address belongs to a participant, a login link is on its way. Check your email.", false);
	}
	render_login_form($token != '' ? "That login link is invalid or has expired. Enter your email for a new one." : '');
}

// Logged in. Keep the token out of Referer headers and caches.
header("Referrer-Policy: no-referrer");
header("Cache-Control: private, no-store");
$id = $gpart->get_id();
?>
