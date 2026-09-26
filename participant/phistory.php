<?
// vi: ts=2 sw=2 tw=120
// $Id: phistory.php,v 1.24 2005/04/01 16:58:42 decibel Exp $
// Variables Passed in url:
// id == Participant ID
// @todo -c Implement .check type of unit name
// @todo -c Implement .scale units

include "../etc/global.inc";
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/participant.php";

if(isset($lockfile)) {
    if(file_exists($lockfile)) {
		trigger_error("Participant History is Unavailable During Update",E_USER_ERROR);
        exit;
    }
}

$gpart = new Participant($gdb, $gproj, $id);

if($gpart->get_retire_to() > 0) {
    header("Location: http://stats.distributed.net/participant/phistory.php?project_id=".$gproj->get_id()."&id=".$gpart->get_retire_to());
    exit();
}
$gpartstats = new ParticipantStats($gdb, $gproj, $id, null);
$history = $gpartstats -> get_stats_history();

$lastupdate = last_update('ec');

$title = "Participant History for ".safe_display($gpart->get_display_name());

include "../templates/header.inc";

?>

<!-- IMPORTANT NOTE TO SCRIPTERS!
This page, like many stats pages, has a version which is far more suitable
for machine parsing.  Please try the url:
http://stats.distributed.net/participant/phistory_raw.php?project_id=$project_id&id=$id
-->
    <p class="text-center"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="psummary.php?project_id=<?=$project_id?>&amp;id=<?=$id?>">View <?=safe_display($gpart->get_display_name())?>'s Participant Summary</a></p>

<?
$phistory_dates = array();
$phistory_values = array();
foreach (array_reverse($history) as $histrow) {
    $phistory_dates[] = strtotime($histrow->stats_date);
    $phistory_values[] = round((float) $histrow->work_units * $gproj->get_scale());
}
?>
<? if (count($phistory_dates) > 1) { ?>
    <div class="mx-auto mt-4 max-w-2xl rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <div id="phistory-chart" class="h-64 w-full text-sm text-slate-500">Loading chart&hellip;</div>
      <noscript><p class="text-xs text-slate-500">Daily history chart needs JavaScript; the table below has the same data.</p></noscript>
    </div>

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

    <div class="mx-auto mt-4 max-w-2xl overflow-hidden rounded-lg border border-slate-200 shadow-sm">
      <table class="w-full text-sm">
      <tr>
       <th class="thead text-left">Date</th>
       <th class="thead text-right"><?=$gproj->get_scaled_unit_name()?></th>
       <th class="thead"></th>
      </tr>
<?

$maxwork_units = (float) 0;
foreach ($history as $histrow)
{
    if($histrow->work_units > $maxwork_units) {
        $maxwork_units = $histrow->work_units;
    }
}

$i = 0;
foreach ($history as $histrow)
{
    $work_units_fmt = number_format($histrow->work_units*$gproj->get_scale(), 0);
    $date_fmt = $histrow->stats_date;
    $width = (int) (((float)$histrow->work_units / $maxwork_units) * 200) + 1;
    ?>
      <tr class="border-b border-slate-100 last:border-0 <?=$i % 2 == 0 ? 'bg-white' : 'bg-slate-50'?>">
      <? if ( $random_stats == 1 ) { ?>
        <!-- Mmmm... random data... -->
      <? } ?>
        <td class="py-1.5 px-3 text-left"><?=$date_fmt?></td>
        <td class="py-1.5 px-3 text-right tabular-nums"><?=$work_units_fmt?></td>
        <td class="py-1.5 px-3"><div class="h-2 rounded-full bg-indigo-600" style="width: <?=$width?>px"></div></td>
      </tr>
<?
	$i++;
	}
?>
      </table>
    </div>
    <p class="mt-4 text-center"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="psummary.php?project_id=<?=$project_id?>&amp;id=<?=$id?>">View <?=safe_display($gpart->get_display_name())?>'s Participant Summary</a></p>
<?include "../templates/footer.inc";
?>
