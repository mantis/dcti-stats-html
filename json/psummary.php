<?php
// Participant summary as JSON.
// id == participant id, project_id == project id,
// show_friends / show_neighbors == include those lists
// WARNING: experimental, the schema is subject to change at any time.

include "../etc/global.inc";
$random_stats = 0;
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/participant.php";
include "json.inc";

$gpart = new Participant($gdb, $gproj, $id);
if ($gpart->get_id() == 0) json_error("Participant not found", 404);

// Retired participants redirect to the participant they were merged into.
if ($gpart->get_retire_to() > 0) {
  $q = array('project_id' => $project_id, 'id' => $gpart->get_retire_to());
  foreach (array('show_friends', 'show_neighbors') as $k) if (isset($_GET[$k])) $q[$k] = 1;
  header("Location: psummary.php?" . http_build_query($q));
  exit;
}

$gpartstats = new ParticipantStats($gdb, $gproj, $id, null);
if (!$gpartstats->are_stats_loaded()) json_error("No stats for the user", 404);

function participant_list($list, $proj)
{
  $out = array();
  foreach ($list as $p) {
    $out[] = array(
      'id' => (int) $p->get_id(),
      'name' => $p->get_display_name(),
      'stats' => json_participant_stats($p->get_current_stats(), $proj),
    );
  }
  return $out;
}

$out = array(
  'id' => (int) $id,
  'project' => $gproj->get_name(),
  'project-id' => (int) $gproj->get_id(),
  'last-update' => last_update('ec'),
  'name' => $gpart->get_display_name(),
  'motto' => $gpart->get_motto(),
  'stats' => json_participant_stats($gpartstats, $gproj),
);
if (isset($_GET['show_friends']))   $out['friends']   = participant_list($gpart->get_friends(), $gproj);
if (isset($_GET['show_neighbors'])) $out['neighbors'] = participant_list($gpart->get_neighbors(), $gproj);

json_out($out);
