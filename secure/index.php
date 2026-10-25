<?php
$title = "Home";
include "../etc/global.inc";
include "../etc/project.inc";
include "../etc/modules.inc";

// Latest stats run per project (same source as the public "data reflects..." banner).
$lastruns = array();
$res = $gdb->query("SELECT project_id, TO_CHAR(MAX(last_date), 'dd-Mon-YYYY') AS lastupdate, CURRENT_DATE - MAX(last_date) AS age FROM Email_Rank_Last_Update GROUP BY project_id");
for ($i = 0, $n = $gdb->num_rows($res); $i < $n; $i++) {
  $gdb->data_seek($i, $res);
  $row = $gdb->fetch_object($res);
  $lastruns[(int) $row->project_id] = $row;
}

// Split into [heading => project ids]; only active projects get the stale flag.
$groups = array('Active projects' => array(), 'Completed projects' => array());
foreach ($stats_project_list as $pid => $label) {
  $groups[stats_project_is_completed($pid) ? 'Completed projects' : 'Active projects'][$pid] = $label;
}

include "etc/header.inc";
?>
<div class="mx-auto max-w-3xl px-4 py-6">
  <h1 class="text-4xl font-extrabold leading-none text-slate-900 sm:text-5xl">Stats Administration</h1>
  <p class="mt-4 text-lg text-slate-700">
    Welcome to stats-slash-secure! Select from the menu on the left!
  </p>
<?php if ($GLOBALS['readonly_secure'] != 0) { ?>
  <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">
    <div class="font-semibold">Read only</div>
    <p class="mt-1 text-sm">The site is currently read-only. As a result no changes are possible at the moment.</p>
    <p class="mt-1 text-sm">Sorry for any inconvenience caused.</p>
  </div>
<?php } ?>
<?php foreach ($groups as $heading => $projects) { if (!$projects) continue; ?>
  <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 shadow-sm">
    <div class="bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700"><?=$heading?> &mdash; last stats run</div>
    <table class="w-full text-sm">
<?php foreach ($projects as $pid => $label) {
  $run = $lastruns[$pid] ?? null;
  $stale = $heading == 'Active projects' && $run && $run->age > 2;
?>
      <tr class="border-t border-slate-200">
        <td class="px-4 py-1.5 text-slate-900"><?=safe_display($label)?></td>
        <td class="px-4 py-1.5 text-right <?=$stale ? 'font-semibold text-amber-700' : 'text-slate-600'?>"><?=$run ? safe_display($run->lastupdate) . ' (' . (int) $run->age . 'd ago)' : 'never'?></td>
      </tr>
<?php } ?>
    </table>
  </div>
<?php } ?>
</div>
<?php
include "etc/footer.inc";
?>
