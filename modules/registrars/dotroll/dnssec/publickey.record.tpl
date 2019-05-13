<tr id="{$pre}tr_{$recordid}">
	<td>
		<select disabled="disabled" name="{$pre}dnssec[{$recordid}][flags]" class="form-control input-sm">
			{foreach from=$flags item=flag key=flagid}
				<option value="{$flagid}"{if $flagid eq $record.flags} selected="selected"{/if}>{$flag}</option>
			{/foreach}
		</select>
	</td>
	<td>
		<select disabled="disabled" name="{$pre}dnssec[{$recordid}][protocol]" class="form-control input-sm">
			{foreach from=$protocols item=protocol key=protocolid}
				<option value="{$protocolid}"{if $protocolid eq $record.protocol} selected="selected"{/if}>{$protocol}</option>
			{/foreach}
		</select>
	</td>
	<td>
		<select disabled="disabled" name="{$pre}dnssec[{$recordid}][algorithm]" class="form-control input-sm">
			{foreach from=$algorithms item=algorithm key=algorithmid}
				<option value="{$algorithmid}"{if $algorithmid eq $record.algorithm} selected="selected"{/if}>{$algorithm}</option>
			{/foreach}
		</select>
	</td>
	<td>
		<input disabled="disabled" placeholder="Public key" type="text" name="{$pre}dnssec[{$recordid}][publicKey]" value="{$record.publicKey}" class="form-control input-sm" />
	</td>
	<td class="">
		<input class="btn btn-{if $pre}warning{else}danger{/if} input-sm" style="display: visible;" type="button" onClick="deleteRecord('tr', '{$recordid}');" value="{if $pre}{$LANG.cancel}{else}{$translation.btndelete}{/if}">
	</td>
</tr>
