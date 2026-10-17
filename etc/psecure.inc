<?php
  // $Id: psecure.inc,v 1.27 2007/10/24 17:11:33 snikkel Exp $

include_once "participant.php";
// Needed for safe_display() etc. below - include_once so it's a no-op for
// callers (pedit.php, pjointeam.php, pretire.php) that already load it.
include_once "modules.inc";

if (isset($_REQUEST['id']))
	$id = $_REQUEST['id'];
if (isset($_REQUEST['pass']))
	$pass = $_REQUEST['pass'];

/**
 * Renders an on-page login form and exits. Replaces the old HTTP Basic Auth
 * challenge (browser-native popup) with a styled form matching the rest of
 * the site. Any other GET/POST fields already present on the request (eg.
 * a profile edit's form fields, or pjointeam.php's team id) are carried
 * through as hidden inputs so they survive the extra round trip.
 */
function render_login_form($error = '')
{
	global $title;
	$title = 'Log In Required';

	$field_cls = "w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500";
	$label_cls = "block text-sm font-medium text-slate-700 mb-1";

	include "../templates/header.inc";
	?>
	<div class="mx-auto max-w-sm">
	  <h1 class="phead mb-4 text-center">Log In</h1>
<? if ($error != '') { ?>
	  <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-center text-sm text-red-700"><?=safe_display($error)?></div>
<? } ?>
	  <form method="post" action="<?=safe_display(basename($_SERVER['SCRIPT_NAME']))?>" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
<?
	foreach (array_merge($_GET, $_POST) as $k => $v) {
		if ($k === 'id' || $k === 'pass' || is_array($v)) continue;
		?>
	    <input type="hidden" name="<?=safe_display($k)?>" value="<?=safe_display($v)?>">
<?
	}
	?>
	    <div>
	      <label class="<?=$label_cls?>" for="psecure_id">Participant ID or Email</label>
	      <input id="psecure_id" name="id" type="text" class="<?=$field_cls?>" autofocus>
	    </div>
	    <div>
	      <label class="<?=$label_cls?>" for="psecure_pass">Password</label>
	      <input id="psecure_pass" name="pass" type="password" class="<?=$field_cls?>">
	    </div>
	    <div class="text-center">
	      <input type="submit" value="Log In" class="cursor-pointer rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-500">
	    </div>
	  </form>
	</div>
	<?
	include "../templates/footer.inc";
	exit;
}

function authenticate() {
	render_login_form();
}

function authfail($why,$tfrom,$tid,$tpass,$desc = "") {
  	if(!($tfrom == "NULL")) {
   		# This might be a bit verbose.  Disabled normally.
   		error_log("$why $tfrom [$tid/$tpass] $desc",0);
   	}

   	if($tfrom == "cookie") {
		# If the cookie failed, that means it's stale.  Let's clear it
      	# so the luser has a chance at logging in manually...
      	SetCookie("sbid","",time()+3600*24*365,"/");
      	SetCookie("sbpass","",time()+3600*24*365,"/");
       	render_login_form("Your saved login has expired. Please log in again.");
   	}

   	if($tfrom == "auth" and $why == "pbadpass") {
   		# The user has actually keyed in their ID, let's ask again.
   		render_login_form("Invalid participant ID or password.");
   	}

	render_login_form("Invalid participant ID or password.");
}

if(isset($id) && isset($pass) && $pass != "") {
   if($debug > 0) trigger_error("Authenticating through URL.",E_USER_NOTICE);
    # ID and PASS provided in url, use these over all other options
   	$test_id = $id;
   	$test_pass = $pass;
   	$test_from = "url";
} else {
    if(isset($_COOKIE['sbid'])) {
      if($debug > 0) trigger_error("Authenticating through Cookie.",E_USER_NOTICE);
   		# We have a cookie set, use this
		$test_id = $_COOKIE['sbid'];
    	$test_pass = $_COOKIE['sbpass'];
    	$test_from = "cookie";
    } else {
   		# No choice but to ask...
   		if(!isset($_SERVER['PHP_AUTH_USER'])) {
       		authenticate();
   		} else {
                if($debug > 0) trigger_error("Authenticating through PHP headers.",E_USER_NOTICE);
      		$test_id = $_SERVER['PHP_AUTH_USER'];
       		$test_pass = $_SERVER['PHP_AUTH_PW'];
       		$test_from = "auth";
   		}
   	}
}
if($debug > 0) print "<!-- Username: $test_id, Password: $test_pass -->\n";

// @TODO - pass null or gproj here?
$gpart = new Participant($gdb, $gproj, $test_id);
if ( !$gpart ) {
	authfail("pdbbadobject",$test_from,$test_id,$test_pass);
}

if ( (is_numeric($test_id) && $test_id != $gpart->get_id()) || (!is_numeric($test_id) && strtolower($test_id) != $gpart->get_email()) ) {
	authfail("pbaduser",$test_from,$test_id,$test_pass);
}

if ( ! $gpart->check_password($test_pass) ) {
	authfail("pbadpass",$test_from,$test_id,$test_pass);
}

// The authentication passed, so let's set the page variables
$id = $test_id;
$pass = $test_pass;
?>

