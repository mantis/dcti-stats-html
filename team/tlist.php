<?
// $Id: tlist.php,v 1.22 2004/07/19 18:16:31 jlawson Exp $

# vi: ts=2 sw=2 tw=120 syntax=php

// Variables Passed in url:
//   low == lowest rank used
//   limit == how many lines to return
//   source == "y" for yesterday, all other values ignored.

include "../etc/limit.inc";  // Handles low, high, limit calculations
include "../etc/global.inc";
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/team.php";
include "../etc/teamstats.php";

if ("$source" == "y") {
  $title = "Team Listing by Yesterday's Rank: $lo to $hi";
} else {
  $source = "o";
  $title = "Team Listing by Overall Rank: $lo to $hi";
}

$lastupdate = last_update('t');

include "../templates/header.inc";

// Get the results
#$team = new Team($gdb, $gproj);
$result =& Team::get_ranked_list($source, $lo, $limit, $rows, $gdb, $gproj);

// Figure out what navagation buttons we should have
if ( $lo > $rows ) {
 $btn_back = "<a href=\"$myname?project_id=$project_id&amp;low=$prev_lo&amp;limit=$limit&amp;source=$source\">Back $limit</a>";
} else if ( $lo > 1 and $lo < $limit ) {
 $btn_back = "<a href=\"$myname?project_id=$project_id&amp;low=1&amp;limit=$limit&amp;source=$source\">Back " . ($lo-1) ."</a>";
} else {
 $btn_back = "&nbsp;";
}

if ( $rows >= $limit ) {
 $btn_fwd = "<a href=\"$myname?project_id=$project_id&amp;low=$next_lo&amp;limit=$limit&amp;source=$source\">Next $limit</a>";
} else {
 $btn_fwd = "&nbsp;";
}

?>
  <div class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-slate-200 shadow-sm">
   <table class="w-full text-sm">
    <tr>
       <td class="tfoot text-left"><?=$btn_back?></td>
       <td colspan="6" class="tfoot">&nbsp;</td>
       <td class="tfoot text-right"><?=$btn_fwd?></td>
    </tr>
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
    $totalblocks=0;
    $totalblocksy=0;

    $cnt = count($result);
    for ($i = 0; $i < $cnt; $i++) {
      $teamTmp =& $result[$i];
      $statsTmp =& $teamTmp->get_current_stats();

      $totalblocks += (float) $statsTmp->get_stats_item('work_total') * $gproj->get_scale();
      $totalblocksy += (float) $statsTmp->get_stats_item('work_today') * $gproj->get_scale();
      $decimal_places=0;
      $first = $statsTmp->get_stats_item('first_date');
      $last = $statsTmp->get_stats_item('last_date');

      $teamid = $teamTmp->get_id();
      ?>
      <tr class="border-b border-slate-100 last:border-0 <?=$i % 2 == 0 ? 'bg-white' : 'bg-slate-50'?>">
        <td class="py-1.5 px-3 text-left whitespace-nowrap"><?=$statsTmp->get_stats_item('rank')?><?=html_rank_arrow($statsTmp->get_stats_item('rank_change'))?></td>
        <td class="py-1.5 px-3 text-left"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="tmsummary.php?project_id=<?=$project_id?>&amp;team=<?=$teamid?>"><?= safe_display($teamTmp->get_name()) ?></a></td>
        <td class="py-1.5 px-3 text-right tabular-nums"><?=$first?></td>
        <td class="py-1.5 px-3 text-right tabular-nums"><?=$last?></td>
        <td class="py-1.5 px-3 text-right tabular-nums"><?=number_format($statsTmp->get_stats_item('days_working'), 0)?></td>
        <td class="py-1.5 px-3 text-right tabular-nums"><?=number_format($statsTmp->get_stats_item('members_current'), 0)?></td>
        <td class="py-1.5 px-3 text-right tabular-nums"><?=number_format( (float) $statsTmp->get_stats_item('work_total') * $gproj->get_scale(), 0)?></td>
        <td class="py-1.5 px-3 text-right tabular-nums"><?=number_format( (float) $statsTmp->get_stats_item('work_today') * $gproj->get_scale(), 0)?></td>
      </tr>
      <?
      unset($teamTmp);
      unset($statsTmp);
    }
    ?>
    <tr>
      <td class="tfoot text-left"><? echo "$lo-$hi"?></td>
      <td class="tfoot text-right" colspan="5">Total</td>
      <td class="tfoot text-right"><?=number_format($totalblocks)?></td>
      <td class="tfoot text-right"><?=number_format($totalblocksy)?></td>
    </tr>
    <tr>
      <td class="tfoot text-left"><?=$btn_back?></td>
      <td colspan="6" class="tfoot">&nbsp;</td>
      <td class="tfoot text-right"><?=$btn_fwd?></td>
    </tr>
   </table>
  </div>
<? include "../templates/footer.inc";?>
