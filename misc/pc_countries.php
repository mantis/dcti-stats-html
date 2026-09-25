<?

 // $Id: pc_countries.php,v 1.18 2005/05/07 18:00:57 decibel Exp $

 $outname = "countries";

 include "../etc/global.inc";
 include "../etc/modules.inc";
 include "../etc/project.inc";

 $qs = "select distinct code, country, count(country) as recs, sum(work_total)* ".$gproj->get_scale()." as units_total,
		sum(work_today)* ".$gproj->get_scale()." as units_today
	from STATS_participant, STATS_country,email_RANK
	where retire_to IS NULL
		and dem_country IS NOT NULL
		and dem_country = code
		and email_RANK.id = STATS_participant.id
		and email_RANK.project_id = $project_id
	group by code, country ";
 if ("$source" == "y") {
   $qs .= "	order by sum(work_today)* ".$gproj->get_scale()." desc";
 } else {
   $qs .= "	order by sum(work_total)* ".$gproj->get_scale()." desc";
 };

if ($debug == 1) 
  echo "<b>SQL Query:</b><br><pre>$qs</pre><br>";

 display_last_update('e');

 // Fetch every row once into a plain array, used for both the bar chart
 // and the table below (avoids querying/seeking twice).
 $country = $gdb->query($qs);
 $countries = $gdb->num_rows();
 $country_rows = array();
 for ($i = 0; $i < $countries; $i++) {
   $gdb->data_seek($i);
   $par = $gdb->fetch_object($country);
   $country_rows[] = array(
     'code'    => $par->code,
     'country' => $par->country,
     'recs'    => (int) $par->recs,
     'total'   => (float) $par->units_total,
     'today'   => (float) $par->units_today,
   );
 }

 // Top 10 countries by the metric this view is sorted on, plus an "Other"
 // bucket for the remainder - a bar chart doesn't read well much past that.
 $bar_metric = ($source == 'y') ? 'today' : 'total';
 $bar_top = array_slice($country_rows, 0, 10);
 $bar_other = 0;
 foreach (array_slice($country_rows, 10) as $r) {
   $bar_other += $r[$bar_metric];
 }
 $bar_max = 0;
 foreach ($bar_top as $r) {
   $bar_max = max($bar_max, $r[$bar_metric]);
 }
 $bar_max = max($bar_max, $bar_other, 1);

 if (count($bar_top) > 0) {
?>
   <div class="mx-auto max-w-3xl overflow-hidden rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <div class="text-sm font-semibold text-slate-700">Top countries by <?=$gproj->get_scaled_unit_name()?> (<?=($source == 'y') ? 'yesterday' : 'overall'?>)</div>
    <div class="mt-3 space-y-2">
<?
   foreach ($bar_top as $r) {
     $icofn = "/images/icons/flags/{$r['code']}.gif";
     if (!file_exists("..$icofn")) {
       $icofn = "/images/icons/flags/unknown.gif";
     }
     $pct = min(100, max(1, 100 * $r[$bar_metric] / $bar_max));
?>
     <div class="flex items-center gap-2 text-sm">
      <span class="inline-flex w-32 shrink-0 items-center gap-1.5 truncate"><img src="<?=$icofn?>" alt="<?=safe_display($r['code'])?>" height=14 width=14> <?=safe_display($r['country'])?></span>
      <span class="h-3 flex-1 rounded-full bg-slate-200"><span class="block h-3 rounded-full bg-indigo-600" style="width: <?=$pct?>%"></span></span>
      <span class="w-24 shrink-0 text-right tabular-nums text-slate-600"><?=number_style_convert($r[$bar_metric])?></span>
     </div>
<?
   }
   if ($bar_other > 0) {
     $pct = min(100, max(1, 100 * $bar_other / $bar_max));
?>
     <div class="flex items-center gap-2 text-sm">
      <span class="w-32 shrink-0 truncate text-slate-500">Other</span>
      <span class="h-3 flex-1 rounded-full bg-slate-200"><span class="block h-3 rounded-full bg-slate-400" style="width: <?=$pct?>%"></span></span>
      <span class="w-24 shrink-0 text-right tabular-nums text-slate-600"><?=number_style_convert($bar_other)?></span>
     </div>
<?
   }
?>
    </div>
   </div>
<?
 }

 print "
	 <div class=\"mx-auto mt-4 max-w-3xl overflow-hidden rounded-lg border border-slate-200 shadow-sm\">
	  <table class=\"w-full text-sm\">
	   <tr>
            <th class=\"thead\" colspan=2>&nbsp;</th>";
 if ($source == 'y') {
   print "
	    <th class=\"thead text-center\" colspan=2><a class=\"text-slate-100 hover:underline\" href=\"$outname.php?project_id=$project_id&source=o\">Overall</a></th>
	    <th class=\"thead text-center\" colspan=2>Yesterday</th>";
 } else {
   print "
	    <th class=\"thead text-center\" colspan=2>Overall</th>
	    <th class=\"thead text-center\" colspan=2><a class=\"text-slate-100 hover:underline\" href=\"$outname.php?project_id=$project_id&source=y\">Yesterday</a></th>";
 }
?>
           </tr>
	   <tr>
	    <th class="thead text-left">Nationality</th>
	    <th class="thead text-center">People</th>
	    <th class="thead text-center"><?=$gproj->get_scaled_unit_name()?></th>
	    <th class="thead text-center"><?=$gproj->get_scaled_unit_name()?>/Person</th>
	    <th class="thead text-center"><?=$gproj->get_scaled_unit_name()?></th>
	    <th class="thead text-center"><?=$gproj->get_scaled_unit_name()?>/Person</th>
	   </tr>
<?

 foreach ($country_rows as $i => $par) {
   $t_row_bg = ($i % 2 == 0) ? 'bg-white' : 'bg-slate-50';
   print "<tr class=\"border-b border-slate-100 last:border-0 $t_row_bg\">";
   $recs = $par['recs'];
   $units_total = $par['total'];
   $units_today = $par['today'];
   $f_recs = number_style_convert($recs);
   $f_units_total = number_style_convert($units_total);
   $f_blockavg_total = number_style_convert($units_total/$recs);
   $f_units_today = number_style_convert($units_today);
   $f_blockavg_today = number_style_convert($units_today/$recs);
   $icofn = "/images/icons/flags/{$par['code']}.gif";
   if(!file_exists("..$icofn")) {
     $icofn = "/images/icons/flags/unknown.gif";
   }
?>
	    <td class="py-1.5 px-3 text-left"><span class="inline-flex items-center gap-1.5"><img src="<?=$icofn?>" alt="<?=safe_display($par['code'])?>" height=14 width=14> <?=safe_display($par['country'])?></span></td>
	    <td class="py-1.5 px-3 text-right tabular-nums"><?=$f_recs?></td>
	    <td class="py-1.5 px-3 text-right tabular-nums"><?=$f_units_total?></td>
	    <td class="py-1.5 px-3 text-right tabular-nums"><?=$f_blockavg_total?></td>
	    <td class="py-1.5 px-3 text-right tabular-nums"><?=$f_units_today?></td>
	    <td class="py-1.5 px-3 text-right tabular-nums"><?=$f_blockavg_today?></td>
	   </tr>
<?
 }
?>
	</table>
	</div>
	<p class="mx-auto mt-4 max-w-3xl text-xs text-slate-500">
	 Note: Nationalities listed on this page are only reflective of
	 those participants who have designated their nationality when
	 <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="/participant/pedit.php">editing</a> their participant information.
	 No attempt has been made to derive nationalities from participant
	 email addresses.
	</p>

