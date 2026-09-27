<?
 // $Id: newteam2.php,v 1.12 2005/12/07 05:44:01 fiddles Exp $

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

 include "../templates/header.inc";
?>
<div class="mx-auto max-w-2xl text-center">
  <p class="mb-6 text-sm italic text-slate-600">How does all this work?</p>

  <div class="mx-auto max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white p-6 text-left shadow-sm">
    <ul class="list-disc space-y-3 pl-5 text-sm text-slate-700">
      <li>The first step to creating a team is for you (the team coordinator) to register the team with the stats server. It is at this point that you will enter a brief description of the team, set the pointer to your team logo, enter your own personal email, and make any team configuration decisions as allowed. Congratulations! You are exactly where you need to be to do this.</li>
      <li>After your team is registered, your team will show up on the team selection lists based on the team category you have chosen.</li>
      <li>A password will be given to you for use in maintaining your team and updating your configuration.</li>
      <li>Each of the members of your team will need to have their own password assigned. They will do this by viewing their own personal statistics page (<a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="/participant/psummary.php?id=1">example</a>) and choosing the link at the bottom of the page. With this password, they will be able to choose which team they are a member of.</li>
      <li>As each member "joins" your team, your team will be credited for any blocks they have completed which are not credited to any other team. This means that participants who are already members of a team who switch to your team will NOT bring their blocks with them.</li>
      <li>Again, only "virgin" blocks from a new participant who is not a member of a team will be credited to your team. And, of course, any blocks completed in the future, for so long as that member is configured as a member of your team.</li>
    </ul>
  </div>

  <p class="mt-6">
    <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="newteam3.php">OK, Already! I've been waiting <i>months</i>. Let's get on with it!</a>
  </p>
</div>
<?
include "../templates/footer.inc";
?>
