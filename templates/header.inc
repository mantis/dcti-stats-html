<?
if (isset($project_id)) {
    $t_project_id = "project_id=$project_id";
} else {
	$t_project_id = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- vi: ts=2 sw=2 tw=120 syntax=php
-->
 <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>stats.distributed.net - <? if (isset($gproj)) { echo $gproj->get_name(); }?> <?=$title?></title>
  <link rel="stylesheet" type="text/css" href="/css/default.css" />
 </head>
 <body class="bg-slate-50 text-slate-900 antialiased">
  <div class="md:flex md:items-start">

   <aside class="bg-slate-950 text-slate-300 md:w-72 md:shrink-0 md:sticky md:top-0 md:h-screen md:overflow-y-auto">
    <div class="p-5 flex items-center gap-3">
     <a href="/" class="shrink-0"><img src="/images/dcti128.jpg" alt="stats.distributed.net" height="56" width="74" class="rounded"></a>
     <div class="min-w-0">
      <div class="text-white font-semibold leading-tight truncate">
       <? if(isset($gproj) && $gproj->get_name() != null) { echo safe_display($gproj->get_name()); } else { echo "stats.distributed.net"; } ?>
      </div>
      <div class="text-xs text-slate-500">distributed.net stats</div>
     </div>
    </div>

    <? if ( isset( $project_id ) && function_exists( 'stats_project_switch_options' ) ) { ?>
    <div class="px-5 pb-5">
     <label for="project-switch" class="block text-xs font-semibold uppercase tracking-wide text-indigo-400 mb-1">Project</label>
     <form method="GET" action="<?=$myname?>" class="flex gap-1.5">
      <? foreach ( $_GET as $t_gk => $t_gv ) { if ( $t_gk != 'project_id' ) { ?>
       <input type="hidden" name="<?=htmlspecialchars($t_gk)?>" value="<?=htmlspecialchars(stripslashes($t_gv))?>">
      <? } } ?>
      <select id="project-switch" name="project_id" onchange="this.form.submit()" class="min-w-0 flex-1 rounded border-0 bg-slate-900 px-2 py-1.5 text-slate-100 focus:ring-2 focus:ring-indigo-500">
       <? foreach ( stats_project_switch_options( isset($project_filter) ? $project_filter : null ) as $t_pid => $t_plabel ) { ?>
        <option value="<?=$t_pid?>" <?=($t_pid == $project_id) ? 'selected' : ''?>><?=safe_display($t_plabel)?></option>
       <? } ?>
      </select>
      <noscript><input class="rounded bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500 cursor-pointer" type="submit" value="Go"></noscript>
     </form>
    </div>
    <? } ?>

    <nav class="px-5 pb-6 space-y-6 text-sm">
     <div>
      <div class="text-xs font-semibold uppercase tracking-wide text-indigo-400 mb-2">Project Stats</div>
      <div class="space-y-1">
       <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/projects.php?<?=$t_project_id?>">Overall Project Stats</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/keyrate.php?<?=$t_project_id?>">Keyrate History</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/misc/participation.php?<?=$t_project_id?>">Participation History</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/misc/platformlist.php?<?=$t_project_id?>&amp;view=tco">CPU/OS Distribution</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/misc/countries.php?<?=$t_project_id?>">Participating Countries</a>
       <? if ( isset( $gproj ) && $gproj->get_prize() <> 0 ) { ?>
        <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/misc/money.php?<?=$t_project_id?>">Distribution of Prize Money</a>
       <? } ?>
      </div>
     </div>

     <div>
      <div class="text-xs font-semibold uppercase tracking-wide text-indigo-400 mb-2">Participant Stats</div>
      <div class="space-y-1 mb-2">
       <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/participant/plist.php?<?=$t_project_id?>&amp;low=1&amp;limit=100">Top 100 Participants Overall</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/participant/plist.php?<?=$t_project_id?>&amp;low=1&amp;limit=100&amp;source=y">Top 100 Participants Yesterday</a>
      </div>
      <form method="GET" name="par-search" id="par-search" action="/participant/psearch.php" class="flex gap-1.5">
       <input type="hidden" name="project_id" value="<?=$project_id?>">
       <input class="min-w-0 flex-1 rounded border-0 bg-slate-900 px-2 py-1 text-slate-100 placeholder-slate-500 focus:ring-2 focus:ring-indigo-500" type="text" name="st" size="16" maxlength="42" placeholder="Search participants">
       <input class="rounded bg-indigo-600 px-3 py-1 font-medium text-white hover:bg-indigo-500 cursor-pointer" type="submit" value="Go">
      </form>
     </div>

     <div>
      <div class="text-xs font-semibold uppercase tracking-wide text-indigo-400 mb-2">Team Stats</div>
      <div class="space-y-1 mb-2">
       <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/team/tlist.php?<?=$t_project_id?>&amp;low=1&amp;limit=100">Top 100 Teams Overall</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/team/tlist.php?<?=$t_project_id?>&amp;low=1&amp;limit=100&amp;source=y">Top 100 Teams Yesterday</a>
      </div>
      <form id="team-search" name="team-search" method="GET" action="/team/tsearch.php" class="flex gap-1.5">
       <input type="hidden" name="project_id" value="<?=$project_id?>">
       <input class="min-w-0 flex-1 rounded border-0 bg-slate-900 px-2 py-1 text-slate-100 placeholder-slate-500 focus:ring-2 focus:ring-indigo-500" type="text" name="st" size="16" maxlength="32" placeholder="Search teams">
       <input class="rounded bg-indigo-600 px-3 py-1 font-medium text-white hover:bg-indigo-500 cursor-pointer" type="submit" value="Go">
      </form>
     </div>

     <div>
      <div class="text-xs font-semibold uppercase tracking-wide text-indigo-400 mb-2">Account</div>
      <div class="space-y-1">
       <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/participant/pedit.php?project_id=<?=$gproj->get_id()?>">Edit your Information</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white" href="/team/newteam1.php">Create a new Team</a>
      </div>
     </div>

     <div class="border-t border-slate-800 pt-4 flex flex-col gap-1 text-xs">
      <a class="font-semibold text-red-400 hover:text-red-300" href="https://bugs.distributed.net/buglist.cgi?product=Stats&bug_status=UNCONFIRMED&bug_status=NEW&bug_status=ASSIGNED&bug_status=REOPENED">Bug Database</a>
      <a class="font-semibold text-red-400 hover:text-red-300" href="https://blogs.distributed.net/">Staff Blogs</a>
      <? if ( isset( $gproj ) && $gproj->get_type() == 'R72' ) { ?>
       <a class="font-semibold text-red-400 hover:text-red-300" href="http://n0cgi.distributed.net/rc5-proxyinfo.html">Current Proxy Rates</a>
      <? } elseif ( isset( $gproj ) && $gproj->get_type() == 'OGRNG' ) { ?>
       <a class="font-semibold text-red-400 hover:text-red-300" href="http://n0cgi.distributed.net/ogr-proxyinfo.html">Current Proxy Rates</a>
      <? } ?>
     </div>
    </nav>
   </aside>

   <div class="min-w-0 flex-1 md:flex md:flex-col md:min-h-screen">
    <div class="border-b border-slate-200 bg-white px-4 py-3">
     <? if (isset($title)) { ?>
      <h1 class="text-lg font-semibold text-slate-900"><?=$title?></h1>
     <? } ?>
    </div>

    <main class="flex-1 px-4 py-6">
