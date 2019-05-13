<h3>{$translation.huElectronicRegistration}</h3>

{if $declareError}
	{include file="$template/includes/alert.tpl" type="danger" msg={$declareError}}
	<p>
		<a class="btn btn-primary" href="{$smarty.server.PHP_SELF}?action=domaindetails&id={$domainid}&modop=custom&a=Declaration">{$translation.declarationGetNew}</a>
	</p>
{elseif $declareSuccess}
	{include file="$template/includes/alert.tpl" type="info" msg={$translation.declarationSuccess}}
{else}
	<p style="margin-bottom: 20px;"><pre  style="white-space: pre-wrap; word-break: keep-all;">{$declaration['declare_text']}</pre></p>
	<p style="margin-bottom: 20px;"><img src="data:image/png;base64,{$declaration['declare_captcha']}" /></p>
	<form method="post" class="form-horizontal" action="{$smarty.server.PHP_SELF}?action=domaindetails&id={$domainid}&modop=custom&a=declaration">
		<div class="row">
			<div class="col-md-12">
				<label for="capthca">{$translation.declarationCapthca}</label>
				<input name="captcha" id="captcha" type="text" />
				<input name="accept" id="accept" type="hidden" value="accept" />
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<input class="btn btn-primary" type="submit" name="submit" value="{$translation.declarationButton}" />
			</div>
		</div>
	</form>
{/if}
