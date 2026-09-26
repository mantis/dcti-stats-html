<?
// vi: ts=2 sw=2 tw=120 syntax=php
// $Id: project.inc,v 1.46 2014/02/20 21:11:21 strigona Exp $

require_once "limit.inc";
require_once "db_pgsql.php";
require_once "project.php";

ob_start();

$myname = $_SERVER['SCRIPT_NAME'];

if (isset($_REQUEST['project_id'])) {
    $project_id = (int)$_REQUEST['project_id'];
} else {
    if (isset($default_project)) {
        $project_id = $default_project;
    } else {
        $project_id = 8;
    }
}

if (isset($_GET['source'])) {
    $temp = $_GET['source'];
    switch ($temp) {
    case 'o':
        $source = 'o';
        break;
    case 'y':
        $source = 'y';
        break;
    default:
        $source = 'o';
        break;
    }
} else {
    $source = 'o';
}

if (isset($_REQUEST['id'])) {
    $id = (int)$_REQUEST['id'];
} else {
    $id = 1;
}

if (isset($_REQUEST['st'])) {
    $st = trim( $_REQUEST['st'] );
} else {
    $st = "";
}

if (isset($_REQUEST['view'])) {
    $view = $_REQUEST['view'];
} else {
    $view = "c";
}

if (isset($_REQUEST['pass'])) {
    $pass = $_REQUEST['pass'];
} else {
    $pass = "";
}

if (isset($_REQUEST['team'])) {
    $tm = (int)$_REQUEST['team'];
} else {
    $tm = 1;
}

$gdb = new DB($conn_string);
$gproj = new Project($gdb, $project_id);

// Canonical list of switchable projects, used by the sidebar project
// switcher (templates/header.inc). A page can narrow this by setting
// $project_filter to an array of project ids *before* including
// templates/header.inc (see project/ogr_status.php for an example).
$stats_project_list = array(
    3   => 'RC5-56',
    5   => 'RC5-64',
    205 => 'RC5-64 (all)',
    8   => 'RC5-72',
    24  => 'OGR-24',
    25  => 'OGR-25',
    26  => 'OGR-26',
    27  => 'OGR-27',
    28  => 'OGR-28',
);

if (!isset($project_filter)) {
    $project_filter = array(3, 5, 205, 8, 24, 25, 26, 27, 28);
}

// Projects whose keyspace has been fully searched (or otherwise retired) vs
// still actively running. Kept as an explicit list alongside
// $stats_project_list since "done" isn't something derivable from the stats
// DB itself.
$stats_project_completed = array(3, 5, 205, 24, 25, 26, 27, 28);

function stats_project_is_completed($project_id)
{
    global $stats_project_completed;
    return in_array((int)$project_id, $stats_project_completed);
}

function stats_project_switch_options($projectFilter)
{
    global $stats_project_list;
    $options = array();
    foreach ($stats_project_list as $pid => $label) {
        if (isset($projectFilter) && is_array($projectFilter) && !in_array($pid, $projectFilter)) {
            continue;
        }
        $options[$pid] = $label;
    }
    return $options;
}

function last_update($type)
{
    global $lastupdate, $project_id, $gdb;

    switch ($type) {
    case 'e':
        $qs = "SELECT TO_CHAR(MAX(last_date), 'dd-Mon-YYYY') AS lastupdate FROM Email_Rank_Last_Update WHERE project_id = $1";
        break;
    case 'ec':
        $qs = "SELECT to_char(MAX(last_date), 'dd-Mon-YYYY') AS lastupdate from Email_Contrib_Last_Update WHERE project_id = $1";
        break;
    case 't':
        $qs = "SELECT to_char(MAX(last_date), 'dd-Mon-YYYY') AS lastupdate FROM team_rank WHERE project_id = $1";
        break;
    case 'm':
        $qs = "SELECT to_char(MAX(last_date), 'dd-Mon-YYYY') AS lastupdate FROM Email_Contrib_Last_Update WHERE project_id = $1";
        break;
    case 'pc':
        $qs = "SELECT to_char(MAX(last_date), 'dd-Mon-YYYY') AS lastupdate FROM Platform_Contrib_Last_Update WHERE project_id = $1";
        break;
    case 'i';
        return $lastupdate;
    }
    $par = $gdb -> query_bound_first ($qs, array( (int)$project_id ));
    if($par) {
        $lastupdate = $par -> lastupdate;
    } else {
        $lastupdate = "some day, not too long ago";
    }
    return $lastupdate;
}

// Sets $lastupdate (fetching it via last_update() when $type is given), which
// templates/footer.inc then displays as the "data shown reflects..." banner.
// This used to also print that banner inline as a table row/close, back when
// templates/header.inc opened a <table> for the whole page; the sidebar shell
// no longer does, so it just sets the variable now.
function display_last_update($type = null)
{
    global $lastupdate;
    if(isset($type)) {
        $lastupdate = last_update($type);
    }
}
?>
