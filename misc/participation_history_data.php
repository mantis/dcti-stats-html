<?php
// Data endpoint for misc/participation.php's chart. Returns daily active
// participant/team counts as JSON: [{date, participants, teams}, ...]
// oldest first, pulled from daily_summary (same table
// etc/projectstats.php::ProjectStats::loadCurrent() and
// misc/rate_history_data.php use).

include "../etc/global.inc";
include "../etc/db_pgsql.php";
include "../etc/project.inc";

header('Content-Type: application/json');

$days = (int)($_GET['days'] ?? 30);
if ($days < 1 || $days > 3653) {
    $days = 30;
}

$query_id = $gdb->query_bound(
    "SELECT date, participants, teams FROM daily_summary WHERE project_id = $1 ORDER BY date DESC LIMIT $2",
    array($project_id, $days)
);

$rows = array();
while ($result = $gdb->fetch_array($query_id)) {
    $rows[] = array(
        'date'        => $result['date'],
        'participants' => (int) $result['participants'],
        'teams'        => (int) $result['teams'],
    );
}

echo json_encode(array_reverse($rows));
