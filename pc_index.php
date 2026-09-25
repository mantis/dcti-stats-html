<?
  # vi: ts=2 sw=2 tw=120 syntax=php
  # $Id: pc_index.php,v 1.51 2009/02/27 03:45:10 thejet Exp $

  $title = "Overall Project Stats";

  include "etc/global.inc";
  include "etc/modules.inc";
  include "etc/projectstats.php";
  include "etc/project.inc";
  include "etc/ogrstubspace.php";

  ####
  # Daily summary
  $gprojstats = $gproj->get_current_stats();
	if (!$gprojstats->_state) {
		display_last_update('i');
		?>
   <div class="mx-auto max-w-md rounded-lg border border-slate-200 bg-white p-6 text-center shadow-sm">
     <p class="phead mb-3">Information not available</p>
     <p class="text-sm text-slate-600">Statistical information for <?=safe_display($gproj->get_name())?> is not currently available. A batch update run may not yet
     have been completed, or may still be in progress.</p>
     <p class="mt-2 text-sm text-slate-600">Please check back later, and apologies for any inconvenience!</p>
   </div>
	 <?php
	 exit();
	 }


  // @todo - this returns date in wrong format for lastupdate
  $lastupdate = $gprojstats->get_stats_item('date');
  display_last_update('i');

  $yest_scaled_work_units = number_format( (float) $gprojstats->get_stats_item('work_units') * $gproj->get_scale());
  $yest_unscaled_work_units = number_format( (float) $gprojstats->get_stats_item('work_units'));
  $yest_emails = number_format($gprojstats->get_stats_item('participants'));
  $yest_teams = number_format($gprojstats->get_stats_item('teams'));
  $new_emails = number_format($gprojstats->get_stats_item('participants_new'));
  $new_teams = number_format($gprojstats->get_stats_item('teams_new'));

  ####
  # Total work, time working

  $time_working_raw = $gprojstats->get_time_working();
  $time_working = number_format($gprojstats->get_time_working());
  $tot_unscaled_work_units = number_format( (float) $gprojstats->get_tot_units());
  $tot_scaled_work_units = number_format( (float) $gprojstats->get_tot_units() * $gproj->get_scale());

  $total_emails = number_format($gprojstats->get_total_emails());
  $total_teams = number_format($gprojstats->get_total_teams());

  ####
  # Percent complete
  $tot_unscaled_units_to_search = number_style_convert($gproj->get_total_units());
  $tot_scaled_units_to_search = number_style_convert($gproj->get_total_units() * $gproj->get_scale());
  $total_remaining = $gproj->get_total_units() - $gprojstats->get_tot_units();
  if ( $gproj->get_total_units() > 0 ) {
      $pct_searched = number_format(100*($gprojstats->get_tot_units()/$gproj->get_total_units()),3);
      $bar_width = number_format(300*($gprojstats->get_tot_units()/$gproj->get_total_units()),0);
  }

  ####
  # Overall Rate
  $overall_unscaled_rate = number_format(( ($gprojstats->get_tot_units()) / ($time_working_raw*86400) ),0);
  $overall_scaled_rate = number_format(( ($gprojstats->get_tot_units() * $gproj->get_scale()) / ($time_working_raw*86400) ),0);

  ####
  # Yesterday Rate
  $yest_unscaled_work_units = number_format($gprojstats->get_stats_item('work_units'));
  $yest_scaled_work_units = number_format($gprojstats->get_stats_item('work_units') * $gproj->get_scale());
  if ( $gproj->get_total_units() > 0 ) {
      $yest_pct =  number_format(100*($gprojstats->get_stats_item('work_units') / $gproj->get_total_units()),6);
      $yest_pct_remaining = number_format(100*($gprojstats->get_stats_item('work_units') / ($gproj->get_total_units() - $gprojstats->get_tot_units() + $gprojstats->get_stats_item('work_units'))),6);
  }
  $yest_unscaled_rate = number_format(( ($gprojstats->get_stats_item('work_units')) / (86400) ),0);
  $yest_scaled_rate = number_format(( ($gprojstats->get_stats_item('work_units') * $gproj->get_scale()) / (86400) ),0);

  ####
  # Pace: yesterday's rate vs the lifetime average, and the bar widths to show it
  $overall_unscaled_rate_raw = $gprojstats->get_tot_units() / ($time_working_raw*86400);
  $yest_unscaled_rate_raw = $gprojstats->get_stats_item('work_units') / 86400;
  if ($overall_unscaled_rate_raw > 0) {
      $pace_multiplier = number_format($yest_unscaled_rate_raw / $overall_unscaled_rate_raw, 1);
  } else {
      $pace_multiplier = null;
  }
  $pace_max_rate = max($yest_unscaled_rate_raw, $overall_unscaled_rate_raw, 1);
  $pace_yest_bar_pct = min(100, max(1, 100 * $yest_unscaled_rate_raw / $pace_max_rate));
  $pace_overall_bar_pct = min(100, max(1, 100 * $overall_unscaled_rate_raw / $pace_max_rate));



  $odds = number_format($total_remaining / $gprojstats->get_stats_item('work_units'),0);

  ####
  # Timeline: project "start" (approximated as today minus days actively
  # worked so far - we don't have a stored project launch date), today's
  # position, and a projection of 50%/100% keyspace completion at
  # yesterday's rate. Only meaningful for a finite keyspace with a
  # non-zero rate yesterday.
  $timeline_available = false;
  if ($gproj->get_total_units() > 0 && $yest_unscaled_rate_raw > 0 && $time_working_raw > 0) {
      $timeline_days_to_100 = $total_remaining / $yest_unscaled_rate_raw;
      $timeline_days_to_50 = $timeline_days_to_100 / 2;
      $timeline_today_ts = time();
      $timeline_start_ts = $timeline_today_ts - ($time_working_raw * 86400);
      $timeline_50_ts = $timeline_today_ts + ($timeline_days_to_50 * 86400);
      $timeline_100_ts = $timeline_today_ts + ($timeline_days_to_100 * 86400);
      $timeline_span = $timeline_100_ts - $timeline_start_ts;
      if ($timeline_span > 0) {
          $timeline_available = true;
          $timeline_start_year = date('Y', $timeline_start_ts);
          $timeline_50_year = date('Y', $timeline_50_ts);
          $timeline_100_year = date('Y', $timeline_100_ts);
          $timeline_today_pct = min(100, max(0, 100 * ($timeline_today_ts - $timeline_start_ts) / $timeline_span));
          $timeline_50_pct = min(100, max(0, 100 * ($timeline_50_ts - $timeline_start_ts) / $timeline_span));

          // A handful of evenly-spaced gridline years between start and 100%
          $timeline_year_span = $timeline_100_year - $timeline_start_year;
          $timeline_step = max(1, round($timeline_year_span / 6 / 5) * 5);
          $timeline_gridlines = array();
          for ($y = ceil($timeline_start_year / $timeline_step) * $timeline_step; $y < $timeline_100_year; $y += $timeline_step) {
              $timeline_gridlines[] = array(
                  'year' => $y,
                  'pct' => min(100, max(0, 100 * (mktime(0,0,0,1,1,$y) - $timeline_start_ts) / $timeline_span)),
              );
          }
      }
  }

  ###
  # Percentage for OGR Phase 1 and Phase 2
  if ($gproj->get_type() == 'OGRP2') {
    $ogrdb = new DB("dbname=ogr");
    $ogrstats = $ogrdb->query_first("SELECT * FROM recent_complete WHERE project_id = " . $gproj->get_id());
    if($ogrstats) {
      $ogr_rundate = $ogrstats->rundate;
      $ogrp1_pct_searched = $ogrstats->tot_pct;
    } else {
      $ogrp1_pct_searched = 100;   // if failed, then just assume 100 since we know its done.
    }
    $ogrp1_bar_width = number_format(3*$ogrp1_pct_searched, 0);
    $ogrp1_pct_link = "project/ogr_graph.php?project_id=" . $gproj->get_id();
  }
  if ($gproj->get_id() == 24) {
    $ogrp2_pct_searched = 100;
    $ogrp2_bar_width = number_format(3*$ogrp2_pct_searched, 0);
    $ogrp2_pct_link = "http://n0cgi.distributed.net/statistics/ogr/ogr24p2-percent.png";
  } elseif ($gproj->get_id() == 25) {

    // load up the list of OGR Stubspaces
    $stubspaceList =& OGRStubspace::get_stubspace_list($gproj, $gdb);
    $cnt = count($stubspaceList);
    $totalStubs = 0;
    $stubsDone = 0;
    $stubsVerified = 0;

    for($i = 0; $i < $cnt; $i++)
    {
      $tmpStats =& $stubspaceList[$i]->get_current_stats();
      $totalStubs += $stubspaceList[$i]->get_total_stubs();
      $stubsDone += $tmpStats->get_stubs_done();
      $stubsVerified += $tmpStats->get_stubs_verified();
      unset($tmpStats);
    }

    if ($stubsDone > 0 && $stubsVerified > 0) {
      $ogrp2_pct_searched = round(($stubsDone + $stubsVerified) / ($totalStubs * 2) * 100, 2);
    } else {
      $ogrp2_pct_searched = 0;
    }
    $ogrp2_bar_width = number_format(3*$ogrp2_pct_searched, 0);
    $ogrp2_pct_link = "#ogrfootnote";
  } elseif ($gproj->get_type() =='OGRNG') {

    // load up the list of OGR Stubspaces
    $stubspaceList =& OGRStubspace::get_stubspace_list($gproj, $gdb);
    $cnt = count($stubspaceList);
    $totalStubs = 0;
    $stubsDone = 0;
    $stubsVerified = 0;

    for($i = 0; $i < $cnt; $i++)
    {
      $tmpStats =& $stubspaceList[$i]->get_current_stats();
      $totalStubs += $stubspaceList[$i]->get_total_stubs();
      $stubsDone += $tmpStats->get_stubs_done();
      $stubsVerified += $tmpStats->get_stubs_verified();
      unset($tmpStats);
    }

    if ($stubsDone > 0) {
      $ogrng_pct_searched = round(($stubsDone + $stubsVerified) / ($totalStubs * 2) * 100, 2);
      if($ogrng_pct_searched == 100 && ($stubsDone + $stubsVerified) < ($totalStubs * 2))
      {
        $ogrng_pct_searched = 99.99;
      }
    }
    else
    {
      $ogrng_pct_searched = 0;
    }
  }

?>
   <div class="mx-auto max-w-4xl text-left">

     <p class="text-sm text-slate-500">Figures include every block received as of <?=safe_display($lastupdate)?>.</p>
     <h1 class="mt-2 text-4xl font-extrabold leading-none text-slate-900 sm:text-5xl"><?=safe_display($gproj->get_name())?></h1>
     <p class="mt-4 max-w-2xl text-lg text-slate-700 sm:text-xl">
<? if ($timeline_available) { ?>
       Searching since <?=$timeline_start_year?> and <strong class="font-bold tabular-nums"><?=$pct_searched?>%</strong> of the way through the keyspace.
<? } elseif (isset($pct_searched)) { ?>
       <strong class="font-bold tabular-nums"><?=$pct_searched?>%</strong> of the keyspace has been searched so far.
<? } ?>
<? if ($pace_multiplier !== null) { ?>
       Yesterday ran at <strong class="font-bold tabular-nums"><?=$pace_multiplier?>&times;</strong> the project's lifetime average.
<? } ?>
     </p>

<? if ($timeline_available) { ?>
     <figure class="mt-12">
       <div class="relative h-3 rounded-full bg-slate-200" role="img"
            aria-label="Timeline from <?=$timeline_start_year?>. Today is about <?=number_format($timeline_today_pct,0)?> percent of the way along. At yesterday's pace the key is most likely found around <?=$timeline_50_year?>, and the full keyspace would be searched by <?=$timeline_100_year?>.">
         <div class="absolute inset-y-0 left-0 rounded-full bg-slate-900" style="width: <?=$timeline_today_pct?>%"></div>
         <div class="absolute inset-y-0 opacity-40" style="left: <?=$timeline_today_pct?>%; width: <?=($timeline_50_pct - $timeline_today_pct)?>%; background-image: repeating-linear-gradient(90deg, #0f172a 0 2px, transparent 2px 6px);"></div>
         <div class="absolute -top-2 h-7 w-1 -translate-x-1/2 rounded bg-amber-500" style="left: <?=$timeline_today_pct?>%"></div>
         <div class="absolute top-1/2 size-4 -translate-y-1/2 -translate-x-1/2 rounded-full border-[3px] border-slate-900 bg-white" style="left: <?=$timeline_50_pct?>%"></div>
<? foreach ($timeline_gridlines as $g) { ?>
         <span class="absolute top-full mt-2 hidden -translate-x-1/2 text-xs tabular-nums text-slate-500 md:block" style="left: <?=$g['pct']?>%"><?=$g['year']?></span>
<? } ?>
         <span class="absolute top-full mt-2 -translate-x-1/2 text-xs font-semibold tabular-nums text-slate-900" style="left: <?=$timeline_today_pct?>%">Today</span>
         <span class="absolute top-full mt-2 -translate-x-1/2 text-xs font-semibold tabular-nums text-slate-900" style="left: <?=$timeline_50_pct?>%"><?=$timeline_50_year?></span>
       </div>
       <figcaption class="mt-10 max-w-2xl text-sm text-slate-600">
         At yesterday's pace, the key is most likely to turn up around <strong class="font-semibold text-slate-900"><?=$timeline_50_year?></strong>, when half the remaining keyspace has been searched.
         Searching every last key would take until <?=$timeline_100_year?>. The key could equally be found in tomorrow's blocks.
       </figcaption>
     </figure>
<? } ?>

<? if ($pace_multiplier !== null) { ?>
     <section class="mt-14">
       <h2 class="text-xl font-bold text-slate-900">Pace</h2>
       <div class="mt-4 grid gap-8 md:grid-cols-[1fr_18rem] md:items-end">
         <div>
           <p class="text-5xl font-bold leading-none tabular-nums text-slate-900"><?=$pace_multiplier?>&times;</p>
           <p class="mt-2 text-sm text-slate-500">Yesterday's rate against the lifetime average</p>
           <div class="mt-5 space-y-3 text-sm">
             <div>
               <div class="flex justify-between"><span class="text-slate-600">Yesterday</span><span class="font-semibold tabular-nums text-slate-900"><?=$yest_scaled_rate?> <?=$gproj->get_scaled_unit_name()?>/sec</span></div>
               <div class="mt-1 h-2.5 w-full rounded-full bg-slate-200"><div class="h-2.5 rounded-full bg-indigo-600" style="width: <?=$pace_yest_bar_pct?>%"></div></div>
             </div>
             <div>
               <div class="flex justify-between"><span class="text-slate-600">Lifetime average</span><span class="font-semibold tabular-nums text-slate-900"><?=$overall_scaled_rate?> <?=$gproj->get_scaled_unit_name()?>/sec</span></div>
               <div class="mt-1 h-2.5 w-full rounded-full bg-slate-200"><div class="h-2.5 rounded-full bg-slate-400" style="width: <?=$pace_overall_bar_pct?>%"></div></div>
             </div>
           </div>
         </div>
         <div>
           <div id="pace-sparkline" class="h-16 w-full text-xs text-slate-400">Loading&hellip;</div>
           <noscript><p class="text-xs text-slate-500">Daily rate history needs JavaScript.</p></noscript>
           <div class="mt-2 text-xs text-slate-500">Daily rate, last 30 days</div>
         </div>
       </div>
     </section>

     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uplot@1/dist/uPlot.min.css">
     <script src="https://cdn.jsdelivr.net/npm/uplot@1/dist/uPlot.iife.min.js"></script>
     <script>
     (function () {
       var container = document.getElementById('pace-sparkline');

       fetch('misc/rate_history_data.php?days=30')
         .then(function (r) { return r.json(); })
         .then(function (rows) {
           if (!rows.length) {
             container.textContent = 'No recent history available.';
             return;
           }

           var dates = rows.map(function (r) { return Math.floor(new Date(r.date).getTime() / 1000); });
           var rates = rows.map(function (r) { return r.rate; });

           container.textContent = '';
           new uPlot({
             width: container.clientWidth,
             height: 64,
             cursor: { show: false },
             legend: { show: false },
             series: [
               {},
               { stroke: '#4f46e5', width: 2, points: { show: false } },
             ],
             axes: [ { show: false }, { show: false } ],
             scales: { x: { time: true } },
           }, [dates, rates], container);
         })
         .catch(function () {
           container.textContent = 'Unable to load chart data.';
         });
     })();
     </script>
<? } ?>

     <section class="mt-14">
       <h2 class="text-xl font-bold text-slate-900">Work done</h2>
       <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
         <table class="w-full min-w-[36rem] text-sm">
           <thead>
             <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
               <th class="py-2 px-3"></th>
               <th class="py-2 px-3">Tested so far</th>
<? if ($gproj->get_total_units() > 0 ) { ?>
               <th class="py-2 px-3">Total to search</th>
<? } ?>
               <th class="py-2 px-3">Yesterday</th>
             </tr>
           </thead>
           <tbody>
             <tr class="border-b border-slate-100">
               <th scope="row" class="py-2 px-3 text-left font-medium text-slate-700"><?=$gproj->get_scaled_unit_name()?></th>
               <td class="py-2 px-3 tabular-nums"><?=$tot_scaled_work_units?></td>
<? if ($gproj->get_total_units() > 0 ) { ?>
               <td class="py-2 px-3 tabular-nums"><?=$tot_scaled_units_to_search?></td>
<? } ?>
               <td class="py-2 px-3 tabular-nums"><?=$yest_scaled_work_units?></td>
             </tr>
             <tr class="border-b border-slate-100">
               <th scope="row" class="py-2 px-3 text-left font-medium text-slate-700"><?=$gproj->get_unscaled_unit_name()?></th>
               <td class="py-2 px-3 tabular-nums"><?=$tot_unscaled_work_units?></td>
<? if ($gproj->get_total_units() > 0 ) { ?>
               <td class="py-2 px-3 tabular-nums"><?=$tot_unscaled_units_to_search?></td>
<? } ?>
               <td class="py-2 px-3 tabular-nums"><?=$yest_unscaled_work_units?></td>
             </tr>
             <tr>
               <th scope="row" class="py-2 px-3 text-left font-medium text-slate-700">Rate</th>
               <td class="py-2 px-3 tabular-nums" colspan="<?=($gproj->get_total_units() > 0) ? 2 : 1?>">
                 <?=$overall_scaled_rate?> <?=$gproj->get_scaled_unit_name()?>/sec
                 <span class="block text-xs font-normal text-slate-500"><?=$overall_unscaled_rate?> <?=$gproj->get_unscaled_unit_name()?>/sec average</span>
               </td>
               <td class="py-2 px-3 tabular-nums">
                 <?=$yest_scaled_rate?> <?=$gproj->get_scaled_unit_name()?>/sec
                 <span class="block text-xs font-normal text-slate-500"><?=$yest_unscaled_rate?> <?=$gproj->get_unscaled_unit_name()?>/sec</span>
               </td>
             </tr>
           </tbody>
         </table>
       </div>
<? if ($gproj->get_total_units() > 0 ) { ?>
       <p class="mt-4 max-w-2xl text-sm text-slate-600">
         Yesterday covered <?=$yest_pct?>% of the keyspace (<?=$yest_pct_remaining?>% of what remains).
         That puts the odds of finding the key in the next 24 hours at 1 in <strong class="font-semibold text-slate-900"><?=$odds?></strong>.
       </p>
<? } ?>
     </section>

<? if ($gproj->get_id() == 24 || $gproj->get_id() == 25 || isset($ogrng_pct_searched)) { ?>
     <section class="mt-14">
       <h2 class="text-xl font-bold text-slate-900">Progress Meters</h2>
       <div class="mt-4 max-w-xs space-y-3">
<? if ($gproj->get_id() == 24 || $gproj->get_id() == 25) { ?>
         <div>
           <div class="mb-1 flex justify-between text-xs text-slate-500"><span>Phase 1</span><span><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="<?=$ogrp1_pct_link?>"><?=$ogrp1_pct_searched?>%</a></span></div>
           <div class="h-3 w-full rounded-full bg-slate-200"><div class="h-3 rounded-full bg-indigo-600" style="width: <?=min(100, max(1, $ogrp1_pct_searched))?>%"></div></div>
         </div>
         <div>
           <div class="mb-1 flex justify-between text-xs text-slate-500"><span>Phase 2</span><span>
<?if($gproj->get_id() == 24) {?>
             <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="<?=$ogrp2_pct_link?>"><?=$ogrp2_pct_searched?>%</a>
<?} else {?>
             <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="project/ogr_status.php">~<?=$ogrp2_pct_searched?>%</a> <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="<?=$ogrp2_pct_link?>">**</a>
<?}?>
           </span></div>
           <div class="h-3 w-full rounded-full bg-slate-200"><div class="h-3 rounded-full bg-indigo-600" style="width: <?=min(100, max(1, $ogrp2_pct_searched))?>%"></div></div>
         </div>
<? } elseif (isset($ogrng_pct_searched)) { ?>
         <div>
           <div class="mb-1 flex justify-between text-xs text-slate-500"><span>Percent complete</span><span><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="project/ogr_status.php?project_id=<?=$gproj->get_id()?>">~<?=$ogrng_pct_searched?>%</a></span></div>
           <div class="h-3 w-full rounded-full bg-slate-200"><div class="h-3 rounded-full bg-indigo-600" style="width: <?=min(100, max(1, $ogrng_pct_searched))?>%"></div></div>
         </div>
<? } ?>
       </div>
     </section>
<? } ?>

     <section class="mt-14">
       <h2 class="text-xl font-bold text-slate-900">Who's searching</h2>
       <div class="mt-4 grid gap-8 sm:grid-cols-2">
         <p class="text-lg leading-relaxed text-slate-700">
           <span class="block text-4xl font-bold tabular-nums text-slate-900"><?=$yest_emails?></span>
           participants active yesterday, <?=$new_emails?> of them new, out of <?=$total_emails?> since the project began.
         </p>
         <p class="text-lg leading-relaxed text-slate-700">
           <span class="block text-4xl font-bold tabular-nums text-slate-900"><?=$yest_teams?></span>
           teams submitted work yesterday, out of <?=$total_teams?> registered
           <? if ($new_teams != '0') { ?>(<?=$new_teams?> of them <?=($new_teams==1 ? 'is' : 'are')?> brand new)<? } ?>.
         </p>
       </div>
     </section>

<? if ($gproj->get_id() == 3 || $gproj->get_type() == 'OGRP2') { ?>
     <div class="mt-14 border-t border-slate-200 pt-5 text-sm">
<? if ($gproj->get_id() == 3) { ?>
       <p><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="http://n0cgi.distributed.net/statistics/rc5-56/index.html">Additional Stats</a></p>
<? } ?>
<?if( $gproj->get_type() == 'OGRP2' ){?>
       <a name="ogrfootnote"></a>
       <p class="mt-3 text-xs text-slate-500">
          For more information about OGR Phase 1 and Phase 2, please <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="http://n0cgi.distributed.net/faq/cache/230.html">read our FAQ page</a>.
       </p>
<?}?>
     </div>
<? } ?>

   </div>
