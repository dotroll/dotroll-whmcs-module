{if $wizzard}
	<h2 class="text-center">{$translation.dnssecStatus}: <span class="label label-{if $dnssec}success{else}danger{/if}">{if $dnssec}{$LANG.domainsautorenewenabled}{else}{$LANG.domainsautorenewdisabled}{/if}</span></h2>
	<br />
	<br />
	<form method="post" action="">
		<p class="text-center">
			<input type="submit" class="btn btn-lg btn-{if $dnssec}danger{else}success{/if}" name="autoconfig" value="{if $dnssec}{$translation.dnssecAutoconfigDisable}{else}{$translation.dnssecAutoconfigEnable}{/if}" />
		</p>
	</form>
	<div style="clear: both;"></div>
	<br />
	<a href="javascript://" data-toggle="collapse" data-target="#dnssec">{$translation.dnssecSettings}</a>
{/if}
