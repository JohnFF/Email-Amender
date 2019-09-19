<div class="crm-form-block">

{* HEADER *}

{ts}Please check your <a href="{crmURL p='civicrm/emailamendersettings'}">Email Address Corrector settings</a> before continuing{/ts}.
<br/><br/>
{ts}This will automatically correct incorrect email addresses attached to the contacts you've selected.{/ts}
{ts}An activity will be added to each contact recording what has changed.{/ts}
<br/><br/>
{$contactIdCount} {ts}contacts are due to be processed.{/ts}

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

</div>
