{**
 * @author    payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx AG
 * @license   MIT License
 *}
<form method="post" action="{$action|escape:'html':'UTF-8'}">
    {if $image ne ''}
    <img src="{$image|escape:'html':'UTF-8'}" width=100/>
    {/if}
</form>
