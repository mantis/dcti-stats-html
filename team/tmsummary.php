<?
// vi: ts=2 sw=2 tw=120
// $Id: tmsummary.php,v 1.46 2005/08/07 18:07:34 decibel Exp $

// Variables Passed in url:
//  team == team id to display

include "../etc/global.inc";
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/projectstats.php";
include "../etc/markup.inc";
include "../etc/team.php";
include "../etc/teamstats.php";

// Query server
$team = new Team($gdb, $gproj, $tm);
if($team->get_id() == 0) {
	$title = "Team Summary";
	include "../templates/header.inc";
	echo '<p class="text-center text-slate-600">That team is not known.</p>';
	include "../templates/footer.inc";
	exit;
}

// Set the current $tm to be the retrieved team_id (to support team renumbering)
$tm = $team->get_id();

$title = "Team #$tm Summary";
$lastupdate = last_update('t');
include "../templates/header.inc";

$stats = $team->get_current_stats();

$neighbors = $team->get_neighbors();

if (private_markupurl_safety($team->get_logo()) != "") {
  $logo = "<img src=\"".$team->get_logo()."\" alt=\"team logo\">";
} else {
  $logo = "";
}
?>
<div class="text-center">
<h1 class="phead mb-4"><?= safe_display($team->get_name()) ?></h1>
<?if($team->get_id_mismatch() == true) {?>
  <h2 class="phead2 text-red-600 mb-4">NOTICE: This team has been renumbered, the new
  team ID is <?=$team->get_id()?>.</h2>
<?}?>
<? if ($logo != "" || $team->get_description() != "") { ?>
  <div class="flex items-center justify-center gap-4 mb-4">
    <?= $logo ?>
    <div class="text-left text-sm text-slate-700"><?= markup_to_html($team->get_description()) ?></div>
  </div>
<? } ?>
  <p class="mb-4 text-sm text-slate-600">
    Team Contact: <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="mailto:<?= safe_display($team->get_contact_email()) ?>"><?=$team->get_contact_name()?></a>.
  </p>
  <div class="mx-auto max-w-md overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
  <table class="w-full text-sm">
    <tr class="border-b border-slate-200">
      <td class="py-2 px-3"></td>
      <td class="phead2 py-2 px-3 text-center">Overall</td>
<? if ($stats->get_stats_item('work_today') > 0) { ?>
      <td class="phead2 py-2 px-3 text-center">Yesterday</td>
<? } ?>
    </tr>
    <tr class="border-b border-slate-100">
      <td class="phead2 py-1.5 px-3 text-left">Rank:</td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?= $stats->get_stats_item('overall_rank') . " " . html_rank_arrow($stats->get_stats_item('overall_rank_previous') - $stats->get_stats_item('overall_rank')) ?></td>
<? if ($stats->get_stats_item('work_today') > 0) { ?>
      <td class="py-1.5 px-3 text-right tabular-nums"><?= $stats->get_stats_item('day_rank') . " " . html_rank_arrow($stats->get_stats_item('day_rank_previous') - $stats->get_stats_item('day_rank')) ?></td>
<? } ?>
    </tr>
    <tr class="border-b border-slate-100">
    <? if ( $random_stats == 1 ) { ?>
      <!-- A random we will go... -->
    <? } ?>
      <td class="phead2 py-1.5 px-3 text-left"><?= $gproj->get_scaled_unit_name() ?>:</td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?= number_style_convert($stats->get_stats_item('work_total') * $gproj->get_scale()) ?></td>
<? if ($stats->get_stats_item('work_today') > 0) { ?>
      <td class="py-1.5 px-3 text-right tabular-nums"><?= number_style_convert($stats->get_stats_item('work_today') * $gproj->get_scale()) ?></td>
<? } ?>
    </tr>
    <? if ($stats->get_stats_item('days_working') > 0) { ?>
    <tr class="border-b border-slate-100">
      <td class="phead2 py-1.5 px-3 text-left"><?= $gproj->get_scaled_unit_name() ?>/sec:</td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?= number_style_convert($stats->get_stats_item('work_total') * $gproj->get_scale() / (86400 * $stats->get_stats_item('days_working')), 3) ?></td>
<? if ($stats->get_stats_item('work_today') > 0) { ?>
      <td class="py-1.5 px-3 text-right tabular-nums"><?= number_style_convert($stats->get_stats_item('work_today') * $gproj->get_scale() / 86400, 3) ?></td>
    <? } ?>
    </tr>
<? } ?>
    <?if($stats->get_stats_item('members_overall') > 0) {?>
    <tr class="border-b border-slate-100">
      <td class="phead2 py-1.5 px-3 text-left"><?= $gproj->get_scaled_unit_name() ?>/member:</td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?= number_style_convert($stats->get_stats_item('work_total') * $gproj->get_scale() / $stats->get_stats_item('members_overall')) ?></td>
<? if ($stats->get_stats_item('work_today') > 0) { ?>
      <td class="py-1.5 px-3 text-right tabular-nums"><?= number_style_convert($stats->get_stats_item('work_today') * $gproj->get_scale() / $stats->get_stats_item('members_today')) ?></td>
<? } ?>
    </tr>
    <?}?>
    <!-- tr>
      <td align="left" class="phead2"><?= $proj_unscaled_unit_name ?>:</td>
      <td align="right"><?= number_style_convert($par->WORK_TOTAL) ?></td>
<? if ($par->WORK_TODAY > 0) { ?>
      <td align="right"><?= number_style_convert($par->WORK_TODAY) ?></td>
<? } ?>
    </tr>
    <?if ($par->Days_Working > 0) { ?>
    <tr>
      <td align="left" class="phead2"><?= $proj_unscaled_unit_name ?>/sec:</td>
      <td align="right"><?= number_style_convert($par->WORK_TOTAL / (86400 * $par->Days_Working)) ?></td>
<? if ($par->WORK_TODAY > 0) { ?>
      <td align="right"><?= number_style_convert($par->WORK_TODAY / 86400) ?></td>
<? } ?>
    </tr>
    <?}?>
    <?if($par->MEMBERS_OVERALL > 0) {?>
    <tr>
      <td align="left" class="phead2"><?= $proj_unscaled_unit_name ?>/member:</td>
      <td align="right"><?= number_style_convert($par->WORK_TOTAL / $par->MEMBERS_OVERALL) ?></td>
<? if ($par->WORK_TODAY > 0) { ?>
      <td align="right"><?= number_style_convert($par->WORK_TODAY / $par->MEMBERS_TODAY) ?></td>
<? } ?>
    </tr>
    <?}?>
    -->
    <tr>
      <td class="phead2 py-1.5 px-3 text-left">Time Working:</td>
      <td class="py-1.5 px-3 text-right tabular-nums" colspan="<?= ($stats->get_stats_item('work_today') > 0) ? 3 : 2 ?>"><?= number_style_convert($stats->get_stats_item('days_working')) ?> days</td>
    </tr>
  </table>
  </div>
  <?
  $t_show_odds = ($gproj->get_total_units() > 0);
  if ($t_show_odds) {
      if ($stats->get_stats_item('work_today') == 0) {
          $t_odds_value = "a zillion-trillion";
      } else {
          $gprojstats = $gproj->get_current_stats();
          $t_odds_value = "1 in " . number_style_convert($gprojstats->get_stats_item('work_units') / $stats->get_stats_item('work_today'));
      }
  }
  ?>
  <div class="mx-auto mt-4 grid max-w-3xl grid-cols-2 gap-3 sm:grid-cols-4">
   <? if ($t_show_odds) { ?>
    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
     <div class="text-2xl font-semibold text-slate-900"><?=$t_odds_value?></div>
     <div class="mt-1 text-xs text-slate-500">Odds of finding the key first</div>
    </div>
   <? } ?>
    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
     <div class="text-2xl font-semibold text-slate-900"><?=number_style_convert($stats->get_stats_item('members_overall'))?></div>
     <div class="mt-1 text-xs text-slate-500">Participants contributed</div>
    </div>
    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
     <div class="text-2xl font-semibold text-slate-900"><?=number_style_convert($stats->get_stats_item('members_current'))?></div>
     <div class="mt-1 text-xs text-slate-500">Current members</div>
    </div>
    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
     <div class="text-2xl font-semibold text-slate-900"><?=number_style_convert($stats->get_stats_item('members_today'))?></div>
     <div class="mt-1 text-xs text-slate-500">Submitted work today</div>
    </div>
  </div>
  <?
  //Some buttons to view team history will go here
  if ($team->get_show_members() == "NO") {
    ?>
    <p class="mt-4 text-sm text-slate-600">This team wishes to keep its membership private.</p>
  <? } else { ?>
    <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-sm text-slate-600">
     <span>View this team's participant stats:</span>
     <? if ($stats->get_stats_item('work_today') > 0) { ?>
      <a class="rounded border border-indigo-200 bg-indigo-50 px-3 py-1 font-medium text-indigo-700 hover:bg-indigo-100" href="tmember.php?project_id=<?=$project_id?>&amp;team=<?=$tm?>&amp;source=y">Yesterday</a>
     <? } ?>
      <a class="rounded border border-indigo-200 bg-indigo-50 px-3 py-1 font-medium text-indigo-700 hover:bg-indigo-100" href="tmember.php?project_id=<?=$project_id?>&amp;team=<?=$tm?>">Overall</a>
     <? if ($team->get_show_members() == "PAS") { ?>
      <span class="text-xs text-slate-400">(password required)</span>
     <? } ?>
    </div>
  <? }

  //A list of teams goes here
  ?>
  <div class="mx-auto mt-4 max-w-3xl overflow-hidden rounded-lg border border-slate-200 shadow-sm">
    <table class="w-full text-sm">
      <tr>
        <th class="thead text-left">Rank</th>
        <th class="thead text-left">Team</th>
        <th class="thead text-right">Days</th>
        <th class="thead text-right"><?= $gproj->get_scaled_unit_name() ?></th>
        <th class="thead text-right">Yesterday</th>
      </tr>
      <?
      $totalwork = 0;
      $yestwork = 0;
      for ($i = 0; $i < count($neighbors); $i++) {
        $tmpStats = $neighbors[$i]->get_current_stats();
      ?>
        <tr class="border-b border-slate-100 last:border-0 <?=$i % 2 == 0 ? 'bg-white' : 'bg-slate-50'?>">
        <?
        $totalwork += $tmpStats->get_stats_item('work_total');
        $yestwork += $tmpStats->get_stats_item('work_today');
        ?>
          <td class="py-1.5 px-3 text-left"><?= $tmpStats->get_stats_item('overall_rank') . " " . html_rank_arrow($tmpStats->get_stats_item('overall_rank_previous') - $tmpStats->get_stats_item('overall_rank')) ?></td>
          <td class="py-1.5 px-3 text-left">
              <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="tmsummary.php?project_id=<?= $project_id ?>&amp;team=<?= $neighbors[$i]->get_id() ?>"><?= safe_display($neighbors[$i]->get_name()) ?></a>
          </td>
          <td class="py-1.5 px-3 text-right tabular-nums"><?= number_style_convert($tmpStats->get_stats_item('days_working')) ?></td>
          <td class="py-1.5 px-3 text-right tabular-nums"><?= number_style_convert($tmpStats->get_stats_item('work_total') * $gproj->get_scale()) ?></td>
          <td class="py-1.5 px-3 text-right tabular-nums"><?= number_style_convert($tmpStats->get_stats_item('work_today') * $gproj->get_scale()) ?></td>
        </tr>
      <?
      }
      ?>
      <tr>
        <td class="tfoot text-right" colspan="3">Total</td>
        <td class="tfoot text-right"><?= number_style_convert($totalwork * $gproj->get_scale()) ?></td>
        <td class="tfoot text-right"><?= number_style_convert($yestwork * $gproj->get_scale()) ?></td>
      </tr>
    </table>
  </div>
    <p class="mt-6 text-center">
      <a class="inline-block rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500" href="/participant/pjointeam.php?team=<?=$tm?>">I want to join this team!</a>
    </p>

    <div class="mx-auto mt-8 max-w-md rounded-lg border border-slate-200 bg-slate-50 p-4 text-left">
      <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Team Coordinator</div>

      <form action="tmedit.php" method="post" class="mb-4">
        <label class="mb-1 block text-sm text-slate-600">Edit this team's information</label>
        <div class="flex gap-2">
          <input class="min-w-0 flex-1 rounded border border-slate-300 px-2 py-1 text-sm" name="pass" size="8" maxlength="8" type="password" placeholder="Team password">
          <input name="team" type="hidden" value="<?=$team->get_id()?>">
          <input class="rounded bg-slate-800 px-3 py-1 text-sm font-medium text-white hover:bg-slate-700 cursor-pointer" value="Edit" type="submit">
        </div>
      </form>

      <form action="tmpass.php" method="post">
        <p class="mb-2 text-sm text-slate-600">Forgotten your team password? We'll email it to <?=$team->get_contact_name()?>.</p>
        <input type="hidden" name="team" value="<?=$team->get_id()?>">
        <input class="rounded border border-slate-300 bg-white px-3 py-1 text-sm font-medium text-slate-700 hover:bg-slate-100 cursor-pointer" type="submit" value="Email me the password">
      </form>
    </div>
  </div>

<? include "../templates/footer.inc"; ?>
