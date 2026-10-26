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
 <body class="bg-[#eee] text-slate-900 antialiased">
  <div class="lg:flex lg:items-start">

   <aside class="bg-[linear-gradient(to_right,#999_59px,#ccc_59px)] text-slate-800 lg:w-72 lg:shrink-0 lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto">
    <div class="flex items-center py-5 pr-5">
     <a href="/" class="shrink-0"><img src="/images/Web-logo.png" alt="stats.distributed.net" height="105" width="140" class="block"></a>
     <button type="button" aria-controls="site-menu" aria-expanded="false" aria-label="Menu" onclick="var m=document.getElementById('site-menu');m.classList.toggle('hidden');this.setAttribute('aria-expanded',!m.classList.contains('hidden'))" class="lg:hidden ml-auto shrink-0 rounded p-2 text-slate-800 hover:bg-slate-200 hover:text-black">
      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
     </button>
    </div>
    <div class="min-w-0 pb-5 pl-[70px] pr-5">
     <div class="min-w-0">
      <div class="flex min-w-0 items-center gap-2">
       <div class="min-w-0 truncate text-black font-semibold leading-tight">
        <? if(isset($gproj) && $gproj->get_name() != null) { echo safe_display($gproj->get_name()); } else { echo "stats.distributed.net"; } ?>
       </div>
<? if ( isset( $project_id ) && function_exists( 'stats_project_is_completed' ) && stats_project_is_completed( $project_id ) ) { ?>
       <span class="shrink-0 rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-medium text-slate-600">Completed</span>
<? } ?>
      </div>
      <a href="https://www.distributed.net/" class="text-xs text-slate-600 hover:text-black">distributed.net &uarr;</a>
     </div>
    </div>

    <div id="site-menu" class="hidden lg:block">
    <? if ( isset( $project_id ) && function_exists( 'stats_project_switch_options' ) ) { ?>
    <div class="pl-[70px] pr-5 pb-5">
     <label for="project-switch" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1">Project</label>
     <form method="GET" action="<?=$myname?>" class="flex gap-1.5">
      <? foreach ( $_GET as $t_gk => $t_gv ) { if ( $t_gk != 'project_id' ) { ?>
       <input type="hidden" name="<?=htmlspecialchars($t_gk)?>" value="<?=htmlspecialchars(stripslashes($t_gv))?>">
      <? } } ?>
      <select id="project-switch" name="project_id" onchange="this.form.submit()" class="min-w-0 flex-1 rounded border-0 bg-white px-2 py-1.5 text-slate-800 focus:ring-2 focus:ring-indigo-500">
       <? foreach ( stats_project_switch_options( isset($project_filter) ? $project_filter : null ) as $t_pid => $t_plabel ) { ?>
        <option value="<?=$t_pid?>" <?=($t_pid == $project_id) ? 'selected' : ''?>><?=safe_display($t_plabel)?></option>
       <? } ?>
      </select>
      <noscript><input class="rounded bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500 cursor-pointer" type="submit" value="Go"></noscript>
     </form>
    </div>
    <? } ?>

    <nav class="pl-[70px] pr-5 pb-6 space-y-6 text-sm">
     <div>
      <div class="text-xs font-semibold uppercase tracking-wide text-slate-600 mb-2">Project Stats</div>
      <div class="space-y-1">
       <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/projects.php?<?=$t_project_id?>">Overall Project Stats</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/keyrate.php?<?=$t_project_id?>">Keyrate History</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/misc/participation.php?<?=$t_project_id?>">Participation History</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/misc/platformlist.php?<?=$t_project_id?>&amp;view=tco">CPU/OS Distribution</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/misc/countries.php?<?=$t_project_id?>">Participating Countries</a>
       <? if ( isset( $gproj ) && $gproj->get_prize() <> 0 ) { ?>
        <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/misc/money.php?<?=$t_project_id?>">Distribution of Prize Money</a>
       <? } ?>
      </div>
     </div>

     <div>
      <div class="text-xs font-semibold uppercase tracking-wide text-slate-600 mb-2">Participant Stats</div>
      <div class="space-y-1 mb-2">
       <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/participant/plist.php?<?=$t_project_id?>&amp;low=1&amp;limit=100">Top 100 Participants Overall</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/participant/plist.php?<?=$t_project_id?>&amp;low=1&amp;limit=100&amp;source=y">Top 100 Participants Yesterday</a>
      </div>
      <form method="GET" name="par-search" id="par-search" action="/participant/psearch.php" class="flex gap-1.5">
       <input type="hidden" name="project_id" value="<?=$project_id ?? ""?>">
       <input class="min-w-0 flex-1 rounded border-0 bg-white px-2 py-1 text-slate-800 placeholder-slate-500 focus:ring-2 focus:ring-indigo-500" type="text" name="st" size="16" maxlength="42" placeholder="Search participants">
       <input class="rounded bg-indigo-600 px-3 py-1 font-medium text-white hover:bg-indigo-500 cursor-pointer" type="submit" value="Go">
      </form>
     </div>

     <div>
      <div class="text-xs font-semibold uppercase tracking-wide text-slate-600 mb-2">Team Stats</div>
      <div class="space-y-1 mb-2">
       <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/team/tlist.php?<?=$t_project_id?>&amp;low=1&amp;limit=100">Top 100 Teams Overall</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/team/tlist.php?<?=$t_project_id?>&amp;low=1&amp;limit=100&amp;source=y">Top 100 Teams Yesterday</a>
      </div>
      <form id="team-search" name="team-search" method="GET" action="/team/tsearch.php" class="flex gap-1.5">
       <input type="hidden" name="project_id" value="<?=$project_id ?? ""?>">
       <input class="min-w-0 flex-1 rounded border-0 bg-white px-2 py-1 text-slate-800 placeholder-slate-500 focus:ring-2 focus:ring-indigo-500" type="text" name="st" size="16" maxlength="32" placeholder="Search teams">
       <input class="rounded bg-indigo-600 px-3 py-1 font-medium text-white hover:bg-indigo-500 cursor-pointer" type="submit" value="Go">
      </form>
     </div>

     <div>
      <div class="text-xs font-semibold uppercase tracking-wide text-slate-600 mb-2">Account</div>
      <div class="space-y-1">
       <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/participant/pedit.php?project_id=<?=isset($gproj) ? $gproj->get_id() : ""?>">Edit your Information</a>
       <a class="block rounded px-2 py-1 -mx-2 text-slate-800 hover:bg-slate-200 hover:text-black" href="/team/newteam1.php">Create a new Team</a>
      </div>
     </div>
    </nav>
    </div>
   </aside>

   <div class="min-w-0 flex-1 lg:flex lg:flex-col lg:min-h-screen">
    <div class="border-b border-slate-200 bg-[#bbb] px-4 py-3">
     <? if (isset($title)) { ?>
      <h1 class="text-lg font-semibold text-slate-900"><?=$title?></h1>
     <? } ?>
    </div>

<? if ( isset( $project_id ) && function_exists( 'stats_project_is_completed' ) && stats_project_is_completed( $project_id ) ) { ?>
    <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-center text-xs text-slate-600">
     This project is <span class="font-semibold text-slate-900">completed</span> &mdash; its keyspace has been fully searched. Historical statistics remain available below.
    </div>
<? } ?>

    <main class="flex-1 px-4 py-6">
