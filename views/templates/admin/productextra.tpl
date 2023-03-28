<div id="mockupgenerator" class="container">
    <h4 class="mb-4">{l s='Mockup Generator' mod='mockupgenerator'}</h4>
    {foreach from=$mockupData item=mockup}
        {assign var="productMockup" value=null}
        {foreach from=$productMockupData item=pMockup}
            {if $pMockup.id_mockup == $mockup.id_mockup}
                {assign var="productMockup" value=$pMockup}
                {break}
            {/if}
        {/foreach}
        <div class="form-group row">
            <label class="control-label col-lg-3 col-form-label" for="mockup_offset_x_{$mockup.id_mockup}">
                {l s='Offset X for Mockup' mod='mockupgenerator'} {$mockup.id_mockup}
            </label>
            <div class="col-lg-9">
                <input type="number" name="mockup_offset_x_{$mockup.id_mockup}" id="mockup_offset_x_{$mockup.id_mockup}" value="{$productMockup.offset_x|default:$mockup.offset_x}" class="form-control">
            </div>
        </div>
        <div class="form-group row">
            <label class="control-label col-lg-3 col-form-label" for="mockup_offset_y_{$mockup.id_mockup}">
                {l s='Offset Y for Mockup' mod='mockupgenerator'} {$mockup.id_mockup}
            </label>
            <div class="col-lg-9">
                <input type="number" name="mockup_offset_y_{$mockup.id_mockup}" id="mockup_offset_y_{$mockup.id_mockup}" value="{$productMockup.offset_y|default:$mockup.offset_y}" class="form-control">
            </div>
        </div>
    {/foreach}
</div>
