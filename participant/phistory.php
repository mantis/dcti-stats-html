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
http://stats.distributed.net/json/phistory.php?project_id=<?=$project_id?>&id=<?=$id?>
-->
    <p class="text-center"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="psummary.php?project_id=<?=$project_id?>&amp;id=<?=$id?>">View <?=safe_display($gpart->get_display_name())?>'s Participant Summary</a></p>

<?
$phistory_daily = array();
foreach (array_reverse($history) as $histrow) {
    $phistory_daily[] = array($histrow->stats_date, round((float) $histrow->work_units * $gproj->get_scale()));
}
$unit_name = safe_display($gproj->get_scaled_unit_name());
?>
<? if (count($phistory_daily) > 0) { ?>
    <div class="mx-auto mt-4 max-w-4xl space-y-4">
      <section id="ph-stats" class="grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-slate-300 bg-slate-300 sm:grid-cols-4"></section>

      <section class="space-y-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div class="inline-flex overflow-hidden rounded-md border border-slate-300 text-sm" role="group" aria-label="View">
            <button type="button" id="ph-v-recent" aria-pressed="true" class="px-3 py-1.5 text-slate-600 aria-pressed:bg-slate-100 aria-pressed:font-medium aria-pressed:text-slate-900">Last 90 days</button>
            <button type="button" id="ph-v-all" aria-pressed="false" class="border-l border-slate-300 px-3 py-1.5 text-slate-600 aria-pressed:bg-slate-100 aria-pressed:font-medium aria-pressed:text-slate-900">All time, by month</button>
          </div>
          <div id="ph-scale" hidden class="inline-flex overflow-hidden rounded-md border border-slate-300 text-sm" role="group" aria-label="Scale">
            <button type="button" id="ph-s-log" aria-pressed="true" class="px-3 py-1.5 text-slate-600 aria-pressed:bg-slate-100 aria-pressed:font-medium aria-pressed:text-slate-900">Log scale</button>
            <button type="button" id="ph-s-lin" aria-pressed="false" class="border-l border-slate-300 px-3 py-1.5 text-slate-600 aria-pressed:bg-slate-100 aria-pressed:font-medium aria-pressed:text-slate-900">Linear</button>
          </div>
        </div>
        <div id="ph-chart" class="w-full text-sm text-slate-500">
        </div>
        <p id="ph-note" class="text-xs text-slate-600"></p>
        <noscript><p class="text-xs text-slate-500">Charts need JavaScript; the daily table below has the same data.</p></noscript>
      </section>

      <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="font-medium">Monthly totals</h2>
        <div class="mt-2 max-h-96 overflow-auto">
          <table class="w-full text-sm tabular-nums" id="ph-months">
            <thead><tr class="sticky top-0 bg-white text-slate-600"><th class="py-1 text-left font-medium">Month</th><th class="py-1 text-right font-medium">Days active</th><th class="py-1 text-right font-medium"><?=$unit_name?></th><th class="py-1 text-right font-medium">Avg per active day</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </section>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uplot@1/dist/uPlot.min.css">
    <script src="https://cdn.jsdelivr.net/npm/uplot@1/dist/uPlot.iife.min.js"></script>
    <script>
    (function () {
      var DAILY = <?=json_encode($phistory_daily)?>;
      var UNIT = <?=json_encode($unit_name)?>;
      var DAY = 864e5;
      var MON = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      var BAR = '#2f6fab', AVG = '#c0561a', GRID = '#ddd', MUTED = '#777';
      var $ = function (id) { return document.getElementById(id); };
      var fmt = function (n) { return Math.round(n).toLocaleString('en-US'); };
      var toD = function (s) { return new Date(s + 'T00:00:00Z'); };
      var iso = function (d) { return d.toISOString().slice(0, 10); };
      var label = function (d) { return MON[d.getUTCMonth()] + ' ' + d.getUTCDate() + ', ' + d.getUTCFullYear(); };
      function short(v) {
        if (v >= 1e9) return +(v / 1e9).toFixed(1) + 'G';
        if (v >= 1e6) return +(v / 1e6).toFixed(1) + 'M';
        if (v >= 1e3) return +(v / 1e3).toFixed(1) + 'K';
        return String(v);
      }

      var byDate = {}, total = 0, peak = DAILY[0];
      DAILY.forEach(function (r) { byDate[r[0]] = r[1]; total += r[1]; if (r[1] > peak[1]) peak = r; });
      var last = toD(DAILY[DAILY.length - 1][0]), first = toD(DAILY[0][0]);

      // Last 90 calendar days; days without work are null.
      var RECENT = [], avg7 = [];
      for (var i = 89; i >= 0; i--) {
        var d = new Date(last - i * DAY);
        RECENT.push({d: d, v: byDate[iso(d)] || null});
      }
      RECENT.forEach(function (o) {
        var s = 0;
        for (var k = 0; k < 7; k++) s += byDate[iso(new Date(o.d - k * DAY))] || 0;
        avg7.push(s / 7);
      });

      // One entry per calendar month, first to last.
      var MONTHS = [], mIdx = {};
      for (var y = first.getUTCFullYear(), m = first.getUTCMonth(); y < last.getUTCFullYear() || (y === last.getUTCFullYear() && m <= last.getUTCMonth()); ) {
        var key = y + '-' + ('0' + (m + 1)).slice(-2);
        mIdx[key] = MONTHS.push({y: y, m: m, key: key, v: 0, days: 0}) - 1;
        if (++m === 12) { m = 0; y++; }
      }
      DAILY.forEach(function (r) { var o = MONTHS[mIdx[r[0].slice(0, 7)]]; o.v += r[1]; o.days++; });

      // Summary strip
      var sum = function (a) { return a.reduce(function (t, o) { return t + (o.v || 0); }, 0); };
      var l30 = sum(RECENT.slice(-30)) / 30, p30 = sum(RECENT.slice(-60, -30)) / 30;
      var chg = p30 ? (l30 / p30 - 1) * 100 : null;
      $('ph-stats').innerHTML = [
        ['Latest day', fmt(DAILY[DAILY.length - 1][1]), label(last)],
        ['30-day average', fmt(l30), chg === null ? 'no prior 30 days' : (chg >= 0 ? '+' : '') + chg.toFixed(1) + '% vs prior 30'],
        ['Best day', fmt(peak[1]), label(toD(peak[0]))],
        ['All-time total', fmt(total), DAILY.length + ' days with work']
      ].map(function (c) {
        return '<div class="flex min-w-0 flex-col bg-white px-3 py-3"><span class="text-xs text-slate-600">' + c[0] +
          '</span><span class="text-xl font-medium tabular-nums">' + c[1] + '</span><span class="text-xs text-slate-500">' + c[2] + '</span></div>';
      }).join('');

      // Monthly table, newest first
      document.querySelector('#ph-months tbody').innerHTML = MONTHS.filter(function (o) { return o.days; }).reverse().map(function (o) {
        return '<tr class="border-t border-slate-100"><td class="py-1">' + MON[o.m] + ' ' + o.y + '</td><td class="py-1 text-right">' + o.days +
          '</td><td class="py-1 text-right">' + fmt(o.v) + '</td><td class="py-1 text-right">' + fmt(o.v / o.days) + '</td></tr>';
      }).join('');

      // Chart (uPlot; hover values come from its built-in legend)
      var chartEl = $('ph-chart'), plot = null, state = {view: 'recent', log: true};
      var ts = function (d) { return d.getTime() / 1000; };
      var axis = {stroke: MUTED, grid: {stroke: GRID, width: 1}, ticks: {stroke: GRID, width: 1}};
      var yAxis = {stroke: MUTED, grid: {stroke: GRID, width: 1}, ticks: {stroke: GRID, width: 1}, size: 60,
        values: function (u, vals) { return vals.map(function (v) { return v == null ? null : short(v); }); }};

      function draw() {
        if (plot) plot.destroy();
        var recent = state.view === 'recent', log = !recent && state.log;
        var items = recent ? RECENT : MONTHS;
        // Log scale cannot plot zero, so empty periods stay null.
        var xs = items.map(function (o) { return recent ? ts(o.d) : Date.UTC(o.y, o.m, 1) / 1000; });
        var ys = items.map(function (o) { return o.v || null; });
        var bars = {paths: uPlot.paths.bars({size: [recent ? 0.8 : 0.9, 100]}), points: {show: false}};
        var series = [{label: 'Date', value: function (u, v) {
          if (v == null) return '--';
          var d = new Date(v * 1000);
          return recent ? label(d) : MON[d.getUTCMonth()] + ' ' + d.getUTCFullYear();
        }}, {label: UNIT + (recent ? ' per day' : ' per month'), stroke: BAR, fill: BAR, width: 0,
          value: function (u, v) { return v == null ? 'none' : fmt(v); }}];
        series[1].paths = bars.paths; series[1].points = bars.points;
        var data = [xs, ys];
        if (recent) {
          series.push({label: '7-day avg', stroke: AVG, width: 2, points: {show: false}, value: function (u, v) { return v == null ? '' : fmt(v); }});
          data.push(avg7);
        }
        plot = new uPlot({
          width: chartEl.clientWidth,
          height: chartEl.clientWidth < 520 ? 240 : 300,
          series: series,
          axes: [axis, log ? Object.assign({}, yAxis, {splits: function (u, i, min, max) {
            var s = []; for (var p = 1; p <= max; p *= 10) s.push(p); return s; }}) : yAxis],
          scales: {x: {time: true}, y: log ? {distr: 3, log: 10, range: function (u, min, max) { return [1, Math.pow(10, Math.ceil(Math.log10(max)))]; }} : {range: function (u, min, max) { return [0, max * 1.05]; }}},
          cursor: {points: {show: false}},
        }, data, chartEl);
        $('ph-note').textContent = recent
          ? 'Each bar is one UTC day. Days with no work are left empty.'
          : log ? 'Each gridline is 10× the one below, so small early months and large recent ones both show. Months with no work are left empty.'
                : 'Linear scale shows true proportions; small early months may be too small to see.';
      }

      function press(id, on) { $(id).setAttribute('aria-pressed', on); }
      function setView(v) { state.view = v; press('ph-v-recent', v === 'recent'); press('ph-v-all', v === 'all'); $('ph-scale').hidden = v === 'recent'; draw(); }
      function setScale(l) { state.log = l; press('ph-s-log', l); press('ph-s-lin', !l); draw(); }
      $('ph-v-recent').onclick = function () { setView('recent'); };
      $('ph-v-all').onclick = function () { setView('all'); };
      $('ph-s-log').onclick = function () { setScale(true); };
      $('ph-s-lin').onclick = function () { setScale(false); };
      new ResizeObserver(function () { if (plot) plot.setSize({width: chartEl.clientWidth, height: plot.height}); }).observe(chartEl);
      draw();
    })();
    </script>
<? } ?>

    <details class="mx-auto mt-4 max-w-4xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm" <?=count($phistory_daily) > 0 ? '' : 'open'?>>
      <summary class="cursor-pointer p-4 font-medium">Daily history</summary>
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
    </details>
    <p class="mt-4 text-center"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="psummary.php?project_id=<?=$project_id?>&amp;id=<?=$id?>">View <?=safe_display($gpart->get_display_name())?>'s Participant Summary</a></p>
<?include "../templates/footer.inc";
?>
