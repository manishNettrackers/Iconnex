{if $apiheader}
{literal}{"Root":{"Header":{{/literal}
{foreach from=$apiheader key="key" item="value" name="header"}
{if $key eq "Canonical"}
{elseif $key eq "AttributionUrl"}
{literal}"Attribution":{"Url":{/literal}"{$apiheader.AttributionUrl}",
{literal}"Text":{/literal}"{$apiheader.AttributionText}",
{literal}"Logo":{/literal}"{$apiheader.AttributionLogo}"{literal}},{/literal}
{elseif $key eq "AttributionText"}
{elseif $key eq "AttributionLogo"}
{else}
{if $smarty.foreach.header.last}"{$key}":"{$value}"{else}"{$key}":"{$value}",{/if}
{/if}{/foreach}{literal}}{/literal}{if $json != 'null'},"{$datalabel}":{/if}
{/if}
{if $json != 'null'}{$json}{/if}
{if $apiheader}
}}
{/if}
