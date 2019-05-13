<div id="dnssec" class="collapse {if !$wizzard}in{/if}">
	<form id="zeditorform" class="form-horizontal" method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails&id={$domainid}&modop=custom&a=Dnssec">
		<div class="center100">
			<table class="table table-striped table-framed">
				<thead>
					<tr>
						<th class="textleft">Flags</th>
						<th class="textleft">Protocol</th>
						<th class="textleft">Algorithm</th>
						<th class="textleft">Public key</th>
						<th></th>
					</tr>
				</thead>
				<tbody id="dnsrecordtbody">
					{foreach from=$dnssec item=record key=recordid}
						{include file="./$type.record.tpl" record=$record recordid=$recordid}
					{/foreach}
				<input type="hidden" id="lastnewrecord" value="0" />
				</tbody>
				<tfoot>
					<tr id="addtr" style="display: none;">
						<td>
							<select disabled="disabled" name="newrecordflags" class="form-control input-sm">
								{foreach from=$flags item=flag key=flagid}
									<option value="{$flagid}">{$flag}</option>
								{/foreach}
							</select>
						</td>
						<td>
							<select disabled="disabled" name="newrecordprotocol" class="form-control input-sm">
								{foreach from=$protocols item=protocol key=protocolid}
									<option value="{$protocolid}">{$protocol}</option>
								{/foreach}
							</select>
						</td>
						<td>
							<select disabled="disabled" name="newrecordalgorithm" class="form-control input-sm">
								{foreach from=$algorithms item=algorithm key=algorithmid}
									<option value="{$algorithmid}">{$algorithm}</option>
								{/foreach}
							</select>
						</td>
						<td><input disabled="disabled" type="text" name="newrecordpublicKey" class="form-control input-sm" value=""/></td>
						<td><input class="btn btn-warning" style="display: visible;" type="button" onClick="deleteRecord('newtr', null);" value="{$LANG.cancel}"></td>
					</tr>
					<tr>
						<td colspan="4" style="text-align: center;">
							<input type="submit" value="{$LANG.clientareasavechanges}" class="btn btn-success" />
						</td>
						<td>
							<input class="btn btn-info" type="button" onclick="addRecord();" value="{$translation.btnadd}">
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</form>
</div>
