<?php
// Shared by psecure.inc (participants) and tmsecure.inc (teams): "magic link" signing and the login form.
//
// A link carries a token "<id>.<expiry>.<hmac>" in ?t=. The HMAC covers the id, the expiry and a
// per-kind email string, so changing the email invalidates every outstanding link. Nothing is stored
// server-side and no cookies are set. Pages pass the token along in their links and as a hidden "t" field.
//
// Needs MAGIC_SECRET in etc/config.inc. Optional: MAGIC_TTL (seconds, default 3600),
// MAGIC_BASE_URL (default https://stats.distributed.net), MAGIC_FROM.

include_once "modules.inc";

if (!defined('MAGIC_TTL'))      define('MAGIC_TTL', 3600);
if (!defined('MAGIC_BASE_URL')) define('MAGIC_BASE_URL', 'https://stats.distributed.net');
if (!defined('MAGIC_FROM'))     define('MAGIC_FROM', 'passmail@distributed.net');

if (!defined('MAGIC_SECRET') || MAGIC_SECRET == '') {
	trigger_error("Login is not configured: MAGIC_SECRET is missing from config.inc", E_USER_ERROR);
}

function magic_sign($id, $expiry, $email)
{
	return hash_hmac('sha256', (int)$id . '|' . (int)$expiry . '|' . strtolower($email), MAGIC_SECRET);
}

// Splits "<id>.<expiry>.<hmac>" and checks shape and expiry. Returns array(id, expiry, hmac) or null.
function magic_parse($token)
{
	$bits = explode('.', (string)$token);
	if (count($bits) != 3 || !preg_match('/^\d+$/', $bits[0]) || !preg_match('/^\d+$/', $bits[1]) || $bits[1] < time()) return null;
	return $bits;
}

// Emails a login link to $to for the current page (query string kept, t= replaced by $token).
function magic_mail($to, $token)
{
	$query = $_GET;
	$query['t'] = $token;
	$url = MAGIC_BASE_URL . $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($query);

	send_mail($to, MAGIC_FROM, "Your distributed.net stats login link",
		"Click this link to log in to the distributed.net stats pages:\n\n$url\n\n" .
		"The link works for " . round(MAGIC_TTL / 60) . " minutes. If you did not ask for it, ignore this message.\n");
}

/**
 * Renders the on-page login form and exits. Query-string fields already on the request
 * (eg. pjointeam.php's team id) are kept in the form action so they survive the round trip
 * and end up in the emailed link.
 */
function render_login_form($message = '', $is_error = true, $hint = "We'll email you a link that logs you in. No password needed.")
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
	      <p class="mt-1 text-xs text-slate-500"><?=safe_display($hint)?></p>
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
