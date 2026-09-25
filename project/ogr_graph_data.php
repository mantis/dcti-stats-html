<?php
// Data endpoint for project/ogr_graph.php's uPlot chart.
// Returns OGR Phase 1/2 completion history as JSON:
// [{date, count, pass1, pass2}, ...] oldest first.

include "../etc/global.inc";
include "../etc/db_pgsql.php";

header('Content-Type: application/json');

$project = (int)($_GET['project_id'] ?? 0);
$history = (int)($_GET['history'] ?? 60);
if ($history < 1 || $history > 365) {
    $history = 60;
}

$db = new DB("dbname=ogr");
$query_id = $db->query_bound(
    "SELECT rundate, count, pass1, pass2 FROM ogr_complete WHERE project_id = $1 ORDER BY rundate DESC LIMIT $2",
    array($project, $history)
);

$rows = array();
while ($result = $db->fetch_array($query_id)) {
    $rows[] = array(
        'date'  => $result['rundate'],
        'count' => (float) $result['count'],
        'pass1' => (float) $result['pass1'],
        'pass2' => (float) $result['pass2'],
    );
}

echo json_encode(array_reverse($rows));
