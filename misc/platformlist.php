<?

# $Id: platformlist.php,v 1.27 2005/07/22 16:55:04 decibel Exp $

$hour = 3;
$now = getdate();
if ($now['hours'] >= 0 and $now['hours'] < $hour) {
	$now = time();
} else {
	$now = time() + 86400;
}

Header("Cache-Control: must-revalidate");
Header("Expires: " . gmdate("D, d M Y", $now) . " $hour:00 GMT");

/// Variables passed in url
//   source == "y" for yseterday, all other values ignored.
//   view == display and sort order (t = total blocks, c = cpu, o = os, v = version)
//           page will show those columns in view, sorted in view's order
//   		(DEFAULT value is 'c', set in project.inc

 include "../etc/global.inc";
 include "../etc/modules.inc";
 include "../etc/project.inc";

 $lastupdate = last_update('e');
 $title = "CPU Participation";

 include "../templates/header.inc";

 $selstr = "select";
 $frostr = "from Platform_Summary p";
 $whestr = "where p.PROJECT_ID = $project_id";
 $grostr = "group by";
 $ordstr = "order by";
 $show_yesterday = 0;
 $show_total = 0;
 if("$source" == "y") {
   $whestr .= " and WORK_TODAY > 0";
 }

 for($i=0; $i < strlen($view); $i++) {
   $ch = substr($view,$i,1);
   if($ch == 'c') $fieldname = "p.CPU";
   if($ch == 'o') $fieldname = "p.OS";
   if($ch == 'v') $fieldname = "p.ver";
   if($ch == 'y') {
     $fieldname = "sum(p.WORK_TODAY)* ".$gproj->get_scale()." as yesterday";
     $show_yesterday = 1;
   }
   if($ch == 't') {
     $fieldname = "sum(p.WORK_TOTAL)* ".$gproj->get_scale()." as total";
     $show_total = 1;
   }

   $selstr .= " $fieldname,";
   if($ch != 'y' and $ch != 't') $grostr .= " $fieldname,";
 }


 $selstr .= " min(FIRST_DATE) as first, max(LAST_DATE) as last,";

 for($i=0; $i < strlen($view); $i++) {
   $ch = substr($view,$i,1);
   if($ch == 'c') {
     $selstr = "$selstr min(coalesce(c.name, 'Unknown')) AS cpuname, min(coalesce(c.image, 'unknown.gif')) AS cpuimage,";
     $frostr = "$frostr LEFT JOIN STATS_cpu c ON ( c.cpu = p.cpu )";
     $ordstr = "$ordstr cpuname,";
   }
   if($ch == 'o') {
     $selstr = "$selstr min(coalesce(o.name, 'Unknown')) AS osname, min(coalesce(o.image, 'unknown.gif')) AS osimage,";
     $frostr = "$frostr LEFT JOIN STATS_os o ON ( o.os = p.os )";
     $ordstr = "$ordstr osname,";
   }
   if($ch == 'v') {
     $selstr = "$selstr p.ver,";
     $ordstr = "$ordstr p.ver,";
   }
   if($ch == 'y') {
     $ordstr = "$ordstr yesterday desc,";
   }
   if($ch == 't') {
     $ordstr = "$ordstr total desc,";
   }
 }

 $selstr = substr($selstr,0,strlen($selstr)-1);
 $grostr = substr($grostr,0,strlen($grostr)-1);
 $ordstr = substr($ordstr,0,strlen($ordstr)-1);

 $QSlist = "$selstr $frostr $whestr $grostr $ordstr";
 $result = $gdb->query($QSlist);


 $rows = $gdb->num_rows();

 // --- CPU / OS share breakdown, built from the (cpu,os) rows already
 // fetched above - no extra query needed. Uses whichever value column the
 // requested $view produced (lifetime total takes priority over yesterday).
 $chart_value_field = $show_total ? 'total' : ($show_yesterday ? 'yesterday' : null);
 $cpu_breakdown = array();
 $os_breakdown = array();
 if ($chart_value_field !== null) {
   for ($i = 0; $i < $rows; $i++) {
     $gdb->data_seek($i);
     $par = $gdb->fetch_object();
     $value = (float) $par->$chart_value_field;
     if (strpos($view, 'c') !== false) {
       $cpuname = trim($par->cpuname);
       $cpu_breakdown[$cpuname] = (isset($cpu_breakdown[$cpuname]) ? $cpu_breakdown[$cpuname] : 0) + $value;
     }
     if (strpos($view, 'o') !== false) {
       $osname = trim($par->osname);
       $os_breakdown[$osname] = (isset($os_breakdown[$osname]) ? $os_breakdown[$osname] : 0) + $value;
     }
   }
 }

 // Validated categorical palette (dataviz skill default, light-mode slots
 // 1-6): fixed hue order, never cycled. A 7th+ category folds into "Other".
 $stats_share_colors = array('#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#008300');
 $stats_share_other_color = '#64748b';

 function stats_share_ink($hex) {
   $hex = ltrim($hex, '#');
   $r = hexdec(substr($hex, 0, 2));
   $g = hexdec(substr($hex, 2, 2));
   $b = hexdec(substr($hex, 4, 2));
   $luma = 0.299 * $r + 0.587 * $g + 0.114 * $b;
   return $luma > 150 ? '#0f172a' : '#ffffff';
 }

 function render_share_bar($label, $breakdown) {
   global $stats_share_colors, $stats_share_other_color;
   if (empty($breakdown)) return;
   $total = array_sum($breakdown);
   if ($total <= 0) return;

   arsort($breakdown);
   $top = array_slice($breakdown, 0, 6, true);
   $other = $total - array_sum($top);

   $segments = array();
   $i = 0;
   foreach ($top as $name => $value) {
     $segments[] = array('name' => $name, 'value' => $value, 'color' => $stats_share_colors[$i]);
     $i++;
   }
   if ($other > 0) {
     $segments[] = array('name' => 'Other', 'value' => $other, 'color' => $stats_share_other_color);
   }
   $n = count($segments);

   print '<div>';
   print '<h3 class="text-sm font-semibold text-slate-900">' . htmlspecialchars($label) . '</h3>';
   print '<div class="mt-2 flex h-6 w-full overflow-hidden rounded-full" style="gap:2px">';
   foreach ($segments as $idx => $seg) {
     $pct = 100 * $seg['value'] / $total;
     if ($n == 1) {
       $radius = 'border-radius:9999px';
     } else if ($idx == 0) {
       $radius = 'border-radius:9999px 0 0 9999px';
     } else if ($idx == $n - 1) {
       $radius = 'border-radius:0 9999px 9999px 0';
     } else {
       $radius = 'border-radius:0';
     }
     $ink = stats_share_ink($seg['color']);
     print '<div class="flex items-center justify-center overflow-hidden whitespace-nowrap px-1 text-[11px] font-semibold" style="width:' . $pct . '%; background-color:' . $seg['color'] . '; ' . $radius . '; color:' . $ink . '" title="' . htmlspecialchars($seg['name']) . ': ' . number_format($pct, 1) . '%">';
     // A bare percentage inside a segment doesn't say what it's a percentage
     // of, so only label a segment inline once it's wide enough to carry its
     // name alongside the number - otherwise skip the label and let the
     // legend (and hover title) name it instead.
     if ($pct >= (strlen($seg['name']) + 6) * 2) {
       print htmlspecialchars($seg['name']) . '&nbsp;' . number_format($pct, 0) . '%';
     }
     print '</div>';
   }
   print '</div>';

   print '<table class="mt-3 w-full text-xs"><tbody>';
   foreach ($segments as $seg) {
     $pct = 100 * $seg['value'] / $total;
     print '<tr class="border-b border-slate-100 last:border-0">';
     print '<td class="py-1 pr-2"><span class="inline-flex items-center gap-1.5"><span class="inline-block size-2.5 shrink-0 rounded-full" style="background-color:' . $seg['color'] . '"></span><span class="text-slate-700">' . htmlspecialchars($seg['name']) . '</span></span></td>';
     print '<td class="py-1 text-right text-slate-500 tabular-nums">' . number_format($pct, 1) . '%</td>';
     print '</tr>';
   }
   print '</tbody></table>';
   print '</div>';
 }

 if (!empty($cpu_breakdown) || !empty($os_breakdown)) {
   $chart_caption = $show_total ? 'lifetime units' : 'yesterday';
   print '<div class="mx-auto mb-8 max-w-4xl grid gap-8 sm:grid-cols-2">';
   render_share_bar('CPU share (' . $chart_caption . ')', $cpu_breakdown);
   render_share_bar('OS share (' . $chart_caption . ')', $os_breakdown);
   print '</div>';
 }

 # Total number of columns in table, not counting yesterday or total columns. Start at 2 to account for first and last.
 $cols = 3;
 print "
    <div class=\"mx-auto max-w-4xl overflow-hidden rounded-lg border border-slate-200 shadow-sm\">
     <table class=\"w-full text-sm\">
      <tr>";
 for($i=0; $i < strlen($view); $i++) {
   $ch = substr($view,$i,1);
   if($ch == 'c') {
     print "<th class=\"thead text-left\">CPU</th>";
     $cols++;
   }
   if($ch == 'o') {
     print "<th class=\"thead text-left\">OS</th>";
     $cols++;
   }
   if($ch == 'v') {
     print "<th class=\"thead text-left\">Version</th>";
     $cols++;
   }
 }
?>
       <th class="thead text-right">First Unit</th>
       <th class="thead text-right">Last Unit</th>
<?
 if($show_yesterday){ print "<th class=\"thead text-right\">Yesterday</th>";}
 if($show_total) { print "<th class=\"thead text-right\">Total ".$gproj->get_scaled_unit_name()."</th>";}
 print '</tr>';
 $total_yesterday = 0;
 $total_overall = 0;
 for ($i = 0; $i<$rows; $i++) {
   $t_row_bg = ($i % 2 == 0) ? 'bg-white' : 'bg-slate-50';
?>
<tr class="border-b border-slate-100 last:border-0 <?=$t_row_bg?>">
<?
 $gdb->data_seek($i);
 $par = $gdb->fetch_object();

 $decimal_places=0;
 $firstd = $par->first;
 $lastd = $par->last;

 for($j=0; $j < strlen($view); $j++) {
   $ch = substr($view,$j,1);
   if($ch == 'c') print "<td class=\"py-1.5 px-3 text-left\"><span class=\"inline-flex items-center gap-1.5\"><img alt=\"\" height=\"14\" width=\"14\" src=\"/images/icons/cpu/$par->cpuimage\"> $par->cpuname</span></td>\n";
   if($ch == 'o') print "<td class=\"py-1.5 px-3 text-left\"><span class=\"inline-flex items-center gap-1.5\"><img alt=\"\" height=\"14\" width=\"14\" src=\"/images/icons/os/$par->osimage\"> $par->osname</span></td>\n";
   if($ch == 'v') print "<td class=\"py-1.5 px-3 text-left\">$par->ver</td>\n";
 }

 print "
 	<td class=\"py-1.5 px-3 text-right tabular-nums\">$firstd</td>
 	<td class=\"py-1.5 px-3 text-right tabular-nums\">$lastd</td>
 ";

 if($show_yesterday) {
   print "<td class=\"py-1.5 px-3 text-right tabular-nums\">" . number_style_convert( (float) $par->yesterday ) . "</td>\n";
   $total_yesterday += (float) $par->yesterday ;
 }
 if($show_total) {
   print "<td class=\"py-1.5 px-3 text-right tabular-nums\">" . number_style_convert( (float) $par->total ) . "</td>\n";
   $total_overall += (float) $par->total ;
 }
 print "</tr>";
}

 if($show_yesterday or $show_total) {

   $padding = (int) $cols - 1;
   print "
   <tr>
	<td class=\"tfoot text-right\" colspan=\"$padding\">Total</td>";

   if ($show_yesterday) {
     print "<td class=\"tfoot text-right\">" . number_style_convert($total_yesterday, 0) . "</td>\n";
   }
   if ($show_total) {
     print "<td class=\"tfoot text-right\">" . number_style_convert($total_overall, 0) . "</td>\n";
   }
 }

   print "
   </tr>
  </table>
 </div>
";
   include "../templates/footer.inc";
?>
