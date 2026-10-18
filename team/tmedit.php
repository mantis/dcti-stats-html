<?php
// $Id: tmedit.php,v 1.15 2005/12/07 05:44:01 fiddles Exp $
//
// tmsecure.inc logs the coordinator in from an emailed link (?t=) and sets $gteam and $token.

include "../etc/global.inc";
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/tmsecure.inc";

unset($gproj);
$gproj = new Project($gdb, 0);

$field_cls = "w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500";
$label_cls = "block text-sm font-medium text-slate-700 mb-1";

$locked = in_array($gteam->get_listmode(), array(8, 9, 18, 19));

// Form posts back to this page; saved fields are re-read below so the page shows the new values.
$saved = false;
$errors = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $readonly_tmedit == 0 && !$locked) {
  if ($gteam->get_listmode() <= 2 && isset($_POST['listmode']) && (int) $_POST['listmode'] <= 2)
    $gteam->set_listmode((int) $_POST['listmode']);
  $gteam->set_name($_POST['name']);
  $gteam->set_url($_POST['url']);
  $gteam->set_contact_name($_POST['contactname']);
  $gteam->set_contact_email($_POST['contactemail']);
  $gteam->set_logo($_POST['logo']);
  $gteam->set_show_members($_POST['showmembers']);
  $gteam->set_show_password($_POST['showpassword']);
  $gteam->set_description($_POST['description']);

  $errors = $gteam->save();
  $saved = $errors == "";
}

// A changed contact email invalidates the link in use, so hand out a fresh one for it.
if ($saved && strtolower($gteam->get_contact_email()) != strtolower($_POST['old_contactemail'] ?? $gteam->get_contact_email())) {
  $token = $gteam->get_id() . '.' . ($exp = time() + MAGIC_TTL) . '.' . team_magic_sign($gteam, $exp);
}

$lmlist = $lmmore = '';
if ($gteam->get_listmode() <= 2) {
  $lm = $gteam->get_listmode();
  $lmlist = "<select name=\"listmode\" class=\"$field_cls\">"
    . "<option value=\"0\"" . ($lm == 0 ? " selected" : "") . ">Anyone may join this team.</option>"
    . "<option value=\"1\"" . ($lm == 1 ? " selected" : "") . ">A password is required to join this team.</option>"
    . "<option value=\"2\"" . ($lm == 2 ? " selected" : "") . ">This team has disbanded.</option>"
    . "</select>";
} else {
  switch ($gteam->get_listmode()) {
    case 8:
    case 18:
      $lmlist = "This team has a known hacker as a member.";
      break;
    case 9:
    case 19:
      $lmlist = "This team has a known spammer as a member.";
      break;
  }
  if ($gteam->get_listmode() >= 10) {
    $lmmore = "This team will not be ranked or listed.";
  }
}

$title = "Edit Team Information";
include "../templates/header.inc";
display_last_update('t');
?>
  <div class="mx-auto max-w-2xl">
    <h1 class="phead mb-1">Team Configuration</h1>
    <p class="mb-6 text-center text-sm text-slate-600">Team #<?=(int) $tm?></p>

<? if ($locked) { ?>
    <div class="mx-auto max-w-md rounded-lg border border-red-200 bg-red-50 p-4 text-center text-sm text-red-700">
      This team is locked and cannot be edited. Contact <a class="underline" href="mailto:help@distributed.net">help@distributed.net</a> for help.
    </div>
<? } elseif ($readonly_tmedit != 0) { ?>
    <div class="mx-auto max-w-md rounded-lg border border-red-200 bg-red-50 p-4 text-center text-sm text-red-700">
      <strong>Read-only.</strong> The server is currently undergoing maintenance. As a result, you cannot currently edit this team's details. Sorry for any inconvenience.
    </div>
<? } else { ?>
<? if ($saved) { ?>
    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-center text-sm text-emerald-700" role="status">
      Your information has been saved. Name changes take full effect after the next stats run.
      <a class="font-medium underline" href="tmsummary.php?team=<?=(int) $gteam->get_id()?>">Return to the team summary</a>
    </div>
<? } elseif ($errors != "") { ?>
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
      <strong>Validation errors:</strong><br><?=nl2br(safe_display($errors))?>
    </div>
<? } ?>
    <form action="tmedit.php" method="post" class="space-y-6">
      <input name="team" type="hidden" value="<?=(int) $gteam->get_id()?>">
      <input name="t" type="hidden" value="<?=safe_display($token)?>">
      <input name="save" type="hidden" value="1">
      <input name="old_contactemail" type="hidden" value="<?=safe_display($gteam->get_contact_email())?>">

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">Team</div>
        <div class="space-y-4 p-4">
          <div>
            <label class="<?=$label_cls?>" for="name">Team Name</label>
            <input id="name" name="name" value="<?=safe_display($gteam->get_name())?>" maxlength="64" class="<?=$field_cls?>">
            <p class="mt-1 text-xs text-slate-500">No HTML in team names. Changes take full effect after the next stats run.</p>
          </div>
          <div>
            <label class="<?=$label_cls?>" for="url">Team Web Page</label>
            <input id="url" name="url" value="<?=safe_display($gteam->get_url())?>" maxlength="64" class="<?=$field_cls?>">
          </div>
          <div>
            <label class="<?=$label_cls?>" for="logo">Team Logo URL</label>
            <input id="logo" name="logo" value="<?=safe_display($gteam->get_logo())?>" maxlength="64" class="<?=$field_cls?>">
          </div>
          <div>
            <span class="<?=$label_cls?>">Joining</span>
            <?=$lmlist?>
<? if ($lmmore != '') { ?>
            <p class="mt-1 text-xs text-slate-500"><?=safe_display($lmmore)?></p>
<? } ?>
          </div>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">Description</div>
        <div class="space-y-2 p-4">
          <textarea name="description" rows="5" class="<?=$field_cls?>"><?=safe_display($gteam->get_description())?></textarea>
          <p class="text-xs text-slate-500">HTML is permitted in the description.</p>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">Coordinator</div>
        <div class="space-y-4 p-4">
          <div>
            <label class="<?=$label_cls?>" for="contactname">Coordinator's Name</label>
            <input id="contactname" name="contactname" value="<?=safe_display($gteam->get_contact_name())?>" maxlength="64" class="<?=$field_cls?>">
          </div>
          <div>
            <label class="<?=$label_cls?>" for="contactemail">Coordinator's Email</label>
            <input id="contactemail" name="contactemail" value="<?=safe_display($gteam->get_contact_email())?>" maxlength="64" class="<?=$field_cls?>">
            <p class="mt-1 text-xs text-slate-500">Login links for this team are sent to this address.</p>
          </div>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="phead2 bg-slate-100 px-4 py-2 text-sm">Privacy</div>
        <div class="space-y-4 p-4">
          <div>
            <label class="<?=$label_cls?>" for="showmembers">Members Listing</label>
            <select id="showmembers" name="showmembers" class="<?=$field_cls?>">
<? foreach (array('YES' => 'Public Members Listing', 'NO' => 'No Members Listing', 'PAS' => 'Private Members Listing') as $val => $label) { ?>
              <option value="<?=$val?>"<?=$gteam->get_show_members() == $val ? ' selected' : ''?>><?=$label?></option>
<? } ?>
            </select>
          </div>
          <div>
            <label class="<?=$label_cls?>" for="showpassword">Team Members' Password</label>
            <input id="showpassword" name="showpassword" value="<?=safe_display($gteam->get_show_password())?>" maxlength="16" class="<?=$field_cls?>">
            <p class="mt-1 text-xs text-slate-500">Needed to view the members listing when it is private.</p>
          </div>
        </div>
      </section>

      <div class="text-center">
        <input type="submit" value="Update information" class="cursor-pointer rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-500">
      </div>
    </form>
<? } ?>
  </div>
<? include "../templates/footer.inc"; ?>
