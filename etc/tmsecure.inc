<?php
// Team login via emailed "magic link" to the team's contact email. See magic.inc for how tokens work.
//
// Needs ?team=N (via project.inc's $tm). On success sets $gteam (authorised Team) and $token.
// Otherwise renders the login form and exits.

include_once "team.php";
include_once "magic.inc";

// The signed "email" is prefixed so a team token can never pass as a participant token (or vice versa).
function team_magic_sign($team, $expiry)
{
	return magic_sign($team->get_id(), $expiry, 'team:' . $team->get_contact_email());
}

// Returns the authorised Team for a token, or null if it is bad, expired or not for team $tm.
function team_magic_verify($token, $tm)
{
	global $gdb, $gproj;
	$bits = magic_parse($token);
	if (!$bits || (int)$bits[0] != $tm) return null;

	$team = new Team($gdb, $gproj, $tm);
	if ($team->get_id() != $tm || $team->get_contact_email() == "") return null;
	if (!hash_equals(team_magic_sign($team, $bits[1]), $bits[2])) return null;

	$team->authorize();
	return $team;
}

// Emails a login link for team $tm if $email is its contact email. Says nothing either way.
function team_magic_send($email, $tm)
{
	global $gdb, $gproj;
	if (!preg_match('/^[^@\s]+@[^@\s]+$/', $email)) return;

	$team = new Team($gdb, $gproj, $tm);
	if ($team->get_id() != $tm || strtolower($team->get_contact_email()) != strtolower($email)) return;

	$expiry = time() + MAGIC_TTL;
	magic_mail($team->get_contact_email(), (int)$team->get_id() . '.' . $expiry . '.' . team_magic_sign($team, $expiry));
}

$token = $_REQUEST['t'] ?? '';
$gteam = $token != '' ? team_magic_verify($token, $tm) : null;

if (!$gteam) {
	$hint = "Enter the team coordinator's email address and we'll email you a link. No password needed.";
	if (isset($_POST['login_email'])) {
		team_magic_send(trim($_POST['login_email']), $tm);
		render_login_form("If that is the coordinator's address for this team, a login link is on its way. Check your email.", false, $hint);
	}
	render_login_form($token != '' ? "That login link is invalid or has expired. Enter the coordinator's email for a new one." : '', true, $hint);
}

// Logged in. Keep the token out of Referer headers and caches.
header("Referrer-Policy: no-referrer");
header("Cache-Control: private, no-store");
?>
