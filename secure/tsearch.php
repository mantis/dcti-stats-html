<?php
// Variables Passed in url:
//   st == Search Term

include "../etc/global.inc";
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/team.php";
include "../etc/teamstats.php";

$st = isset($_GET['st']) ? trim($_GET['st']) : '';
$title = "Team Search: [".safe_display($st)."]";

$lastupdate = last_update('t');

if (!is_numeric($st) && strlen($st) < 3) {
  include "etc/header.inc";
  ?>
  <div class="mx-auto max-w-3xl px-4 py-6">
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
      <div class="font-semibold">There was an error processing your request</div>
      <p class="mt-1">Search text must be at least 3 characters.</p>
    </div>
  </div>
  <?php
  include "etc/footer.inc";
  exit;
}

if (is_numeric($st)) {
  $team = new Team($gdb, $gproj, (int)$st);
  $result = $team->get_id_mismatch() ? array() : array($team);
} else {
  $result = Team::get_search_list($st, 50, $gdb, $gproj);
}
$rows = count($result);

if ($rows == 1) {
  // Only one hit, jump straight to the team summary
  $id = (int) $result[0]->get_id();
  header("Location: /team/tmsummary.php?project_id=".(int)$gproj->get_id()."&team=$id");
  exit;
}

include "etc/header.inc";
?>
  <div class="mx-auto max-w-3xl px-4 py-6">
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <table class="w-full text-sm">
        <tr>
          <th class="thead w-32 text-left">Team ID</th>
          <th class="thead text-left">Team Name</th>
        </tr>
<?php if ($rows <= 0) { ?>
        <tr><td colspan="2" class="px-3 py-4 text-center text-slate-500">No Matching Records Found</td></tr>
<?php } ?>
<?php for ($i = 0; $i < $rows; $i++) {
  $id = (int) $result[$i]->get_id();
  $href = "/team/tmsummary.php?project_id=".(int)$gproj->get_id()."&amp;team=$id";
?>
        <tr class="border-b border-slate-100 last:border-0 <?php echo $i % 2 == 0 ? 'bg-white' : 'bg-slate-50'; ?>">
          <td class="px-3 py-1.5 text-left tabular-nums"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="<?php echo $href; ?>"><?php echo $id; ?></a></td>
          <td class="px-3 py-1.5 text-left"><a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="<?php echo $href; ?>"><?php echo safe_display($result[$i]->get_name()); ?></a></td>
        </tr>
<?php } ?>
      </table>
    </div>
    <p class="mt-3 text-sm text-slate-500">Only the first 50 results are returned.</p>
  </div>
<?php include "etc/footer.inc"; ?>
