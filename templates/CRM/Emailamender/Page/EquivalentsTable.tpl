<div id="{$filter_id}">
<h3>{$title}</h3>
<p>
  When an e-mail arrives from an address for which there is no existing
  contact in the system, normally a new contact is created with that
  address.
  <br/><br/>
  However, some e-mail domains are equivalent, and lead to the
  same accounts, eg. user@<strong>gmail</strong>.com and
  user@<strong>googlemail</strong>.com.
  <br/><br/>
  If the exact "from" address on an incoming e-mail is not found on an
  existing contact, we try its possible equivalents.
  <br/><br/>
  In the table below, groups can be created of domains that are equivalent.
  <br/><br/>
  Because this function is separate from the correction of added
  addresses, it is not subject to the box 'Enable automatic email
  corrections' being ticked.
  <br/><br/>
  It does not change any existing data for a contact.
</p>
<table id="{$filter_id}_table">
        <th>Group identifier</th>
        <th>Equivalent domain</th>
        <th>Options</th>
{foreach from=$data key=find item=replaceWith}
<tr>
        <td style="max-width: 43% !important; min-width: 43% !important; width: 43% !important;">    
                <input type="text" value="{$replaceWith}" originalValue="{$find}" filter_id="{$filter_id}" class="correction_to">
		<span class="error_msg"></span>
        </td>   
        <td style="max-width: 43% !important; min-width: 43% !important; width: 43% !important;">    
                <input type="text" value="{$find}" originalValue="{$find}" filter_id="{$filter_id}" class="correction_from">
		<span class="error_msg" style="display: none"></span>
        </td>   
        <td>    
                <a href="#" class="deleteButton" filter_id="{$filter_id}">Delete this equivalent</a> 
        </td>   
</tr>
{/foreach}
</table>
<input class="add_new_equivalent" type="button" value="Add new equivalent" filter_id="{$filter_id}"></input>
<input class="save_correction_changes save_changes_button" type="button" value="Save changes" style="display: none" filter_id="{$filter_id}"></input>
</div>
