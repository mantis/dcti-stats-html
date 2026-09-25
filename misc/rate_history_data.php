<?php
// Shared data endpoint for the site's daily-rate charts (pc_index.php's
// Pace sparkline, keyrate.php's history chart). Returns the last N days'
// scaled daily rate as JSON: [{date, rate}, ...] oldest first. Pulled from
// daily_summary, the same table etc/projectstats.php::ProjectStats
// ::loadCurrent() uses for the project's current totals.

include "../etc/global.inc";
include "../etc/db_pgsql.php";
include "../etc/project.inc";

header('Content-Type: application/json');

$days = (int)($_GET['days'] ?? 30);
if ($days < 1 || $days > 3653) {
    $days = 30;
}

$query_id = $gdb->query_bound(
    "SELECT date, work_units FROM daily_summary WHERE project_id = $1 ORDER BY date DESC LIMIT $2",
    array($project_id, $days)
);

$rows = array();
while ($result = $gdb->fetch_array($query_id)) {
    $rows[] = array(
        'date' => $result['date'],
        'rate' => round(((float) $result['work_units'] * $gproj->get_scale()) / 86400, 2),
    );
}

echo json_encode(array_reverse($rows));
