<?php
// Shared helpers for the json/ endpoints. Include after project.inc.

// Drop anything buffered so far (project.inc starts output buffering), then emit $data as JSON.
function json_out($data, $status = 200)
{
  while (ob_get_level()) ob_end_clean();
  http_response_code($status);
  header("Content-Type: application/json; charset=utf-8");
  // DB text may be ISO-8859-1; json_encode only accepts UTF-8.
  array_walk_recursive($data, function (&$v) {
    if (is_string($v) && !mb_check_encoding($v, 'UTF-8')) $v = mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
  });
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  exit;
}

function json_error($message, $status = 400, $code = null)
{
  json_out(array('error' => array_filter(array('code' => $code, 'message' => $message))), $status);
}

// Participant stats block, keyed by stat name (ranks carry a "change").
function json_participant_stats($stats, $proj)
{
  $scale = $proj->get_scale();
  return array(
    'rank-project'     => array('value' => $stats->get_stats_item("overall_rank"), 'change' => $stats->get_stats_item("overall_change")),
    'rank-project-day' => array('value' => $stats->get_stats_item("day_rank"), 'change' => $stats->get_stats_item("day_change")),
    'work-overall'     => array('value' => round((float) $stats->get_stats_item("work_total") * $scale, 0), 'unit' => $proj->get_scaled_unit_name()),
    'work-day'         => array('value' => round((float) $stats->get_stats_item("work_today") * $scale, 0), 'unit' => $proj->get_scaled_unit_name()),
    'work-first'       => array('value' => $stats->get_stats_item("first_date")),
    'work-last'        => array('value' => $stats->get_stats_item("last_date")),
    'days-working'     => array('value' => $stats->get_stats_item("days_working")),
  );
}

// Team stats block; $full adds the fields only shown for the team itself.
function json_team_stats($stats, $proj, $full = false)
{
  $scale = $proj->get_scale();
  $unit = $proj->get_scaled_unit_name();
  $out = array(
    'overall-rank' => array('value' => $stats->get_stats_item("overall_rank"), 'change' => $stats->get_stats_item('overall_rank_previous') - $stats->get_stats_item("overall_rank")),
    'overall-work' => array('value' => round($stats->get_stats_item("work_total") * $scale, 0), 'unit' => $unit),
  );
  if ($stats->get_stats_item("work_today") > 0) {
    $out['day-rank'] = array('value' => $stats->get_stats_item("day_rank"), 'change' => $stats->get_stats_item('day_rank_previous') - $stats->get_stats_item("day_rank"));
    $out['day-work'] = array('value' => round($stats->get_stats_item("work_today") * $scale, 0), 'unit' => $unit);
  }
  $out['time-working'] = array('value' => round($stats->get_stats_item("days_working"), 0), 'unit' => 'days');
  if ($full && $proj->get_total_units() > 0 && $stats->get_stats_item("work_today") > 0) {
    $projstats = $proj->get_current_stats();
    $out['odds'] = array('value' => round($projstats->get_stats_item("work_units") / $stats->get_stats_item("work_today"), 0));
  }
  $out['members-overall'] = array('value' => round($stats->get_stats_item("members_overall"), 0));
  $out['members-current'] = array('value' => round($stats->get_stats_item("members_current"), 0));
  $out['members-day']     = array('value' => round($stats->get_stats_item("members_today"), 0));
  return $out;
}

// Map the show_members setting to its public label.
function json_membership($team)
{
  switch ($team->get_show_members()) {
    case "NO":  return "private";
    case "PAS": return "password";
    default:    return "public";
  }
}
