<?php

// $Id: pedit.php,v 1.27 2006/11/02 15:41:31 thejet Exp $
//
// psecure.inc will obtain $id and $pass from the user.
// Input may come from the url, http headers, or a client cookie

include "../etc/global.inc";
include "../etc/project.inc";
include "../etc/modules.inc";
include "../etc/psecure.inc";
include "../etc/team.php";

$title = 'Participant Edit';
$nonprofit = 0+$gpart->get_non_profit();

$team = $gpart -> get_team_id();
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
	$npoptions .= "<option value=\"$npbuf\" $selstring>".safe_display($npdata->name)."</option>";
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
	$countryoptions .= "<option value=\"$country->code\" $selstring>".safe_display($country->country)."</option>";
}

// Shared field styling, used inline below and in $lmlist's <select>.
$field_cls = "w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500";
$label_cls = "block text-sm font-medium text-slate-700 mb-1";

$sel_normal = '';
$sel_obscure = '';
$sel_realname = '';

$lmmore = '';
if($gpart->get_list_mode() <= 2) {
	switch ($gpart->get_list_mode()) {
		case 0:
			$sel_normal = 'selected';
			break;
		case 1:
			$sel_obscure = 'selected';
			break;
		case 2:
			$sel_realname = 'selected';
			break;
	}
	$lmlist = "
          <select name=\"listas\" class=\"$field_cls\">
          <option value=\"0\" $sel_normal>List me as '".safe_display($gpart->get_email())."'.</option>
          <option value=\"1\" $sel_obscure>List me as 'Participant $id'.</option>
          <option value=\"2\" $sel_realname>List me using my real name.</option>
          </select>";
} else { // @TODO any code about list_mode from here on should never get reached as psecure should block it...
	switch ($gpart->get_list_mode()) {
		case 8:
		case 18:
			$lmlist = 'This participant is a known hacker.';
			break;
		case 9:
		case 19:
			$lmlist = 'This participant is a known spammer.';
			break;
		case 11:
			$lmlist = 'This participant is a team address.';
			break;
	}
	if ($gpart->get_list_mode() >= 10) {
		$lmmore = "This participant will not be ranked or listed.";
	}
}

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

include "../templates/header.inc";
display_last_update();

?>
  <div class="mx-auto max-w-2xl">
    <h1 class="phead mb-1">Participant Configuration</h1>
    <p class="mb-6 text-center text-sm text-slate-600"><?=safe_display($gpart->get_email())?></p>

<? if ($readonly_pedit != 0) { ?>
    <div class="mx-auto max-w-md rounded-lg border border-red-200 bg-red-50 p-4 text-center text-sm text-red-700">
      <strong>Read-only.</strong> The server is currently undergoing maintenance. As a result, you cannot currently edit your details. Sorry for any inconvenience.
    </div>
<? } else { ?>
    <form action="pedit_save.php" method="post" class="space-y-6">
      <input name="id" type="hidden" value="<?=safe_display($gpart->get_id())?>">
      <input name="pass" type="hidden" value="<?=safe_display($pass)?>">
      <input name="project_id" type="hidden" value="<?=$gproj->get_id()?>">

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">Team &amp; Listing</div>
        <div class="space-y-4 p-4">
          <div>
            <span class="<?=$label_cls?>">Team</span>
            <p class="text-sm text-slate-600"><?=safe_display("$team: $teamname")?></p>
            <p class="mt-1 text-xs text-slate-500">
              To join a team, have your email address and password handy and visit that team's stats summary page.
<? if ($team != 0) { ?>
              If you do not wish to be on a team, click <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="pjointeam.php?team=0">here</a>.
<? } ?>
            </p>
          </div>
          <div>
            <label class="<?=$label_cls?>" for="nonprofit">Non-Profit</label>
            <select id="nonprofit" name="nonprofit" class="<?=$field_cls?>">
              <?=$npoptions?>
            </select>
          </div>
          <div>
            <span class="<?=$label_cls?>">List Mode</span>
            <?=$lmlist?>
<? if ($lmmore != '') { ?>
            <p class="mt-1 text-xs text-slate-500"><?=safe_display($lmmore)?></p>
<? } ?>
          </div>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">Motto</div>
        <div class="space-y-2 p-4">
          <textarea name="motto" rows="5" class="<?=$field_cls?>"><?=safe_display($gpart->get_motto())?></textarea>
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
<? for ($f = 0; $f < 5; $f++) { $letter = chr(ord('a') + $f); ?>
          <div>
            <label class="<?=$label_cls?>" for="friend_<?=$letter?>">Friend #<?=$f + 1?></label>
            <input id="friend_<?=$letter?>" name="friend_<?=$letter?>" value="<?=safe_display(get_friend_id($gpart, $f))?>" class="<?=$field_cls?>">
          </div>
<? } ?>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">Contact Details</div>
        <div class="grid gap-4 p-4 sm:grid-cols-2">
          <div>
            <label class="<?=$label_cls?>" for="contact_name">Real Name</label>
            <input id="contact_name" name="contact_name" value="<?=safe_display($gpart->get_contact_name())?>" class="<?=$field_cls?>">
          </div>
          <div>
            <label class="<?=$label_cls?>" for="contact_phone">Phone Number</label>
            <input id="contact_phone" name="contact_phone" value="<?=safe_display($gpart->get_contact_phone())?>" class="<?=$field_cls?>">
          </div>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">About You</div>
        <div class="grid gap-4 p-4 sm:grid-cols-2">
          <div>
            <label class="<?=$label_cls?>" for="dem_yob">Year you were born</label>
            <input id="dem_yob" name="dem_yob" value="<?=safe_display($gpart->get_dem_yob())?>" size="4" class="<?=$field_cls?>">
          </div>
          <div>
            <label class="<?=$label_cls?>" for="dem_gender">Gender</label>
            <select id="dem_gender" name="dem_gender" class="<?=$field_cls?>">
              <option value="">Private</option>
              <option value="M" <?=$gsel_male?>>Male</option>
              <option value="F" <?=$gsel_female?>>Female</option>
            </select>
          </div>
          <div>
            <label class="<?=$label_cls?>" for="dem_country">Country</label>
            <select id="dem_country" name="dem_country" class="<?=$field_cls?>">
              <?=$countryoptions?>
            </select>
          </div>
          <div>
            <label class="<?=$label_cls?>" for="dem_heard">How did you hear about distributed.net?</label>
            <select id="dem_heard" name="dem_heard" class="<?=$field_cls?>">
              <option value="0" <?=$hsel_dunno?>>Who knows? I've slept since then.</option>
              <option value="1" <?=$hsel_friend?>>A friend told me about it.</option>
              <option value="2" <?=$hsel_banner?>>I clicked on a banner.</option>
              <option value="3" <?=$hsel_link?>>I followed a link from someone's page.</option>
              <option value="4" <?=$hsel_sig?>>Saw it in someone's .signature file.</option>
              <option value="5" <?=$hsel_press?>>Read an article about it.</option>
              <option value="99" <?=$hsel_promo?>>None of the above, you need more options!</option>
            </select>
          </div>
          <div class="sm:col-span-2">
            <label class="<?=$label_cls?>" for="dem_motivation">Why do you participate?</label>
            <select id="dem_motivation" name="dem_motivation" class="<?=$field_cls?>">
              <option value="0" <?=$msel_dunno?>>Why not?</option>
              <option value="1" <?=$msel_cool?>>It's really cool!</option>
              <option value="2" <?=$msel_politic?>>To fight the man! It's all about politics.</option>
              <option value="3" <?=$msel_cash?>>I need the money.</option>
              <option value="4" <?=$msel_stats?>>I love stats.</option>
              <option value="5" <?=$msel_cow?>>The cow is soooooo cute.</option>
              <option value="6" <?=$msel_sex?>>To attract the opposite sex.</option>
            </select>
          </div>
        </div>
      </section>

      <div class="rounded-lg border border-slate-200 bg-white p-4 text-center shadow-sm">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
          <input name="cookie" type="checkbox" value="yes" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
          Save my login information in a cookie
        </label>
        <p class="mt-2 text-xs text-red-600">It would be very silly to do this on a machine you share with others or on a machine that's not in a secure location. This will store your password on the machine.</p>
      </div>

      <div class="text-center">
        <input type="submit" value="Update my information" class="cursor-pointer rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-500">
      </div>
    </form>
<? } ?>

    <div class="mx-auto mt-10 max-w-lg text-center text-sm text-slate-600">
      <p>If this address is no longer current/valid, you may &ldquo;retire&rdquo; its blocks into another email address. Before you can do this, you should update all of your clients to your new email address and wait for that new address to appear in the stats database.</p>
<? if ($readonly_pretire == 0) { ?>
      <p class="mt-2"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="pretire.php?id=<?=$id?>&amp;pass=<?=$test_pass?>">Retire this email address permanently</a></p>
<? } else { ?>
      <p class="mt-2 text-slate-400">Retiring is temporarily unavailable.</p>
<? } ?>
    </div>

    <div class="mx-auto mt-10 max-w-lg text-center">
      <h2 class="phead mb-2 text-lg">All information is completely confidential</h2>
      <p class="text-sm text-slate-600">Name and phone number are so we can reach you if your client finds the winning block. The other details just help us understand who's running the client and how to best attract new people.</p>
    </div>
  </div>
<?
include "../templates/footer.inc";

function get_friend_id(&$par, $index)
{
  $friend = $par->get_friends($index);
  if($friend == null)
    return "";
  else
    return $friend->get_id();
}
?>
