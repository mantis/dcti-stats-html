<?php
$project_id = (int) ($_REQUEST['project_id'] ?? 0);
switch ($project_id) {
    case 8:
        $title = 'RC5-72 Log Tail';
        $file = 'r72.log';
        break;
    case 24:
    case 25:
        $title = 'OGR-P2 Log Tail';
        $file = 'ogrp2.log';
        break;
    default:
        $title = 'Log Tail';
        $file = null;
}
$log = $file ? shell_exec('tail -50 ~statproc/log/' . $file) : null;

include "etc/header.inc";
?>
<div class="mx-auto max-w-5xl px-4 py-6">
  <h1 class="text-4xl font-extrabold leading-none text-slate-900 sm:text-5xl"><?php echo htmlspecialchars($title); ?></h1>
<?php if ($file) { ?>
  <p class="mt-2 text-sm text-slate-500">Last 50 lines of <code class="rounded bg-slate-100 px-1.5 py-0.5"><?php echo htmlspecialchars($file); ?></code></p>
  <pre class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-slate-950 p-4 font-mono text-xs leading-relaxed text-slate-100 shadow-sm"><?php echo htmlspecialchars((string) $log); ?></pre>
<?php } else { ?>
  <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">Unknown project.</div>
<?php } ?>
</div>
<?php
include "etc/footer.inc";
?>
