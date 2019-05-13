{if $error}
	{include file="$template/includes/alert.tpl" type="error" errorshtml=$error}
{elseif $successful}
	{include file="$template/includes/alert.tpl" type="success" msg=$LANG.changessavedsuccessfully}
{/if}
{include file="$template/includes/alert.tpl" type="info" msg={$translation.dnssecDesc}}
<script src="/modules/registrars/dotroll/dnssec/{$type}.min.js"></script>
{include file='./dnssec/wizzard.tpl'}
{include file="./dnssec/$type.tpl"}
