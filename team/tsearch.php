<?
# vi: ts=2 sw=2 tw=120
# $Id: tsearch.php,v 1.19 2004/07/19 06:26:00 jlawson Exp $

// Variables Passed in url:
//   st == Search Term

include "../etc/global.inc";
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/team.php";
include "../etc/teamstats.php";

$title = "Team Search: [".safe_display($st)."]";

if (is_numeric($st)) {
  $team = new Team($gdb, $gproj, (int)$st);
  $result = $team->get_id_mismatch() ? array() : array($team);
} else {
  $result = Team::get_search_list($st, 50, $gdb, $gproj);
}
$rows = count($result);

if($rows == 1) {
  # Only one hit, let's jump straight to tmsummary
  $team = $result[0];
  $id = (int) $team->get_id();
  header("Location: tmsummary.php?project_id=$project_id&team=$id");
  exit;
}

$lastupdate = last_update('t');

include "../templates/header.inc";

?>
  <div class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-slate-200 shadow-sm">
   <table class="w-full text-sm">
    <tr>
      <th class="thead text-left">Rank</th>
      <th class="thead text-left">Team</th>
      <th class="thead text-right">First Unit</th>
      <th class="thead text-right">Last Unit</th>
      <th class="thead text-right">Days</th>
      <th class="thead text-right">Current Members</th>
      <th class="thead text-right"><?=$gproj->get_scaled_unit_name()?> Overall</th>
      <th class="thead text-right"><?=$gproj->get_scaled_unit_name()?> Yesterday</th>
    </tr>
    <?

    $totalblocks = 0;
    $totalblocksy = 0;
    if($rows <= 0)
    {
      echo "<tr><td class=\"py-4 px-3 text-center text-slate-500\" colspan=\"8\">No Matching Records Found</td></tr>\n";
    }
    for ($i = 0; $i < $rows; $i++) {
      $teamTmp =& $result[$i];
      $statsTmp =& $teamTmp->get_current_stats();
      $members = number_format($statsTmp->get_stats_item('members_current'));
      $teamid = $teamTmp->get_id();
      $totalblocks += (float) $statsTmp->get_stats_item('work_total') * $gproj->get_scale();
      $totalblocksy += (float) $statsTmp->get_stats_item('work_today') * $gproj->get_scale();

    ?>
    <tr class="border-b border-slate-100 last:border-0 <?=$i % 2 == 0 ? 'bg-white' : 'bg-slate-50'?>">
      <td class="py-1.5 px-3 text-left"><?= $statsTmp->get_stats_item('overall_rank') . html_rank_arrow($statsTmp->get_stats_item('rank_change'))?></td>
      <td class="py-1.5 px-3 text-left"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="tmsummary.php?project_id=<?=$project_id?>&team=<?=$teamid?>"><?=safe_display($teamTmp->get_name())?></a></td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?= $statsTmp->get_stats_item('first_date')?></td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?= $statsTmp->get_stats_item('last_date')?></td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?= number_format($statsTmp->get_stats_item('days_working'))?></td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?=$members?></td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?=number_format( (float) $statsTmp->get_stats_item('work_total') * $gproj->get_scale())?></td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?=number_format( (float) $statsTmp->get_stats_item('work_today') * $gproj->get_scale())?></td>
    </tr>
    <?
    }
    ?>
    <tr>
      <td class="tfoot text-left"><?=$rows?></td>
      <td class="tfoot text-right" colspan="5">Total</td>
      <td class="tfoot text-right"><?=number_format($totalblocks, 0)?></td>
      <td class="tfoot text-right"><?=number_format($totalblocksy, 0)?></td>
    </tr>
   </table>
  </div>
<?include "../templates/footer.inc";?>
