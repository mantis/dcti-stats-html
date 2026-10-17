<?php
// Participant search as JSON.
// st == search term (required): digits = exact id, otherwise 3+ chars of partial email
// limit == max results (optional, default 50)
// WARNING: experimental, the schema is subject to change at any time.

include "../etc/global.inc";
$random_stats = 0;
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/participant.php";
include "json.inc";

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;

if (is_numeric($st)) {
  $one = new Participant($gdb, $gproj, (int) $st);
  $rows = array();
  if ($one->get_id() == (int) $st) {
    $rows[] = (object) array('id' => $one->get_id(), 'display_name' => $one->get_display_name());
  }
} else {
  if (strlen($st) < 3) json_error("Search text must be at least 3 characters", 400, 1);
  $rows = Participant::get_search_list_no_stats($st, $limit, $gdb);
}

$results = array();
foreach ($rows as $r) {
  $results[] = array('id' => (int) $r->id, 'name' => $r->display_name);
}
json_out(array('results' => $results));
