<?php
$title = "Server Info";
include "../etc/global.inc";
include "../etc/project.inc";
include "etc/header.inc";

$apache = function_exists('apache_get_version') ? apache_get_version() : $_SERVER['SERVER_SOFTWARE'];
$pg = $gdb->query_first('SHOW server_version');

$rows = array(
	'Apache'     => $apache,
	'PHP'        => PHP_VERSION . ' (' . PHP_SAPI . ')',
	'PostgreSQL' => $pg->server_version,
	'OS'         => php_uname('s') . ' ' . php_uname('r'),
	'Hostname'   => php_uname('n'),
);
?>
<div class="mx-auto max-w-3xl px-4 py-6">
  <h1 class="text-4xl font-extrabold leading-none text-slate-900 sm:text-5xl">Server Info</h1>
  <table class="mt-6 w-full border-collapse overflow-hidden rounded-xl border border-slate-200 bg-white text-sm shadow-sm">
<?php foreach ($rows as $name => $value) { ?>
    <tr class="border-b border-slate-200 last:border-0">
      <th class="w-40 bg-slate-100 px-4 py-2 text-left font-semibold text-slate-700"><?php echo htmlspecialchars($name); ?></th>
      <td class="px-4 py-2 font-mono text-slate-900"><?php echo htmlspecialchars($value); ?></td>
    </tr>
<?php } ?>
  </table>
</div>
<?php
include "etc/footer.inc";
?>
