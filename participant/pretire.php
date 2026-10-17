<?php

// $Id: pretire.php,v 1.30 2007/11/10 02:57:58 snikkel Exp $

include "../etc/global.inc";
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/psecure.inc";
include "../etc/team.php";

$title = "Retiring " . $gpart->get_email();

$field_cls = "w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500";
$btn_cls = "cursor-pointer rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-500";
$danger_cls = "cursor-pointer rounded bg-red-600 px-3 py-1 text-xs font-medium text-white hover:bg-red-500";
$old_email = safe_display($gpart->get_email());

include "../templates/header.inc";
display_last_update('t');
?>
  <div class="mx-auto max-w-2xl">
    <h1 class="phead mb-1">Retire Email Address</h1>
    <p class="mb-6 text-center text-sm text-slate-600"><?=$old_email?></p>
<?
if ($readonly_pretire == 1) {
    include "../templates/readonly.inc";
    ?>
    <p class="text-center"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="/">I'll go find something else to do then</a></p>
  </div>
<?
    include "../templates/footer.inc";
    exit;
}

if (isset($_POST['destid']) && $_POST['destid'] <> '') {
    // Step 3: the actual (permanent) retire. POST only, so a stray link click can't trigger it.
    $destid = (int) $_POST['destid'];
    if ($destid > 0 && $destid != $id && $gpart->retire($destid)) {
        $destpart = new Participant($gdb, $gproj, $destid);
        ?>
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
      <h2 class="mb-2 font-semibold">Retire procedure successful</h2>
      <p class="mb-2">You have retired <strong><?=$old_email?></strong>. This takes effect during the next stats run.</p>
      <p class="mb-2">All past blocks, and any future blocks submitted from <?=$old_email?>, will be allocated to the stats for <strong><?=safe_display($destpart->get_email())?></strong> instead.</p>
      <p>Future blocks from this address will be attributed to team <?=(int) $destpart->get_team_id()?>, which is your current team. If you change teams later, it will affect all retired emails as you'd expect.</p>
    </div>
    <p class="mt-6 text-center"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="/">Great, take me home</a></p>
<?
    } else {
        ?>
    <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-center text-sm text-red-700" role="alert">
      The retire procedure failed. Nothing was changed.
    </div>
<?
    }
} elseif (isset($_REQUEST['ems']) && $_REQUEST['ems'] <> "") {
    // Step 2: pick the destination address from the matches.
    $ems = $_REQUEST['ems'];
    $result = Participant::get_search_list_no_stats($ems, 50, $gdb);
    $matches = array();
    foreach ($result as $row) {
        if ((int) $row->id != $id) $matches[] = $row;
    }
    if (count($matches) == 0) {
        ?>
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-center text-sm text-red-700" role="alert">
      No other participants were found matching <strong><?=safe_display($ems)?></strong>.
      For more information, see <a class="underline" href="http://www.distributed.net/faqs">the FAQ</a>.
    </div>
    <p class="text-center"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="pretire.php?t=<?=urlencode($token)?>">Search again</a></p>
<?
    } else {
        ?>
    <h2 class="mb-2 text-lg font-semibold text-slate-900">Choose your new email address</h2>
    <p class="mb-2 text-sm text-slate-600">These are the addresses that match what you typed. Choose the one you want to move to.</p>
    <p class="mb-4 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
      This <strong>will permanently retire</strong> <?=$old_email?>. Choosing an address below changes the stats database and cannot be undone.
    </p>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <table class="w-full text-left text-sm">
        <thead class="phead2 bg-slate-100">
          <tr><th class="px-4 py-2 text-right">ID #</th><th class="px-4 py-2">Email address</th><th class="px-4 py-2"></th></tr>
        </thead>
        <tbody>
<? foreach ($matches as $row) { ?>
          <tr class="border-t border-slate-100">
            <td class="px-4 py-2 text-right"><?=(int) $row->id?></td>
            <td class="px-4 py-2"><?=safe_display($row->email)?></td>
            <td class="px-4 py-2 text-right">
              <form method="post" action="pretire.php" onsubmit="return confirm('Permanently retire <?=addslashes($old_email)?> into this address?')">
                <input type="hidden" name="t" value="<?=safe_display($token)?>">
                <input type="hidden" name="destid" value="<?=(int) $row->id?>">
                <input type="submit" value="Retire into this address" class="<?=$danger_cls?>">
              </form>
            </td>
          </tr>
<? } ?>
        </tbody>
      </table>
    </section>
<?
    }
} else {
    // Step 1: explain, and ask for the new address.
    ?>
    <div class="mb-6 space-y-3 text-sm text-slate-700">
      <p class="rounded-lg border border-red-200 bg-red-50 p-3 text-red-700">
        You are about to completely and <strong>permanently</strong> remove <?=$old_email?> from the stats database. This is irreversible, and we cannot restore it.
      </p>
      <p>All past, current and future work submitted by this email will be attributed to a new email address.</p>
      <p>You should <strong>only</strong> do this if you will no longer use the old address. It exists so participants can move from one address to another without losing their longevity or standing.</p>
      <p>Before you start, change your clients over to the new address, flush blocks from it, and wait until those blocks are visible in stats.</p>
    </div>
    <form action="pretire.php" method="post" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
      <input type="hidden" name="t" value="<?=safe_display($token)?>">
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-700" for="ems">Your new email address</label>
        <input id="ems" type="text" name="ems" maxlength="64" class="<?=$field_cls?>" autofocus required>
      </div>
      <div class="text-center">
        <input type="submit" value="Search for this email" class="<?=$btn_cls?>">
      </div>
    </form>
<?
}
?>
  </div>
<?
include "../templates/footer.inc";
?>
