<div id="{$filter_id}">
<h3>{$title}</h3>
<table id="{$filter_id}_table">
        <th>Incorrect Domain</th>
        <th>Replace with</th>
        <th>Options</th>
{foreach from=$data key=find item=replaceWith}
<tr>
        <td style="max-width: 43% !important; min-width: 43% !important; width: 43% !important;">    
                <input type="text" value="{$find}" originalValue="{$find}" filter_id="{$filter_id}" class="correction_from">
		<span class="error_msg" style="display: none"></span>
        </td>   
        <td style="max-width: 43% !important; min-width: 43% !important; width: 43% !important;">    
                <input type="text" value="{$replaceWith}" originalValue="{$find}" filter_id="{$filter_id}" class="correction_to">
		<span class="error_msg"></span>
        </td>   
        <td>    
                <a href="#" class="deleteButton" filter_id="{$filter_id}">Delete this correction</a> 
        </td>   
</tr>
{/foreach}
</table>
<input class="add_new_correction" type="button" value="Add new correction" filter_id="{$filter_id}"></input>
<input class="save_correction_changes save_changes_button" type="button" value="Save changes" style="display: none" filter_id="{$filter_id}"></input>
</div>
