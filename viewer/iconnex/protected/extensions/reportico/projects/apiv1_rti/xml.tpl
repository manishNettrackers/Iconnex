<?xml version="1.0"?>

{if $apiheader}
<Root>
    <Header>
{foreach from=$apiheader key="key" item="value"}
{if $key eq "PublishDateTime"}
        <{$key} canonical="{$apiheader.Canonical}">{$value}</{$key}>
{elseif $key eq "Canonical"}
{elseif $key eq "AttributionUrl"}
        <Attribution>
            <Url>{$apiheader.AttributionUrl}</Url>
            <Text>{$apiheader.AttributionText}</Text>
            <Logo>{$apiheader.AttributionLogo}</Logo>
        </Attribution>
{elseif $key eq "AttributionText"}
{elseif $key eq "AttributionLogo"}
{else}
        <{$key}>{$value}</{$key}>
{/if}{/foreach}    </Header>
{/if}
{$xml}
{if $apiheader}
</Root>
{/if}
