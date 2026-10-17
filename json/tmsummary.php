<?php
// Team summary as JSON.
// team == team id, project_id == project id
// WARNING: experimental, the schema is subject to change at any time.

include "../etc/global.inc";
$random_stats = 0;
include "../etc/modules.inc";
include "../etc/markup.inc";
include "../etc/project.inc";
include "../etc/team.php";
include "../etc/teamstats.php";
include "../etc/projectstats.php";
include "json.inc";

$team = new Team($gdb, $gproj, $tm);
if ($team->get_id() == 0) json_error("That team is not known.", 404);

$stats = $team->get_current_stats();
$nearby = array();
foreach ($team->get_neighbors_full() as $t) {
  $s = $t->get_current_stats();
  $nearby[] = array(
    'id' => (int) $t->get_id(),
    'name' => $t->get_name(),
    'membership' => json_membership($t),
    'stats' => array('date' => $s->get_stats_item("last_date")) + json_team_stats($s, $gproj),
  );
}

json_out(array(
  'id' => (int) $team->get_id(),
  'project' => $gproj->get_name(),
  'project-id' => (int) $gproj->get_id(),
  'date' => last_update('t'),
  'name' => $team->get_name(),
  'logo' => $team->get_logo(),
  'description_html' => markup_to_html($team->get_description()),
  'contact' => $team->get_contact_email(),
  'membership' => json_membership($team),
  'stats' => array('date' => $stats->get_stats_item("last_date")) + json_team_stats($stats, $gproj, true),
  'nearby-teams' => $nearby,
));
