<?php
  // $Id: newteam4.php,v 1.16 2005/12/07 05:44:01 fiddles Exp $
  
  include "../etc/global.inc";
  include "../etc/modules.inc";
  include "../etc/project.inc";
  include "../etc/team.php";
  include "../etc/teamstats.php";
  unset($proj_name);
  $lastupdate = last_update('t');

  if ($readonly_tmedit != 0) {
     $title = "Team Creation Disabled";
     include "../templates/header.inc";
     include "../templates/readonly.inc";
     include "../templates/footer.inc";
     exit;
  }
  $title = "Adding Team data to stats...";
  include "../templates/header.inc";

  // Create the team object to save the information
  $newteam = new Team($gdb, $gproj);
  $newteam->set_name($_GET['name']);
  $newteam->set_contact_name($_GET['contactname']);
  $newteam->set_contact_email($_GET['contactemail']);

  // Build a random password
  $passstring = "0Aa1Bb2Cc3Dd4Ee5Ff6Gg7Hh8Ii9JjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";
  $password = "";
  for($i = 0; $i < 8; $i++)
  {
    $password .= substr($passstring, rand(0,strlen($passstring)-1), 1);
  }
  $newteam->set_password($password);

  $retVal = $newteam->save();
  if($retVal != "")
  {
    // validation error
    ?>
    <div class="mx-auto max-w-md rounded-lg border border-red-200 bg-red-50 p-4 text-center text-sm text-red-700">
      <h2 class="mb-2 text-base font-semibold">Validation errors occurred</h2>
      <p><?=nl2br(safe_display($retVal))?></p>
      <p class="mt-3"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="javascript:history.back()">Go back and correct the problem</a></p>
    </div>
    <?
    include "../templates/footer.inc";
    exit(0);
  }

  $password = $newteam->get_password();
  $teamnum = $newteam->get_id();
?>
  <div class="mx-auto max-w-xl text-center">
    <h1 class="phead mb-6">Saving your new team&hellip;</h1>

    <div class="mx-auto grid max-w-md gap-4 sm:grid-cols-2">
      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Team number</p>
        <p class="mt-1 text-2xl font-bold tabular-nums text-red-700"><?=safe_display($teamnum)?></p>
      </div>
      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Configuration password</p>
        <p class="mt-1 text-2xl font-bold tabular-nums text-red-700"><?=safe_display($password)?></p>
      </div>
    </div>

    <p class="mx-auto mt-6 max-w-md text-sm text-slate-600">
      Your team will <strong class="text-red-700">not</strong> be listed in the stats database <strong>until you've joined it</strong>. After you join your team, it will show up after the next stats run.
    </p>

    <div class="mx-auto mt-6 max-w-md space-y-4 text-left text-sm">
      <div>
        <p class="text-slate-700">You may edit your team information by using this link:</p>
        <a class="break-all text-indigo-600 hover:text-indigo-800 hover:underline" href="tmedit.php?team=<?=$teamnum?>&amp;pass=<?=$password?>">http://stats.distributed.net/team/tmedit.php?team=<?=safe_display($teamnum)?>&amp;pass=<?=safe_display($password)?></a>
      </div>
      <div>
        <p class="text-slate-700">You should also join your team by using this link. This will require you to know your email address and your participant password.</p>
        <a class="break-all text-indigo-600 hover:text-indigo-800 hover:underline" href="/participant/pjointeam.php?team=<?=$teamnum?>">http://stats.distributed.net/participant/pjointeam.php?team=<?=safe_display($teamnum)?></a>
      </div>
    </div>
  </div>
<?
include "../templates/footer.inc";
?>
