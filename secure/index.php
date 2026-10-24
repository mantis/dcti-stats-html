<?php
$title = "Home";
include "../etc/config.inc";
include "etc/header.inc";
?>
<div class="mx-auto max-w-3xl px-4 py-6">
  <h1 class="text-4xl font-extrabold leading-none text-slate-900 sm:text-5xl">Stats Administration</h1>
  <p class="mt-4 text-lg text-slate-700">
    Welcome to stats-slash-secure! Select from the menu on the left!
  </p>
<?php if ($GLOBALS['readonly_secure'] != 0) { ?>
  <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">
    <div class="font-semibold">Read only</div>
    <p class="mt-1 text-sm">The site is currently read-only. As a result no changes are possible at the moment.</p>
    <p class="mt-1 text-sm">Sorry for any inconvenience caused.</p>
  </div>
<?php } ?>
</div>
<?php
include "etc/footer.inc";
?>
