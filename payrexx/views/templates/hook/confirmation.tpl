{*
* 2017 Payrexx AG
* @license MIT License
*}

<h3>{l s='Your order is confirmed'}</h3>
<p>
	{l s='An email has been sent to your mail address %s.' sprintf=$customer_email}
	{l s='You can also '}
	<a href="{$invoice_url|escape:'html':'UTF-8'}" title="{l s='Invoice'}" target="_blank">
		{l s='download your invoice'}
	</a>
</p>
