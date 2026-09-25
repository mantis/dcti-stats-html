<?
// vi: ts=2 sw=2 tw=120 syntax=php
// Keyrate history, rendered client-side with uPlot from our own
// daily_summary data (misc/rate_history_data.php). Used to embed a fixed
// set of pre-rendered PNGs from an external server (bovine.statsdev...)
// for a handful of hardcoded project ids; now works for any project with
// daily_summary history.

include "etc/global.inc";
include "etc/modules.inc";
include "etc/project.inc";

$title = "Keyrate History";

include "templates/header.inc";
?>

<div class="mx-auto max-w-4xl">
  <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap gap-2" id="keyrate-ranges">
      <button type="button" class="keyrate-range-btn rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 hover:bg-indigo-100" data-days="7">1 Week</button>
      <button type="button" class="keyrate-range-btn rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 hover:bg-indigo-100" data-days="30">1 Month</button>
      <button type="button" class="keyrate-range-btn rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 hover:bg-indigo-100" data-days="365">1 Year</button>
      <button type="button" class="keyrate-range-btn rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 hover:bg-indigo-100" data-days="1095">3 Years</button>
      <button type="button" class="keyrate-range-btn rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 hover:bg-indigo-100" data-days="3650">10 Years</button>
    </div>
    <div id="keyrate-chart" class="mt-4 h-96 w-full text-sm text-slate-500">Loading chart&hellip;</div>
    <noscript>
      <p class="mt-4 text-sm text-slate-600">This chart needs JavaScript. The underlying data is available as JSON at
        <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="misc/rate_history_data.php?project_id=<?=$project_id?>&amp;days=365">rate_history_data.php?project_id=<?=$project_id?>&amp;days=365</a>.
      </p>
    </noscript>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uplot@1/dist/uPlot.min.css">
<script src="https://cdn.jsdelivr.net/npm/uplot@1/dist/uPlot.iife.min.js"></script>
<script>
(function () {
  var container = document.getElementById('keyrate-chart');
  var buttons = document.querySelectorAll('.keyrate-range-btn');
  var plot = null;

  function setActive(btn) {
    buttons.forEach(function (b) {
      b.classList.toggle('bg-indigo-600', b === btn);
      b.classList.toggle('text-white', b === btn);
      b.classList.toggle('bg-indigo-50', b !== btn);
      b.classList.toggle('text-indigo-700', b !== btn);
    });
  }

  function load(days) {
    fetch('misc/rate_history_data.php?project_id=<?=$project_id?>&days=' + days)
      .then(function (r) { return r.json(); })
      .then(function (rows) {
        if (!rows.length) {
          container.textContent = 'No keyrate history available for this project.';
          return;
        }

        var dates = rows.map(function (r) { return Math.floor(new Date(r.date).getTime() / 1000); });
        var rates = rows.map(function (r) { return r.rate; });

        if (plot) {
          plot.destroy();
        }
        container.textContent = '';
        plot = new uPlot({
          width: container.clientWidth,
          height: 360,
          series: [
            {},
            { label: '<?=safe_display($gproj->get_scaled_unit_name())?>/sec', stroke: '#4f46e5', width: 2 },
          ],
          axes: [
            { stroke: '#475569', grid: { stroke: '#e2e8f0' } },
            { stroke: '#475569', grid: { stroke: '#e2e8f0' } },
          ],
        }, [dates, rates], container);
      })
      .catch(function () {
        container.textContent = 'Unable to load chart data.';
      });
  }

  buttons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      setActive(btn);
      load(btn.dataset.days);
    });
  });

  setActive(buttons[1]);
  load(30);
})();
</script>

<? include "templates/footer.inc"; ?>
