$(document).ready(function()
{
    $('#order, #contract').change(function()
    {
        $('#money').val($(this).find('option:selected').attr('data-amount'));
        $('#customer').val($(this).find('option:selected').attr('data-customer'));
        $('#customer').trigger('chosen:updated');
    })

    var contract = $('#contract').val();
    $('.contractTD select').empty().load(createLink('crm.contract', 'getOptionMenu', 'traderID=' + $('#trader').val()), function()
    {
        $('#contract').val(contract);
    });
})
