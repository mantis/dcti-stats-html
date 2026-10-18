<?php
// Participant login via emailed "magic link". See magic.inc for how tokens work.
//
// On success sets $gpart (authorised Participant), $id and $token. Otherwise renders the
// login form and exits.

include_once "participant.php";
include_once "magic.inc";

// Returns the authorised Participant for a token, or null if it is bad, expired or the account is ineligible.
function magic_verify($token)
{
	global $gdb, $gproj;
	$bits = magic_parse($token);
	if (!$bits) return null;

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

	$expiry = time() + MAGIC_TTL;
	magic_mail($part->get_email(), (int)$part->get_id() . '.' . $expiry . '.' . magic_sign($part->get_id(), $expiry, $part->get_email()));
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
