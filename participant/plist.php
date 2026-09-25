<?
// vi: ts=2 sw=2 tw=120 syntax=php
// $Id: plist.php,v 1.30 2004/07/19 00:59:37 jlawson Exp $
// Variables Passed in url:
// low == lowest rank used
// limit == how many lines to retuwn
// source == "y" for yseterday, all other values ignored.
include "../etc/limit.inc"; // Handles low, high, limit calculations

include "../etc/global.inc";
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/participant.php";

if ("$source" == "y") {
    $title = "Participant Listing by Yesterday's Rank: $lo to $hi";
} else {
    $source = "o";
    $title = "Participant Listing by Overall Rank: $lo to $hi";
}
$lastupdate = last_update('e');
include "../templates/header.inc";

?>
    <div class="mx-auto max-w-4xl overflow-hidden rounded-lg border border-slate-200 shadow-sm">
     <table class="w-full text-sm">
      <tr>
       <th class="thead text-left">Rank</th>
       <th class="thead text-left">Participant</th>
       <th class="thead text-right">First Unit</th>
       <th class="thead text-right">Last Unit</th>
       <th class="thead text-right">Days</th>
       <th class="thead text-right"><?=$gproj->get_scaled_unit_name()?></th>
      </tr>
<?

$totalrows = 0;
$plist = Participant::get_ranked_list($source, $lo, $limit, $totalrows, $gdb, $gproj);
$totalblocks = (float) 0;
$i = 0;
if ($plist) {
	foreach ($plist as $par) {
		$statspar =& $par->get_current_stats();
	    $totalblocks = $totalblocks + (float) $statspar -> get_stats_item('blocks') * $gproj->get_scale();
	    ?>
		<tr class="border-b border-slate-100 last:border-0 <?=$i % 2 == 0 ? 'bg-white' : 'bg-slate-50'?>">
			<td class="py-1.5 px-3 text-left"><?=$statspar -> get_stats_item('rank')?><?=html_rank_arrow($statspar -> get_stats_item('change')) ?></td>
			<td class="py-1.5 px-3 text-left"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="psummary.php?project_id=<?=$project_id?>&amp;id=<?=$par -> get_id() ?>"><?=safe_display($par -> get_display_name()) ?></a></td>
			<td class="py-1.5 px-3 text-right tabular-nums"><?=$statspar -> get_stats_item('first_date') ?></td>
			<td class="py-1.5 px-3 text-right tabular-nums"><?=$statspar -> get_stats_item('last_date') ?></td>
			<td class="py-1.5 px-3 text-right tabular-nums"><?=$statspar -> get_stats_item('days_working')?></td>
			<td class="py-1.5 px-3 text-right tabular-nums"><?=number_style_convert((float) $statspar -> get_stats_item('blocks') * $gproj->get_scale()) ?></td>
		</tr>
	 <?
	    $i++;
	}
} else {
  	trigger_error("Unable to get Participant List",E_USER_ERROR);
}
$totalblocks = number_format($totalblocks, 0);
if ($lo > sizeof($plist)) {
    $btn_back = "<a class=\"text-slate-100 hover:underline\" href=\"$myname?project_id=$project_id&amp;low=$prev_lo&amp;limit=$limit&amp;source=$source\">Back $limit</a>";
} else {
    $btn_back = "&nbsp;";
}

if (sizeof($plist) >= $limit) {
    $btn_fwd = "<a class=\"text-slate-100 hover:underline\" href=\"$myname?project_id=$project_id&amp;low=$next_lo&amp;limit=$limit&amp;source=$source\">Next $limit</a>";
} else {
    $btn_fwd = "&nbsp;";
}
?>
	 <tr>
	  <td class="tfoot text-left"><? echo "$lo-$hi"?></td>
	  <td class="tfoot text-right" colspan="4">Total</td>
	  <td class="tfoot text-right"><?=$totalblocks?></td>
	 </tr>
	 <tr>
	  <td class="tfoot text-left"><?=$btn_back?></td>
	  <td class="tfoot" colspan="4">&nbsp;</td>
	  <td class="tfoot text-right"><?=$btn_fwd?></td>
	 </tr>
     </table>
    </div>
<? include "../templates/footer.inc"; ?>
