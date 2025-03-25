<h3>{$LANG.domaincontactinfo}</h3>

{include file="$template/includes/alert.tpl" type="info" msg=$LANG.whoisContactWarning}

{if $successful}
    {include file="$template/includes/alert.tpl" type="success" msg=$LANG.changessavedsuccessfully textcenter=true}
{/if}

{if $error}
    {include file="$template/includes/alert.tpl" type="error" msg=$error textcenter=true}
{/if}

{if $contactdetails['pending_registrant']}
	{include file="$template/includes/alert.tpl" type="warning" msg="{$translation.contactValidationIsPending}"}
{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails&id={$domainid}&modop=custom&a=Contacts" id="frmDomainContactModification">
    <div class="row">
        {foreach from=$contactdetails name=contactdetails key=contactdetail item=values}
			<div class="col-md-6">
				<h4>{if $translation.$contactdetail}{$translation.$contactdetail}{else}{$contactdetail}{/if}</h4>
				{foreach key=name item=value from=$values}
					{if ($contactdetail == 'registrant' && $contactdetails['pending_registrant']) || $contactdetail == 'pending_registrant'}
						{assign var="disabled" value=true}
					{else}
						{assign var="disabled" value=false}
					{/if}
					{if $domain|substr:-3 == '.hu'}
						{if $name == 'firstname' || $name == 'lastname' || $name == 'companyname' || $name == 'vatnumber' || $name == 'phonenumber' || $name == 'email' || $name == 'confirmemail' || $name == 'confirmphone'}
							{assign var="disabled" value=true}
						{/if}
					{/if}
					<div class="form-group">
						<label>{if $name == 'vatnumber' && $values.companyname == ''}{$translation.identity}{elseif $translation.$name}{$translation.$name}{else}{$name}{/if}</label>
						{if $name == 'country'}
							<select id="contactdetails[{$contactdetail}][country]" name="contactdetails[{$contactdetail}][country]" class="form-control" {if $disabled == true}disabled="disabled"{/if}>
								{foreach $clientcountries as $countryCode => $countryName}
									<option value="{$countryCode}"{if $countryCode eq $value} selected="selected"{/if}>
										{$countryName}
									</option>
								{/foreach}
							</select>
						{else}
							<input type="{if $name == 'phonenumber'}tel{else}text{/if}" id="contactdetails_{$contactdetail}_{$name}" name="contactdetails[{$contactdetail}][{$name}]" value="{$value}" class="form-control {$contactdetail}customwhois" {if $disabled == true} disabled="disabled"{/if} />
						{/if}
					</div>
				{/foreach}
			</div>
			{if $smarty.foreach.contactdetails.index % 2 == 1}
				<div class="clearfix"></div>
			{/if}
		{/foreach}
    </div>
    <br />
    <p class="text-center">
        <input type="submit" value="{$LANG.clientareasavechanges}" class="btn btn-primary" />
        <input type="reset" value="{$LANG.clientareacancel}" class="btn btn-default" />
    </p>
</form>
<script type="text/javascript">
	var allowSubmit = true;
</script>
