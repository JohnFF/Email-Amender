<p>When the Enable Automatic Email Corrections setting below is checked, email addresses are corrected automatically as they are added. So <strong>john@hotmai.cpm</strong> would be corrected to <strong>john@hotmail.com</strong></p><br/>
<div style="text-align: center; width: 100%">
<input style="margin: auto;" type="checkbox" id="email_amender_enabled" {if $email_amender_enabled eq "true"} checked="checked" {/if}>Enable automatic email corrections.
</div>
<br/>
<p>The Email Amender has been designed not to affect edits made to any email addresses, or to change any email addresses that are already in your system. It will only act when new email addresses are added.<br/><br/>
You can add, remove, and edit the automatic corrections using the forms below.<br/><br/>
There are two types of corrections that can be made. Top Level Domains, such as "com" in "john@hotmail.<strong>com</strong>", have one set of corrections. Second Level Domains, such as "gmail" in "john@<strong>gmail</strong>.com" have another set of corrections.<br/><br/>
Incorrect Top Level Domains and Second Level Domains can be corrected in the same email address. So <strong>john@hotmai.cpm</strong> will be corrected to <strong>john@hotmail.com</strong>.<br/><br/>
Some Top Level Domains are "compounds", for instance ".co.uk" ".ac.uk" and ".org.uk". They are treated as a Top Level Domain, so <strong>john@hotmai.co.uk</strong> will be corrected to <strong>john@hotmail.co.uk</strong>, whereas <strong>john@hotmai.hotmai.com</strong> would be corrected to <strong>john@hotmai.hotmail.com</strong>. You can add new compound Top Level Domains using the form below.<br/><br/>
Subdomains are not examined, and neither is anything before the @ sign. So <strong>gmai@gmai.gmai.com</strong> will be corrected to <strong>gmai@gmai.gmail.com</strong><br/><br/>
An activity of type "Amended Email" is recorded every time a correction is made, so you can review the impact that this Email Amender is having on your database.<br/><br/>
</p>


{include file="CRM/Emailamender/Page/EmailAmenderSettingsTable.tpl" title='Top Level Domain Settings' data=$top_level_filter_settings filter_id="top_level_domain"}
<br/><br/>
{include file="CRM/Emailamender/Page/EmailAmenderSettingsTable.tpl" title='Second Level Domain Settings' data=$second_level_filter_settings filter_id="second_level_domain"}
<br/><br/>
<div id="compound_tld">
<h3>Compound Top Level Domain Names</h3>
"Compound" Top Level Domain Names indicate second level domain names that are usually treated as part of the first. For instance, in the case of the incorrect email address john@gmai.co.uk, we want to repair the 'gmai', not the 'co'. 
<table id="compound_tld_table">
	<th>Compound Domain Name</th>
	<th>Options</th>
	{foreach from=$compound_top_level_domains item=compoundTld}
		<tr>
			<td><input type="text" value="{$compoundTld}" filter_id="compound_tld"></input></td>
			<td><a href="#" class="deleteButton" filter_id="compound_tld">Delete this compound tld</a></td>
		</tr>
	{/foreach}
</table>
<input class="add_new_compound_tld" type="button" value="Add new compound tld" filter_id="compound_tld"></input>
<input class="save_tld_changes save_changes_button" type="button" value="Save changes" style="display: none" filter_id="compound_tld"></input>
</div>
<br/><br/>
{include file="CRM/Emailamender/Page/EquivalentsTable.tpl" title='Equivalent Domains' data=$equivalent_domain_settings filter_id="equivalent_domain"}
{include file="CRM/Emailamender/Page/EmailAmenderSettings.js"}
