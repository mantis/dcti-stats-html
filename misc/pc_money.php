<?
# $Id: pc_money.php,v 1.7 2004/07/16 20:45:27 decibel Exp $
# vi: ts=2 sw=2 tw=120 syntax=php

$title = "Disposition of Prize Money";

include "../etc/global.inc";
include "../etc/modules.inc";
include "../etc/project.inc";

if ( $gproj->get_prize() == 0 ) {
  print "
    <div class=\"mx-auto max-w-md rounded-lg border border-slate-200 bg-white p-6 text-center text-sm text-slate-600 shadow-sm\">
      Sorry, there's no prize for this contest.
    </div>";
 exit;
}

$fmt_prize = number_style_convert($gproj->get_prize());
$fmt_a = number_style_convert(($gproj->get_prize())*0.6);
$fmt_b = number_style_convert(($gproj->get_prize())*0.1);
$fmt_c = number_style_convert(($gproj->get_prize())*0.1);
$fmt_d = number_style_convert(($gproj->get_prize())*0.2);

$qs = "SELECT m.nonprofit, m.people, m.votes, n.name, n.url, n.comments
FROM (  SELECT p.nonprofit, count(*) AS people, sum(r.work_total * 1) AS votes
	FROM stats_participant p, email_rank r
	WHERE p.id = r.id
	AND r.project_id = 8
	GROUP BY p.nonprofit
	) m, stats_nonprofit n
WHERE m.nonprofit = n.nonprofit
ORDER BY votes desc";

$result = $gdb->query($qs);
$rows = $gdb->num_rows();
$par = $gdb->fetch_object();
$np_winner = $par->nonprofit;
$nm_winner = $par->name;

if( $np_winner <> 1 ) {
 $np_runnerup = 1;
 $nm_runnerup = "distributed.net";
} else {
 $gdb->data_seek(1);
 $par = $gdb->fetch_object($result);
 $np_runnerup = $par->nonprofit;
 $nm_runnerup = $par->name;
}

print "
  <div class=\"mx-auto max-w-2xl space-y-8\">

    <div class=\"overflow-hidden rounded-lg border border-slate-200 shadow-sm\">
      <div class=\"phead2 bg-slate-100 py-2 text-center\">The US\$$fmt_prize prize will be divided as follows</div>
      <table class=\"w-full text-sm\">
        <tr class=\"border-b border-slate-100\">
          <td class=\"py-1.5 px-3\">$nm_winner</td>
          <td class=\"py-1.5 px-3 text-right tabular-nums\">US\$$fmt_a</td>
        </tr>
        <tr class=\"border-b border-slate-100\">
          <td class=\"py-1.5 px-3\">The individual who finds the key</td>
          <td class=\"py-1.5 px-3 text-right tabular-nums\">US\$$fmt_b</td>
        </tr>
        <tr class=\"border-b border-slate-100\">
          <td class=\"py-1.5 px-3\">The winning individual's team</td>
          <td class=\"py-1.5 px-3 text-right tabular-nums\">US\$$fmt_c</td>
        </tr>
        <tr>
          <td class=\"py-1.5 px-3\">$nm_runnerup</td>
          <td class=\"py-1.5 px-3 text-right tabular-nums\">US\$$fmt_d</td>
        </tr>
      </table>
    </div>

    <div>
      <h2 class=\"mb-2 text-lg font-semibold text-slate-900\">How this is determined</h2>
      <div class=\"space-y-3 text-sm leading-relaxed text-slate-700\">
        <p>Each individual participant involved in the effort is allowed to select which non-profit he or she would prefer to send the prize money. Each person will be given one vote per block submitted. The non-profit that receives the most votes will be given 60% of the prize money.</p>
        <p>Of the remaining 40%, 10% will be given to the individual who finds the key and 10% will be given to that person's team. If the winning individual is NOT on a team when they find the key, they will receive the entire 20%.</p>
        <p>The 20% remaining will be retained by distributed.net to fund additional projects.</p>
        <p>If distributed.net is, however, selected as the recipient non-profit, the 20% that would have otherwise gone to distributed.net will instead be given to the runner-up non-profit.</p>
        <p>Is everyone confused yet?</p>
        <p>To choose your non-profit, simply edit your participant information. If you know your email address and stats password, you can do so <a class=\"text-indigo-600 hover:text-indigo-800 hover:underline\" href=\"/pedit.php3\">here and now</a>.</p>
      </div>
    </div>

    <div class=\"overflow-hidden rounded-lg border border-slate-200 shadow-sm\">
      <div class=\"phead2 bg-slate-100 py-2 text-center\">Current Voting Scoreboard</div>
      <table class=\"w-full text-sm\">
        <tr>
          <th class=\"thead text-left\">Non-Profit</th>
          <th class=\"thead text-right\">Votes</th>
          <th class=\"thead text-right\">Supporters</th>
        </tr>";
 for( $i=0; $i<$rows; $i++) {
   $gdb->data_seek($i);
   $par = $gdb->fetch_object();
   $votes = number_style_convert($par->votes);
   $people = number_style_convert($par->people);
   $t_row_bg = ($i % 2 == 0) ? 'bg-white' : 'bg-slate-50';
   print "
        <tr class=\"border-b border-slate-100 last:border-0 $t_row_bg\">
          <td class=\"py-1.5 px-3\">$par->name</td>
          <td class=\"py-1.5 px-3 text-right tabular-nums\">$votes</td>
          <td class=\"py-1.5 px-3 text-right tabular-nums\">$people</td>
        </tr>";
 }
 print "
      </table>
    </div>

    <div class=\"overflow-hidden rounded-lg border border-slate-200 shadow-sm\">
      <div class=\"phead2 bg-slate-100 py-2 text-center\">Non-Profit Information and Links</div>
      <div class=\"divide-y divide-slate-100\">";
 for( $i=$rows-1; $i>=0; $i--) {
   $gdb->data_seek($i);
   $par = $gdb->fetch_object($result);
   print "
        <div class=\"p-3\">
          <a class=\"font-semibold text-indigo-600 hover:text-indigo-800 hover:underline\" href=\"$par->url\">$par->name</a>
          <p class=\"mt-1 text-sm text-slate-700\">$par->comments</p>
        </div>";
 }

?>
      </div>
    </div>

  </div>
