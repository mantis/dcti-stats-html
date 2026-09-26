<?
// vi: ts=2 sw=2 tw=120 syntax=php
// $Id: psummary.php,v 1.74 2008/05/02 17:50:15 jlawson Exp $
// Variables Passed in url:
// id == Participant ID

include '../etc/global.inc';
include '../etc/modules.inc';
include '../etc/project.inc';
include '../etc/projectstats.php';
include '../etc/markup.inc';
include '../etc/participant.php';

function par_list($i, $par, $stats, &$totaltoday, &$totaltotal, $proj_scale, $color_a = "", $color_b = "")
{
    global $gproj, $random_stats;
    $parid = 0 + $par->get_id();
    $totaltoday += $stats->get_stats_item("work_today");
    $totaltotal += $stats->get_stats_item("work_total");
    $participant = $par->get_display_name();

    $t_highlight = ($color_a == "row3");
    ?>
    <tr class="border-b border-slate-100 last:border-0 <?=$t_highlight ? 'bg-amber-50' : ($i % 2 == 0 ? 'bg-white' : 'bg-slate-50')?>">
      <? if ( $random_stats == 1 ) { ?>
        <!-- *aol voice* YOU'VE GOT RANDOM! */aol voice* -->
      <? } ?>
      <td class="py-1.5 px-3 text-left whitespace-nowrap"><?echo $stats->get_stats_item("overall_rank") . html_rank_arrow($stats->get_stats_item("overall_change")) ?></td>
      <td class="py-1.5 px-3 text-left"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="psummary.php?project_id=<?=$gproj->get_id()?>&amp;id=<?=$parid?>"><?=safe_display($participant)?></a></td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?echo number_style_convert($stats->get_stats_item("days_working"));?></td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?echo number_style_convert($stats->get_stats_item("work_total") * $proj_scale) ?></td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?echo number_style_convert($stats->get_stats_item("work_today") * $proj_scale) ?></td>
    </tr>
    <?
}

function par_footer($totaltoday, $totaltotal, $proj_scale)
{
    ?>
    <tr class="bg-slate-800 text-slate-100 font-medium">
      <td class="py-1.5 px-3 text-right" colspan="3">Total</td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?echo number_style_convert($totaltotal * $proj_scale)?></td>
      <td class="py-1.5 px-3 text-right tabular-nums"><?echo number_style_convert($totaltoday * $proj_scale)?></td>
    </tr>
    <?
}

// Get overall project stats for various reasons
$gprojstats = $gproj->get_current_stats();

// Get the participant's record from STATS_Participant and store it in $person
$gpart = new Participant($gdb, $gproj, $id);
if($gpart->get_id() == 0) {
    $title = "Participant Summary - Error occured ";
    include "../templates/header.inc";
    include "../templates/error.inc";
    include "../templates/footer.inc";
    exit();
}

// Is this person retired?
if($gpart -> get_retire_to() > 0) {
    header("Location: psummary.php?project_id=$project_id&id=".$gpart -> get_retire_to());
    exit();
}

$gpartstats = new ParticipantStats($gdb, $gproj, $id, null);

$title = "Participant Summary for " . safe_display($gpart -> get_display_name());

$lastupdate = last_update('e');
include "../templates/header.inc";

// Get the participant's best day, store result in $best_day
/* removed for now - killing sybase
$qs = "p_phistory @project_id = $project_id, @id = $id, @sort_field = 'WORK_UNITS', @sort_dir = 'desc'";
sybase_query("set rowcount 0");
$result = sybase_query($qs);
$best_day = sybase_fetch_object($result);
$best_day_units = (float) $best_day->WORK_UNITS;
$best_rate = number_format((($best_day_units*$constant_keys_in_one_block)/(86400))/1000,0);
*/

?>
  <div class="text-center">
    <h1 class="phead mb-4"><?=safe_display($gpart->get_display_name())?>'s stats</h1>

    <? if($gpart -> get_motto() <> '') { ?>
      <p class="italic text-slate-600 mb-4"><?=markup_to_html($gpart->get_motto())?></p>
    <? } ?>

    <? if (!$gpartstats -> are_stats_loaded()) { ?>
    <div class="mx-auto max-w-md rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-600 shadow-sm">
      This participant hasn't submitted any work yet, so there are no stats to show.
    </div>
    <? } else { ?>
    <div class="mx-auto max-w-md overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <table class="w-full text-sm">
        <tr class="border-b border-slate-200">
          <td class="py-2 px-3"></td>
          <td class="phead2 py-2 px-3 text-center">Overall</td>
          <td class="phead2 py-2 px-3 text-center">Yesterday</td>
        </tr>
        <tr class="border-b border-slate-100">
          <td class="phead2 py-1.5 px-3 text-left">Rank:</td>
          <td class="py-1.5 px-3 text-right tabular-nums whitespace-nowrap">
            <?
            echo $gpartstats->get_stats_item('overall_rank') . html_rank_arrow($gpartstats -> get_stats_item('overall_change'));
            ?>
          </td>
          <td class="py-1.5 px-3 text-right tabular-nums whitespace-nowrap">
            <?
            echo $gpartstats->get_stats_item('day_rank') . html_rank_arrow($gpartstats -> get_stats_item('day_change'));
            ?>
          </td>
        </tr>
        <tr class="border-b border-slate-100">
          <td class="phead2 py-1.5 px-3 text-left">Percentile:</td>
          <td class="py-1.5 px-3 text-right tabular-nums">
            <?
            echo number_style_convert( 100 * (1 - ($gpartstats->get_stats_item('overall_rank') / $gprojstats->get_total_emails())), 2 );
            ?>
          </td>
          <td class="py-1.5 px-3 text-right tabular-nums">
            <?
            echo number_style_convert( 100 * (1 - ($gpartstats->get_stats_item('day_rank') / $gprojstats->get_stats_item('participants'))), 2 ) ;
            ?>
          </td>
        </tr>
        <tr class="border-b border-slate-100">
          <td class="phead2 py-1.5 px-3 text-left"><?=$gproj->get_scaled_unit_name()?>:</td>
          <td class="py-1.5 px-3 text-right tabular-nums"><?=number_style_convert($gpartstats->get_stats_item('work_total') * $gproj->get_scale()) ?></td>
          <td class="py-1.5 px-3 text-right tabular-nums"><?=number_style_convert($gpartstats->get_stats_item('work_today') * $gproj->get_scale())?></td>
        </tr>
        <tr class="border-b border-slate-100">
          <td class="phead2 py-1.5 px-3 text-left"><?=$gproj->get_scaled_unit_name()?>/sec:</td>
          <td class="py-1.5 px-3 text-right tabular-nums">
            <? if ($gpartstats->get_stats_item('days_working') > 0) {
                 echo number_style_convert($gpartstats->get_stats_item('work_total') * $gproj->get_scale() / (86400 * $gpartstats->get_stats_item('days_working')), 3);
               }
             ?>
          </td>
          <td class="py-1.5 px-3 text-right tabular-nums">
            <? echo number_style_convert($gpartstats -> get_stats_item('work_today') * $gproj -> get_scale() / 86400, 3);
            ?>
          </td>
        </tr>
        <tr class="border-b border-slate-100">
          <td class="phead2 py-1.5 px-3 text-left"><?=$gproj -> get_unscaled_unit_name()?>:</td>
          <td class="py-1.5 px-3 text-right tabular-nums"><?=number_style_convert($gpartstats -> get_stats_item('work_total')) ?></td>
          <td class="py-1.5 px-3 text-right tabular-nums"><? echo number_style_convert($gpartstats -> get_stats_item('work_today')) ?></td>
        </tr>
        <tr class="border-b border-slate-100">
          <td class="phead2 py-1.5 px-3 text-left"><?=$gproj -> get_unscaled_unit_name()?>/sec:</td>
          <td class="py-1.5 px-3 text-right tabular-nums">
            <? if ($gpartstats->get_stats_item('days_working') > 0) {
                echo number_style_convert($gpartstats->get_stats_item('work_total') / (86400 * $gpartstats->get_stats_item('days_working')), 0);
            }
            ?>
          </td>
          <td class="py-1.5 px-3 text-right tabular-nums">
            <? echo number_style_convert($gpartstats -> get_stats_item('work_today') / 86400, 0);
            ?>
          </td>
        </tr>
        <tr>
          <td class="phead2 py-1.5 px-3 text-left">Time Working:</td>
          <td class="py-1.5 px-3 text-right tabular-nums" colspan="2">
            <? echo number_format($gpartstats -> get_stats_item('days_working')) . " day" . plural($gpartstats -> get_stats_item('days_working'));
            ?>
          </td>
        </tr>
      </table>
    </div>
    <p>

<?
/*
  $pct_of_best = (float) $rs_rank->TODAY * $gproj->get_scale() / $best_day_units;
  if($pct_of_best == 1) {
?>
  <br>
  Yesterday was this participant's best day ever!
  </p>
<?
  } elseif ( $best_day_units > 0 ) {
?>
  </p>
  <p>
  This is  <? echo number_format($pct_of_best*100,0)?>  % of this participant's best day ever, which was
  <br>
   <? echo sybase_date_format_long($best_day->DATE)?> when <? echo number_format($best_day->WORK_UNITS,0)?>
   units were completed.
<? if ($gproj->get_total_units() > 0 ) { ?>
were completed at a rate of <?=$best_rate?> Kkeys/sec.
<? } ?>
  </p><!-- Thanks, Havard! -->
<?
  }
*/
?>
    <?
    $history = $gpartstats -> get_stats_history();
    $phistory_dates = array();
    $phistory_values = array();
    foreach (array_reverse($history) as $histrow) {
        $phistory_dates[] = strtotime($histrow->stats_date);
        $phistory_values[] = round((float) $histrow->work_units * $gproj->get_scale());
    }
    ?>
    <div class="mx-auto mt-4 max-w-2xl rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <div class="mb-2 flex items-baseline justify-between gap-2">
        <span class="text-sm font-semibold text-slate-600">Daily <?=$gproj->get_scaled_unit_name()?></span>
        <a class="whitespace-nowrap text-sm text-indigo-600 hover:text-indigo-800 hover:underline" href="phistory.php?project_id=<?=$project_id?>&amp;id=<?=$id?>">Full history &rarr;</a>
      </div>
      <? if (count($phistory_dates) > 1) { ?>
      <div id="phistory-chart" class="h-64 w-full text-sm text-slate-500">Loading chart&hellip;</div>
      <noscript><p class="text-xs text-slate-500">Daily history chart needs JavaScript; see the full history for a table.</p></noscript>
      <? } else { ?>
      <p class="text-xs text-slate-500">Not enough history yet for a chart.</p>
      <? } ?>
    </div>

    <? if (count($phistory_dates) > 1) { ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uplot@1/dist/uPlot.min.css">
    <script src="https://cdn.jsdelivr.net/npm/uplot@1/dist/uPlot.iife.min.js"></script>
    <script>
    (function () {
      var container = document.getElementById('phistory-chart');
      var dates = <?=json_encode($phistory_dates)?>;
      var values = <?=json_encode($phistory_values)?>;

      container.textContent = '';
      new uPlot({
        width: container.clientWidth,
        height: 256,
        series: [
          {},
          { label: '<?=safe_display($gproj->get_scaled_unit_name())?>', stroke: '#4f46e5', width: 2, points: { show: false } },
        ],
        axes: [
          { stroke: '#475569', grid: { stroke: '#e2e8f0' } },
          { stroke: '#475569', grid: { stroke: '#e2e8f0' } },
        ],
        scales: { x: { time: true } },
      }, [dates, values], container);
    })();
    </script>
    <? } ?>
        <? if (($gproj -> get_type() == 'RC5' or $gproj -> get_type() == 'R72') && ($gpartstats -> get_stats_item('work_today') > 0)) {
            $odds = number_format($gprojstats->get_stats_item('work_units') / $gpartstats -> get_stats_item('work_today'));
            ?>
            <div class="mx-auto max-w-md mt-4">
              <div class="flex items-center gap-3 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3">
                <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
                    <circle cx="12" cy="12" r="9"></circle>
                    <circle cx="12" cy="12" r="5"></circle>
                    <circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"></circle>
                  </svg>
                </div>
                <div>
                  <div class="text-lg font-bold text-indigo-700">1 in <?=$odds?></div>
                  <div class="text-xs text-slate-600">odds this participant finds the key before anyone else does</div>
                </div>
              </div>
            </div>
        <? } ?>
    <div class="mx-auto max-w-3xl overflow-hidden rounded-lg border border-slate-200 shadow-sm">
      <div class="phead2 bg-slate-100 py-2 text-center">Neighbors</div>
      <table class="w-full text-sm">
        <tr>
          <th class="thead text-left">Rank</th>
          <th class="thead text-left">Participant</th>
          <th class="thead text-right">Days</th>
          <th class="thead text-right">Overall <?=$gproj->get_scaled_unit_name()?></th>
          <th class="thead text-right">Yesterday <?=$gproj->get_scaled_unit_name()?></th>
        </tr>
        <?
          $totaltoday = 0;
          $totaltotal = 0;
          $neighbors = $gpart->get_neighbors();
          $numneighbors = count($neighbors);
          for ($i = 0; $i < $numneighbors; $i++) {
              if($gpart->get_id() <> $neighbors[$i]->get_id()) {
                  par_list($i, $neighbors[$i], $neighbors[$i]->get_current_stats(), $totaltoday, $totaltotal, $gproj->get_scale());
              } else {
                  par_list($i, $neighbors[$i], $neighbors[$i]->get_current_stats(), $totaltoday, $totaltotal, $gproj->get_scale(), "row3", "row3");
          }
  }
  par_footer($totaltoday, $totaltotal, $gproj->get_scale());
  ?>
      </table>
    </div>
    <br /><br />
<?
$numfriends = count($gpart->get_friends());
if($numfriends >= 1) {
    ?>
    <div class="mx-auto max-w-3xl overflow-hidden rounded-lg border border-slate-200 shadow-sm">
      <div class="phead2 bg-slate-100 py-2 text-center">Friends</div>
      <table class="w-full text-sm">
        <tr>
          <th class="thead text-left">Rank</th>
          <th class="thead text-left">Participant</th>
          <th class="thead text-right">Days</th>
          <th class="thead text-right">Overall <?=$gproj->get_scaled_unit_name()?></th>
          <th class="thead text-right">Yesterday <?=$gproj->get_scaled_unit_name()?></th>
        </tr>
        <?
    $totaltoday = 0;
    $totaltotal = 0;
    $printed_self = false;
    for ($i = 0; $i < $numfriends; $i++) {
        $par = $gpart->get_friends($i);
        $stats = $par->get_current_stats();
        if($gpartstats->get_stats_item('work_total') >= $stats->get_stats_item('work_total') && !$printed_self) {
            par_list($i, $gpart, $gpartstats, $totaltoday, $totaltotal, $gproj->get_scale(), "row3", "row3");
            $printed_self = true;
        }
        par_list($i, $par, $stats, $totaltoday, $totaltotal, $gproj->get_scale());
    }
    par_footer($totaltoday, $totaltotal, $gproj -> get_scale());
    ?>
      </table>
    </div>
    <?
}
?>
    <? } ?>
    <p class="mt-6">
    <form action="ppass.php" method="post">
        <div>
            <input type="hidden" name="project_id" value="<?=$gproj->get_id()?>">
            <input type="hidden" name="id" value="<?=$id?>">
            <input class="rounded bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 cursor-pointer" type="submit" value="Please email me my password.">
        </div>
    </form>
    </p>
<?
include "../templates/footer.inc";
?>
