<?php
$t_link = 'block rounded px-2 py-1 -mx-2 text-slate-100 hover:bg-slate-900 hover:text-white';
$t_head = 'text-xs font-semibold uppercase tracking-wide text-indigo-400 mb-2';
$t_input = 'min-w-0 flex-1 rounded border-0 bg-slate-900 px-2 py-1 text-slate-100 placeholder-slate-500 focus:ring-2 focus:ring-indigo-500';
$t_btn = 'rounded bg-indigo-600 px-3 py-1 font-medium text-white hover:bg-indigo-500 cursor-pointer';
?>
<!DOCTYPE html>
<html lang="en">
 <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Stats Administration<?php if (isset($title)) { echo ' - ' . $title; } ?></title>
  <link rel="stylesheet" type="text/css" href="/css/default.css" />
 </head>
 <body class="bg-slate-50 text-slate-900 antialiased">
  <div class="lg:flex lg:items-start">

   <aside class="bg-slate-950 text-slate-300 lg:w-72 lg:shrink-0 lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto">
    <div class="p-5 flex items-center gap-3">
     <a href="/secure/" class="shrink-0"><img src="/images/dcti128.jpg" alt="stats.distributed.net" height="56" width="74" class="rounded"></a>
     <div class="min-w-0">
      <div class="truncate text-white font-semibold leading-tight">Stats Administration</div>
      <a href="/" class="text-xs text-slate-500 hover:text-slate-300">stats.distributed.net &uarr;</a>
     </div>
     <button type="button" aria-controls="site-menu" aria-expanded="false" aria-label="Menu" onclick="var m=document.getElementById('site-menu');m.classList.toggle('hidden');this.setAttribute('aria-expanded',!m.classList.contains('hidden'))" class="lg:hidden ml-auto shrink-0 rounded p-2 text-slate-300 hover:bg-slate-900 hover:text-white">
      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
     </button>
    </div>

    <div id="site-menu" class="hidden lg:block">
    <nav class="px-5 pb-6 space-y-6 text-sm">
     <div>
      <div class="<?=$t_head?>">General</div>
      <div class="space-y-1">
       <a class="<?=$t_link?>" href="/secure/phpinfo.php">Server Info</a>
       <a class="<?=$t_link?>" href="/secure/project_logs.php?project_id=8">RC5-72 Log Tail</a>
      </div>
     </div>

     <div>
      <div class="<?=$t_head?>">Participant Search</div>
      <form method="GET" name="par-search" id="par-search" action="/secure/psearch.php" class="flex gap-1.5">
       <input class="<?=$t_input?>" type="text" name="st" size="16" maxlength="42" placeholder="Search participants">
       <input class="<?=$t_btn?>" type="submit" value="Go">
      </form>
     </div>

     <div>
      <div class="<?=$t_head?>">Team Search</div>
      <form id="team-search" name="team-search" method="GET" action="/secure/tsearch.php" class="flex gap-1.5">
       <input class="<?=$t_input?>" type="text" name="st" size="16" maxlength="32" placeholder="Search teams">
       <input class="<?=$t_btn?>" type="submit" value="Go">
      </form>
     </div>
    </nav>
    </div>
   </aside>

   <div class="min-w-0 flex-1 lg:flex lg:flex-col lg:min-h-screen">
    <div class="border-b border-slate-200 bg-white px-4 py-3 flex flex-wrap items-baseline justify-between gap-2">
     <h1 class="text-lg font-semibold text-slate-900"><?php echo isset($title) ? $title : 'Stats Administration'; ?></h1>
     <span class="text-xs text-slate-500">Logged in as <?php echo htmlspecialchars($_SERVER['REMOTE_USER'] ?? ''); ?></span>
    </div>

    <main class="flex-1 px-4 py-6">
