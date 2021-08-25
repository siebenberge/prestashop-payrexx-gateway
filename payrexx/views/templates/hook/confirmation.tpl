{*
* 2017 Payrexx AG
* @license MIT License
*}

<h3>{l='Your order is confirmed'}</h3>
<p>
	{l='An email has been sent to your mail address %s.' sprintf=$customer_email}
	{l='You can also '}
	<a href="{$invoice_url|escape:'html':'UTF-8'}" title="{l='Invoice'}" target="_blank">
		{l='download your invoice'}
	</a>
</p>
