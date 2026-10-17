<?php
// Daily totals for a project as JSON.
// project_id == project id
// WARNING: experimental, the schema is subject to change at any time.

include "../etc/global.inc";
include "../etc/project.inc";
include "json.inc";

$qs = "SELECT	to_char(date, 'YYYY-MM-DD') AS date, work_units, participants, teams
	FROM	Daily_Summary
	WHERE	PROJECT_ID = $project_id
	ORDER BY	DATE";
$result = $gdb->query($qs);

$days = array();
while ($row = $gdb->fetch_object($result)) {
  $days[] = array(
    'date' => $row->date,
    'units' => (float) $row->work_units,
    'participants' => (int) $row->participants,
    'teams' => (int) $row->teams,
  );
}

json_out(array('project-id' => (int) $project_id, 'days' => $days));
