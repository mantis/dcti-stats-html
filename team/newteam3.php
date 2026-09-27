<?
 // $Id: newteam3.php,v 1.11 2005/12/07 05:44:01 fiddles Exp $

 $title = "New Team Creation - Information";

 include "../etc/global.inc";
 include "../etc/modules.inc";
 include "../etc/project.inc";
 unset($proj_name);

 $lastupdate = last_update('t');
 if ($readonly_tmedit != 0) {
     $title = "Team Creation Disabled";
     include "../templates/header.inc";
     include "../templates/readonly.inc";
     include "../templates/footer.inc";
     exit;
 }

 $field_cls = "w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500";
 $label_cls = "block text-sm font-medium text-slate-700 mb-1";

 include "../templates/header.inc";
?>
<div class="mx-auto max-w-2xl text-center">
  <p class="mb-6 text-sm italic text-slate-600">What else do I need to know?</p>

  <div class="mx-auto max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white p-6 text-left shadow-sm">
    <ul class="list-disc space-y-3 pl-5 text-sm text-slate-700">
      <li>Below is a very condensed version of the information that you may configure for your team. This is the minimum you must provide to register your team.</li>
      <li>Your team will NOT be listed until after the next database update takes place. This could be anywhere from one minute to one day from now. Please be patient.</li>
      <li>Oh yeah. It'd be sorta nice if you like, recruited folks and stuff.</li>
    </ul>
  </div>

  <h2 class="phead mt-8 mb-4 text-lg">Team Information</h2>
  <form action="newteam4.php" method="get" class="mx-auto max-w-xl space-y-4 overflow-hidden rounded-lg border border-slate-200 bg-white p-6 text-left shadow-sm">
    <div>
      <label class="<?=$label_cls?>" for="name">Full Team Name</label>
      <input id="name" name="name" type="text" value="" size="50" maxlength="64" class="<?=$field_cls?>">
    </div>
    <div>
      <label class="<?=$label_cls?>" for="contactname">Coordinator Name (You)</label>
      <input id="contactname" name="contactname" type="text" value="" size="50" maxlength="64" class="<?=$field_cls?>">
    </div>
    <div>
      <label class="<?=$label_cls?>" for="contactemail">Coordinator Email Address (Yours)</label>
      <input id="contactemail" name="contactemail" type="text" value="" size="50" maxlength="64" class="<?=$field_cls?>">
    </div>
    <div class="text-center">
      <input type="submit" value="This is exciting! When do I get my password?" class="cursor-pointer rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-500">
    </div>
  </form>
</div>
<?
include "../templates/footer.inc";
?>
