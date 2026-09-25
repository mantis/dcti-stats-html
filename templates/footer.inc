    </main>

    <footer class="mt-auto border-t border-slate-200 bg-white">
<? if( isset( $lastupdate ) ) { ?>
     <div class="border-b border-slate-100 bg-amber-50 text-center text-xs text-amber-700 py-1.5 px-4">
<? if( $lastupdate <> "" ) { ?>
      Data shown reflects all blocks received as of <?=$lastupdate?> at 23:59 UTC. Current time is <?=gmstrftime('%d-%b-%Y %T')?>.
<? } else { ?>
      Update currently in progress...
<? } ?>
     </div>
<? } ?>
     <div class="px-4 py-6 text-center text-xs text-slate-400">
      Built with <a class="hover:underline hover:text-slate-500" href="http://www.php.net">PHP</a> and <a class="hover:underline hover:text-slate-500" href="http://www.postgresql.org/">PostgreSQL</a>
     </div>
    </footer>
   </div>
  </div>
<?
/* if($debug >= DEBUG_TIME ) {
?>
    <p style="text-align: center"> Page generated in <?=timer_current()?> seconds </p>
<?
*/
    if($debug >= DEBUG_SHOW_QUERY) {
        echo '<table border="1">';
        $queries = $gdb->get_queries();
        echo "<tr><td>Total queries: " . count( $queries ) . "</td></tr>\n";
        foreach ( $gdb->get_queries() as $query ) {
            echo "<tr><td>" . $query[0] . "</td></tr>\n";
        }
        echo "</table>\n";
    }
    if($debug >= DEBUG_TRACE) { echo xdebug_dump_function_trace(); }
    if($debug >= DEBUG_PROFILE) { echo xdebug_dump_function_profile(); }
    if($log_timing == 1) {
        if ( !isset( $other ) ) {
            if ( isset( $source ) ) {
                $other = $source;
            } else {
                $other = '';
            }
        }
        log_timing($gdb, $project_id, $other);
    }
//} 

?>

<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-327688-4";
urchinTracker();
</script>

 </body>
</html>
