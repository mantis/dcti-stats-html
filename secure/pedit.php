<?php

// $Id: pedit.php,v 1.3 2007/10/24 17:30:44 snikkel Exp $
//
// psecure.inc will obtain $id and $pass from the user.
// Input may come from the url, http headers, or a client cookie
  
include "../etc/global.inc";
include "../etc/project.inc";
include "../etc/modules.inc";
include "../etc/team.php";
include "../etc/participant.php";

if (is_int($_REQUEST['id']))
	$id = $_REQUEST['id'];

$gpart = new Participant($gdb, $gproj, $id, true);
if (!$gpart) {
	trigger_error('No participant found with that ID!');
}

$title = 'Participant Edit';  
// @todo - nonprofit + global vars?
$nonprofit = 0+$gpart->get_non_profit();
$password = $gpart->get_password();

$team = $gpart -> get_team_id();
// get a team object or is that pointless for id/name?
$teamname = "Not a team member";
if( $team > 0 ) {
	$teamname = "Invalid team";
        $teamptr = new Team($gdb, $gproj, $team);
        if($teamptr->get_id() > 0)
          $teamname = $teamptr->get_name();
        $teamptr = null;
}

$qs = "select * from STATS_nonprofit order by nonprofit";
$npresult =  $gdb->query($qs);
$nonprofits = $gdb->num_rows($npresult);
  
$npoptions = "<option value=\"0\">None Selected</option>";
for( $i = 0; $i < $nonprofits; $i++) {
	$gdb->data_seek($i,$npresult);
	$npdata = $gdb->fetch_object($npresult);
	$npbuf = 0+$npdata->nonprofit;
	if( $npbuf == $nonprofit) {
		$selstring = "selected";
	} else {
		$selstring = "";
	}
	$npoptions .= "
	<option value=\"$npbuf\" $selstring>".safe_display($npdata->name)."</option>";
}
  
$qs = "select * from STATS_country order by country";
$countryresult = $gdb->query($qs);
$countries = $gdb->num_rows($countryresult);

$countryoptions = "<option value=\"\">None Selected</option>";
for( $i = 0; $i < $countries; $i++) {
	$gdb->data_seek($i,$countryresult);
	$country = $gdb->fetch_object($countryresult);
	if( $country->code == $gpart->get_dem_country() ) {
		$selstring = "selected";
	} else {
		$selstring = "";
	}
	$countryoptions .= "
		<option value=\"$country->code\" $selstring>".safe_display($country->country)."</option>";
}

$sel_obscure = '';
$sel_realname = '';
$sel_hackershow = '';
$sel_spammershow = '';
$sel_invisible = '';
$sel_team = '';
$sel_hacker = '';
$sel_spammer = '';

$lmmore = '';
switch ($gpart->get_list_mode()) {
	case 0:
		$sel_obscure = 'selected';
		break;
	case 1:
		$sel_obscure = 'selected';
		break;
	case 2:
		$sel_realname = 'selected';
		break;
	case 8:
		$sel_hackershow = 'selected';
		break;
	case 9:
		$sel_spammershow = 'selected';
		break;
	case 10:
		$sel_invisible = 'selected';
		break;
	case 11:
		$sel_team = 'selected';
		break;
	case 18:
		$sel_hacker = 'selected';
		break;
	case 19:
		$sel_spammer = 'selected';
		break;
}
// Shared field styling, used inline below and in $lmlist's <select>.
$field_cls = "w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500";
$label_cls = "block text-sm font-medium text-slate-700 mb-1";

$lmlist = "
         <select id=\"listas\" name=\"listas\" class=\"$field_cls\">
         <option value=\"1\" $sel_obscure>List me as 'Participant $id'.</option>
         <option value=\"2\" $sel_realname>List me using my real name.</option>
	 <option value=\"8\" $sel_hackershow>HACKER, But show them.</option>
         <option value=\"9\" $sel_spammershow>SPAMMER, But show them.</option>
         <option value=\"10\" $sel_invisible>Invisible, do not show.</option>
         <option value=\"11\" $sel_team>Team address, do not rank.</option>
         <option value=\"18\" $sel_hacker>HACKER, Do not show.</option>
         <option value=\"19\" $sel_spammer>SPAMMER, Do not show.</option>
         </select>";

$hsel_dunno = '';
$hsel_friend = '';
$hsel_banner = '';
$hsel_link = '';
$hsel_sig = '';
$hsel_press = '';
$hsel_promo = '';

switch ($gpart->get_dem_heard()) {
	case 0:
		$hsel_dunno="selected";
		break;
	case 1:
		$hsel_friend="selected";
		break;
	case 2:
		$hsel_banner="selected";
		break;
	case 3:
		$hsel_link="selected";
		break;
	case 4:
		$hsel_sig="selected";
		break;
	case 5:
		$hsel_press="selected";
		break;
	case 99:
		$hsel_promo="selected";
		break;
}

$gsel_male = '';
$gsel_female = '';
switch ($gpart->get_dem_gender()) {
	case "M":
		$gsel_male="selected";
		break;
	case "F":
		$gsel_female="selected";
		break;
}

$msel_dunno='';
$msel_cool='';
$msel_politic='';
$msel_cash='';
$msel_cow='';
$msel_sex='';
$msel_stats='';

switch ($gpart->get_dem_motivation()) {
	case 0:
		$msel_dunno="selected";
		break;
	case 1:
		$msel_cool="selected";
		break;
	case 2:
		$msel_politic="selected";
		break;
	case 3:
		$msel_cash="selected";
		break;
	case 4:
		$msel_stats="selected";
		break;
	case 5:
		$msel_cow="selected";
		break;
	case 6:
		$msel_sex="selected";
		break;
}

include "etc/header.inc";
display_last_update();

$email = safe_display($gpart->get_email());
?>
  <div class="mx-auto max-w-2xl px-4 py-6">
    <h1 class="phead mb-1">Participant Configuration</h1>
    <p class="mb-6 text-center text-sm text-slate-600"><?php echo $email; ?></p>

<?php if ($readonly_secure != 0) { ?>
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-center text-sm text-amber-800">
      <strong>Read only.</strong> The site is currently read-only, so changes are not possible at the moment.
    </div>
<?php } ?>
    <form action="pedit_save.php" method="post" class="space-y-6">
      <input name="id" type="hidden" value="<?php echo safe_display($gpart->get_id()); ?>">
      <input name="project_id" type="hidden" value="<?php echo $gproj->get_id(); ?>">

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">Team &amp; Listing</div>
        <div class="space-y-4 p-4">
          <div>
            <span class="<?php echo $label_cls; ?>">Team</span>
            <p class="text-sm text-slate-600"><?php echo safe_display("$team: $teamname"); ?></p>
          </div>
          <div>
            <label class="<?php echo $label_cls; ?>" for="nonprofit">Non-Profit</label>
            <select id="nonprofit" name="nonprofit" class="<?php echo $field_cls; ?>">
              <?php echo $npoptions; ?>
            </select>
          </div>
          <div>
            <label class="<?php echo $label_cls; ?>" for="listas">List Mode</label>
            <?php echo $lmlist; ?>
<?php if ($lmmore != '') { ?>
            <p class="mt-1 text-xs text-slate-500"><?php echo safe_display($lmmore); ?></p>
<?php } ?>
          </div>
          <div>
            <label class="<?php echo $label_cls; ?>" for="password">Password</label>
            <input id="password" type="text" name="password" value="<?php echo safe_display($password); ?>" class="<?php echo $field_cls; ?>">
          </div>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">Motto</div>
        <div class="space-y-2 p-4">
          <textarea name="motto" rows="5" class="<?php echo $field_cls; ?>"><?php echo safe_display($gpart->get_motto()); ?></textarea>
          <ul class="list-disc space-y-0.5 pl-5 text-xs text-slate-500">
            <li>[B]bold text[/B]</li>
            <li>[I]italic text[/I]</li>
            <li>[BR] new line</li>
            <li>[URL=http://location/]description[/URL]</li>
            <li>[CENTER]center text[/CENTER]</li>
            <li>[IMG=http://location/image.gif]</li>
          </ul>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">Friends</div>
        <div class="grid grid-cols-2 gap-4 p-4 sm:grid-cols-3">
<?php for ($f = 0; $f < 5; $f++) { $letter = chr(ord('a') + $f); ?>
          <div>
            <label class="<?php echo $label_cls; ?>" for="friend_<?php echo $letter; ?>">Friend #<?php echo $f + 1; ?></label>
            <input id="friend_<?php echo $letter; ?>" name="friend_<?php echo $letter; ?>" value="<?php echo safe_display(get_friend_id($gpart, $f)); ?>" class="<?php echo $field_cls; ?>">
          </div>
<?php } ?>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">Contact Details</div>
        <div class="grid gap-4 p-4 sm:grid-cols-2">
          <div>
            <label class="<?php echo $label_cls; ?>" for="contact_name">Real Name</label>
            <input id="contact_name" name="contact_name" value="<?php echo safe_display($gpart->get_contact_name()); ?>" class="<?php echo $field_cls; ?>">
          </div>
          <div>
            <label class="<?php echo $label_cls; ?>" for="contact_phone">Phone Number</label>
            <input id="contact_phone" name="contact_phone" value="<?php echo safe_display($gpart->get_contact_phone()); ?>" class="<?php echo $field_cls; ?>">
          </div>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">About You</div>
        <div class="grid gap-4 p-4 sm:grid-cols-2">
          <div>
            <label class="<?php echo $label_cls; ?>" for="dem_yob">Year you were born</label>
            <input id="dem_yob" name="dem_yob" value="<?php echo safe_display($gpart->get_dem_yob()); ?>" size="4" class="<?php echo $field_cls; ?>">
          </div>
          <div>
            <label class="<?php echo $label_cls; ?>" for="dem_gender">Gender</label>
            <select id="dem_gender" name="dem_gender" class="<?php echo $field_cls; ?>">
              <option value="">Private</option>
              <option value="M" <?php echo $gsel_male; ?>>Male</option>
              <option value="F" <?php echo $gsel_female; ?>>Female</option>
            </select>
          </div>
          <div>
            <label class="<?php echo $label_cls; ?>" for="dem_country">Country</label>
            <select id="dem_country" name="dem_country" class="<?php echo $field_cls; ?>">
              <?php echo $countryoptions; ?>
            </select>
          </div>
          <div>
            <label class="<?php echo $label_cls; ?>" for="dem_heard">How did you hear about distributed.net?</label>
            <select id="dem_heard" name="dem_heard" class="<?php echo $field_cls; ?>">
              <option value="0" <?php echo $hsel_dunno; ?>>Who knows?  I've slept since then.</option>
              <option value="1" <?php echo $hsel_friend; ?>>A friend told me about it.</option>
              <option value="2" <?php echo $hsel_banner; ?>>I clicked on a banner.</option>
              <option value="3" <?php echo $hsel_link; ?>>I followed a link from someone's page.</option>
              <option value="4" <?php echo $hsel_sig; ?>>Saw it in someone's .signature file.</option>
              <option value="5" <?php echo $hsel_press; ?>>Read an article about it.</option>
              <option value="99" <?php echo $hsel_promo; ?>>None of the above, You need more options!</option>
            </select>
          </div>
          <div class="sm:col-span-2">
            <label class="<?php echo $label_cls; ?>" for="dem_motivation">Why?</label>
            <select id="dem_motivation" name="dem_motivation" class="<?php echo $field_cls; ?>">
              <option value="0" <?php echo $msel_dunno; ?>>Why not?</option>
              <option value="1" <?php echo $msel_cool; ?>>It's really cool!</option>
              <option value="2" <?php echo $msel_politic; ?>>To fight the man!  It's all about politics.</option>
              <option value="3" <?php echo $msel_cash; ?>>I need the money</option>
              <option value="4" <?php echo $msel_stats; ?>>I love stats.</option>
              <option value="5" <?php echo $msel_cow; ?>>The cow is soooooo cute.</option>
              <option value="6" <?php echo $msel_sex; ?>>To attract the opposite sex.</option>
            </select>
          </div>
        </div>
      </section>

<?php if ($readonly_secure == 0) { ?>
      <div class="text-center">
        <input value="Update Information" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
      </div>
<?php } ?>
    </form>
  </div>
<?php
include('etc/footer.inc');

function get_friend_id(&$par, $index)
{
  $friend =$par->get_friends($index);
  if($friend == null)
    return "";
  else
    return $friend->get_id();
}
?>
