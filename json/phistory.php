<?php
// Participant work history as JSON.
// id == participant id, project_id == project id
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
  header("Location: phistory.php?" . http_build_query(array('project_id' => $project_id, 'id' => $gpart->get_retire_to())));
  exit;
}

$gpartstats = new ParticipantStats($gdb, $gproj, $id, null);
$history = array();
foreach ($gpartstats->get_stats_history() as $row) {
  $history[] = array(
    'date' => $row->stats_date,
    'units' => round($row->work_units * $gproj->get_scale(), 0),
  );
}

json_out(array(
  'id' => (int) $id,
  'name' => $gpart->get_display_name(),
  'project-id' => (int) $gproj->get_id(),
  'last-update' => last_update('ec'),
  'history' => $history,
));
