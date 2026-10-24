<?
// vi: ts=2 sw=2 tw=120 syntax=php

$title = "Home";

include "etc/global.inc";
include "etc/modules.inc";
include "etc/project.inc";

$stats_projects = array(
    array('id' => 8,   'name' => 'RC5-72',       'icon' => 'cowhead.gif', 'desc' => "RSA Labs' 72bit RC5 Encryption Challenge",                  'status' => 'active'),
    array('id' => 24,  'name' => 'OGR-24',       'icon' => 'roohead.gif', 'desc' => 'Optimal Golomb Rulers',                                     'status' => 'completed'),
    array('id' => 25,  'name' => 'OGR-25',       'icon' => 'roohead.gif', 'desc' => 'Optimal Golomb Rulers',                                     'status' => 'completed'),
    array('id' => 26,  'name' => 'OGR-26',       'icon' => 'roohead.gif', 'desc' => 'Optimal Golomb Rulers',                                     'status' => 'completed'),
    array('id' => 27,  'name' => 'OGR-27',       'icon' => 'roohead.gif', 'desc' => 'Optimal Golomb Rulers',                                     'status' => 'completed'),
    array('id' => 28,  'name' => 'OGR-28',       'icon' => 'roohead.gif', 'desc' => 'Optimal Golomb Rulers',                                     'status' => 'completed'),
    array('id' => 3,   'name' => 'RC5-56',       'icon' => 'cowhead.gif', 'desc' => "RSA Labs' 56bit RC5 Encryption Challenge",                  'status' => 'completed'),
    array('id' => 5,   'name' => 'RC5-64',       'icon' => 'cowhead.gif', 'desc' => "RSA Labs' 64bit RC5 Encryption Challenge",                  'status' => 'completed'),
    array('id' => 205, 'name' => 'RC5-64 (all)', 'icon' => 'cowhead.gif', 'desc' => 'RC5-64, plus the work done after the key was found',        'status' => 'completed'),
);

include "templates/header.inc";
?>

  <div class="mx-auto max-w-5xl">

    <header>
      <h1 class="text-4xl font-extrabold leading-none text-slate-900 sm:text-5xl">stats.distributed.net</h1>
      <p class="mt-4 max-w-2xl text-lg text-slate-700 sm:text-xl">
        distributed.net's statistics server, tracking progress on our ongoing distributed computing projects.
      </p>
    </header>

<? foreach ($stats_projects as $p) { if ($p['status'] == 'active') { ?>
    <a href="/projects.php?project_id=<?=$p['id']?>" class="group mt-10 flex items-center gap-6 rounded-2xl border border-indigo-200 bg-white p-6 shadow-sm transition hover:border-indigo-400 hover:shadow-md">
      <img src="/images/<?=$p['icon']?>" alt="" class="h-24 w-auto max-w-none shrink-0 [image-rendering:pixelated]">
      <div class="min-w-0">
        <span class="inline-block rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">active</span>
        <h2 class="mt-2 text-2xl font-bold text-slate-900 group-hover:text-indigo-700"><?=safe_display($p['name'])?></h2>
        <p class="mt-1 text-slate-600"><?=safe_display($p['desc'])?></p>
      </div>
      <span class="ml-auto hidden text-sm font-medium text-indigo-600 sm:block">View statistics &rarr;</span>
    </a>
<? } } ?>

    <section class="mt-14">
      <h2 class="text-xl font-bold text-slate-900">Completed projects</h2>
      <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
<? foreach ($stats_projects as $p) { if ($p['status'] != 'active') { ?>
        <a href="/projects.php?project_id=<?=$p['id']?>" class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
          <img src="/images/<?=$p['icon']?>" alt="" class="h-14 w-auto max-w-none shrink-0 [image-rendering:pixelated]">
          <div class="min-w-0">
            <div class="font-semibold text-slate-900"><?=safe_display($p['name'])?></div>
            <div class="text-sm text-slate-500"><?=safe_display($p['desc'])?></div>
          </div>
        </a>
<? } } ?>
      </div>
    </section>

    <section class="mt-14">
      <h2 class="text-xl font-bold text-slate-900">More from distributed.net</h2>
      <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <a class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300" href="https://cgi.distributed.net/speed/">
          <div class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">CPU benchmark rankings</div>
          <div class="text-sm text-slate-500">Client speed comparison database (live)</div>
        </a>
        <a class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300" href="https://blogs.distributed.net/">
          <div class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">Staff .plans &amp; blogs</div>
          <div class="text-sm text-slate-500">Running progress and development announcements</div>
        </a>
        <a class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300" href="https://www.distributed.net/Discussion#lists">
          <div class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">Announcements mailing list</div>
          <div class="text-sm text-slate-500">Project news by email</div>
        </a>
        <a class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300" href="https://faq.distributed.net/?file=63">
          <div class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">About the statsbox</div>
          <div class="text-sm text-slate-500">Details of the machine behind these stats</div>
        </a>
        <a class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300" href="http://n1cgi.distributed.net/proxymessages.html">
          <div class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">Proxy greeting messages</div>
          <div class="text-sm text-slate-500">Revised at least hourly</div>
        </a>
      </div>
    </section>

  </div>

<?
include "templates/footer.inc";
?>
