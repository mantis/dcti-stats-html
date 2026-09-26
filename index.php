<?
// vi: ts=2 sw=2 tw=120 syntax=php

$title = "Home";

include "etc/global.inc";
include "etc/modules.inc";

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

  <div class="mx-auto max-w-4xl">

    <h1 class="text-4xl font-extrabold leading-none text-slate-900 sm:text-5xl">stats.distributed.net</h1>
    <p class="mt-4 max-w-2xl text-lg text-slate-700 sm:text-xl">
      distributed.net's statistics server, tracking progress on our ongoing distributed computing projects.
    </p>

    <section class="mt-14">
      <h2 class="text-xl font-bold text-slate-900">Welcome!</h2>
      <div class="mt-4 max-w-2xl space-y-3 text-sm text-slate-600">
        <p>
          This server hosts the database which keeps track of statistics for the ongoing distributed.net projects.
        </p>
        <p>
          Running progress and development announcements can be found in
          <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="https://blogs.distributed.net/">staff .plans/blogs</a>
          and the announcements <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="https://www.distributed.net/Discussion#lists">mailing list</a>.
        </p>
        <p>
          For those of you that have a morbid fascination about the statsbox itself, you can see
          <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="https://faq.distributed.net/?file=63">some details</a>
          of the actual box.
        </p>
      </div>
    </section>

    <section class="mt-14">
      <h2 class="text-xl font-bold text-slate-900">Project Statistics</h2>
      <p class="mt-4 text-sm text-slate-600">Here is a list of our projects that currently offer statistics:</p>

      <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
        <table class="w-full min-w-[36rem] text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
              <th class="py-2 px-3" colspan="2">Project</th>
              <th class="py-2 px-3">Description</th>
              <th class="py-2 px-3">Status</th>
            </tr>
          </thead>
          <tbody>
<? foreach ($stats_projects as $p) { ?>
            <tr class="border-b border-slate-100 last:border-0">
              <td class="py-2 px-3 w-10"><img src="/images/<?=$p['icon']?>" alt="" class="size-6"></td>
              <td class="py-2 px-3 font-medium">
                <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="/projects.php?project_id=<?=$p['id']?>"><?=safe_display($p['name'])?></a>
              </td>
              <td class="py-2 px-3 text-slate-600"><?=safe_display($p['desc'])?></td>
              <td class="py-2 px-3">
<? if ($p['status'] == 'active') { ?>
                <span class="inline-block rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">active</span>
<? } else { ?>
                <span class="inline-block rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">completed</span>
<? } ?>
              </td>
            </tr>
<? } ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="mt-14">
      <h2 class="text-xl font-bold text-slate-900">Cross-Project Statistics</h2>
      <ul class="mt-4 list-disc space-y-1 pl-5 text-sm text-slate-700">
        <li><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="http://n1cgi.distributed.net/speed/">Client speed comparison database (live)</a></li>
        <li><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="http://n1cgi.distributed.net/proxymessages.html">Proxy greeting Messages (revised at least hourly)</a></li>
      </ul>
    </section>

  </div>

<?
include "templates/footer.inc";
?>
