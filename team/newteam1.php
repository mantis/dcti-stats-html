<?
 // $Id: newteam1.php,v 1.11 2005/12/07 05:44:01 fiddles Exp $
 //
 // Team creation, step 1.  This will soon be modified to have a
 // psecure wrapper to only allow team creation to be performed
 // by an authenticated participant.  

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
  <h1 class="phead mb-4">Team Registration Center</h1>
  <p class="mb-6 text-sm italic text-slate-600">Notice: It is very important that you read and understand the following before continuing.</p>

  <div class="mx-auto max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white p-6 text-left shadow-sm">
    <ul class="list-disc space-y-3 pl-5 text-sm text-slate-700">
      <li>It is quite likely that you do not need to register a team. Unless you are the official coordinator of a group of participants, you do not need to create a team.</li>
      <li>If you are working alone, under your own email, you do not need to create a team.</li>
      <li>If you are a member of a large team, but cannot find your team listed, you should probably not be creating a team. Only your team coordinator needs to perform this step. If your team is not listed, contact your coordinator and make sure your team is added properly.</li>
      <li>If you have not even looked to see if your team exists, you should <strong>not</strong> be creating a team. The stats system will allow similar teams to be created, so if you create a team that is redundant, your effort will become split. You have been warned.</li>
      <li>If there are other, existing teams that are similar to the team you are thinking about adding, perhaps you should reconsider your plans.</li>
    </ul>
  </div>

  <p class="mt-6">
    <a class="text-indigo-600 hover:text-indigo-800 hover:underline" href="newteam2.php">OK, OK, I still think that I'm doing the right thing</a>
  </p>
</div>
<?
include "../templates/footer.inc";
?>
