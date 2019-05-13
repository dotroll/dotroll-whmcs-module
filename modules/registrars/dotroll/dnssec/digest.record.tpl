<tr id="{$pre}tr_{$recordid}">
	<td>
		<input disabled="disabled" placeholder="Key Tag" type="text" name="{$pre}dnssec[{$recordid}][keytag]" value="{$record.keytag}" class="form-control input-sm" />
	</td>
	<td>
		<select disabled="disabled" name="{$pre}dnssec[{$recordid}][algorithm]" class="form-control input-sm">
			{foreach from=$algorithms item=algorithm key=algorithmid}
				<option value="{$algorithmid}"{if $algorithmid eq $record.algorithm} selected="selected"{/if}>{$algorithm}</option>
			{/foreach}
		</select>
	</td>
	<td>
		<select disabled="disabled" name="{$pre}dnssec[{$recordid}][digesttype]" class="form-control input-sm">
			{foreach from=$digestTypes item=digesttype key=digesttypeid}
				<option value="{$digesttypeid}"{if $digesttypeid eq $record.digesttype} selected="selected"{/if}>{$digesttype}</option>
			{/foreach}
		</select>
	</td>
	<td>
		<input disabled="disabled" placeholder="Digest" type="text" name="{$pre}dnssec[{$recordid}][digest]" value="{$record.digest}" class="form-control input-sm" />
	</td>
	<td class="">
		<input class="btn btn-{if $pre}warning{else}danger{/if} input-sm" style="display: visible;" type="button" onClick="deleteRecord('tr', '{$recordid}');" value="{if $pre}{$LANG.cancel}{else}{$translation.btndelete}{/if}">
	</td>
</tr>
