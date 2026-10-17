<?php
// Team member listing as JSON.
// team == team id, low == first record (1-based, default 1), limit == page size (default 100)
// pass == team password if the team requires one, source == y for yesterday's contributors only
// project_id == project to include statistics for
// WARNING: experimental, the schema is subject to change at any time.

include "../etc/global.inc";
$random_stats = 0;
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/team.php";
include "../etc/teamstats.php";
include "../etc/participant.php";
include "json.inc";

$low = max(1, (int) ($_GET['low'] ?? 1));
$limit = (int) ($_GET['limit'] ?? 100);
if ($limit < 1) $limit = 100;

$team = new Team($gdb, $gproj, $tm);
if ($team->get_id() == 0) json_error("That team is not known.", 404);
$tm = $team->get_id();

$show = $team->get_show_members();
if ($show == "NO") json_error("This team does not list its members.", 403);
if ($show == "PAS" && $pass != $team->get_show_password()) {
  json_error($pass == "" ? "Password required" : "Invalid password", 403);
}

$source = (($_GET['source'] ?? '') == 'y') ? 'y' : 'o';
$members = Participant::get_team_list($tm, $source, $low, $limit, $rows, $gdb, $gproj);

$list = array();
foreach ($members as $i => $m) {
  $s = $m->get_current_stats();
  $list[] = array(
    'id' => (int) $m->get_id(),
    'name' => $m->get_display_name(),
    'rank-overall' => $low + $i,
    'stats' => array(
      'rank-project' => array('value' => $s->get_stats_item("rank"), 'change' => $s->get_stats_item("rank_change")),
      'work-overall' => array('value' => round((float) $s->get_stats_item("work_total") * $gproj->get_scale(), 0), 'unit' => $gproj->get_scaled_unit_name()),
      'work-day'     => array('value' => round((float) $s->get_stats_item("work_today") * $gproj->get_scale(), 0), 'unit' => $gproj->get_scaled_unit_name()),
      'work-first'   => array('value' => $s->get_stats_item("first_date")),
      'work-last'    => array('value' => $s->get_stats_item("last_date")),
    ),
  );
}

json_out(array(
  'id' => (int) $tm,
  'project' => $gproj->get_name(),
  'project-id' => (int) $gproj->get_id(),
  'date' => last_update('m'),
  'members' => array('start' => $low, 'limit' => $limit, 'total' => $rows, 'list' => $list),
));
