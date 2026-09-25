<?
// vi: ts=2 sw=2 tw=120 syntax=php
// OGR Phase 1/2 completion history, rendered client-side with uPlot.
// Replaces the old jpgraph-generated static PNG (see git history for
// project/ogr_graph.php's jpgraph version and etc/jpgraph/, removed).
// Data comes from project/ogr_graph_data.php.

include "../etc/global.inc";
include "../etc/modules.inc";
include "../etc/project.inc";

$title = "OGR-$project_id Completion Statistics";

include "../templates/header.inc";
?>

<div class="mx-auto max-w-3xl">
  <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <div id="ogr-graph" class="h-80 w-full text-sm text-slate-500">Loading chart&hellip;</div>
    <noscript>
      <p class="text-sm text-slate-600">This chart needs JavaScript. The underlying data is available as JSON at
        <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="ogr_graph_data.php?project_id=<?=$project_id?>">ogr_graph_data.php?project_id=<?=$project_id?></a>.
      </p>
    </noscript>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uplot@1/dist/uPlot.min.css">
<script src="https://cdn.jsdelivr.net/npm/uplot@1/dist/uPlot.iife.min.js"></script>
<script>
(function () {
  var container = document.getElementById('ogr-graph');

  fetch('ogr_graph_data.php?project_id=<?=$project_id?>')
    .then(function (r) { return r.json(); })
    .then(function (rows) {
      if (!rows.length) {
        container.textContent = 'No completion history available for this project yet.';
        return;
      }

      var dates = rows.map(function (r) { return Math.floor(new Date(r.date).getTime() / 1000); });
      var pass1 = rows.map(function (r) { return r.pass1 / 1e6; });
      var pass2 = rows.map(function (r) { return r.pass2 / 1e6; });
      var pctComplete = rows.map(function (r) {
        return r.count > 0 ? ((r.pass1 / r.count + r.pass2 / r.count) / 2) * 100 : 0;
      });

      container.textContent = '';
      new uPlot({
        width: container.clientWidth,
        height: 320,
        scales: { pct: { range: [0, 100] } },
        series: [
          {},
          { label: 'Pass 1 stubs (M)', stroke: '#4f46e5', width: 2 },
          { label: 'Pass 2 stubs (M)', stroke: '#dc2626', width: 2 },
          { label: '% complete', stroke: '#d97706', width: 2, scale: 'pct' },
        ],
        axes: [
          {},
          { label: 'Stubs (millions)', stroke: '#475569', grid: { stroke: '#e2e8f0' } },
          { label: '% complete', scale: 'pct', side: 1, stroke: '#475569', grid: { show: false } },
        ],
      }, [dates, pass1, pass2, pctComplete], container);
    })
    .catch(function () {
      container.textContent = 'Unable to load chart data.';
    });
})();
</script>

<? include "../templates/footer.inc"; ?>
