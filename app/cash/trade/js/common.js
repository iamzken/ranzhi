$(document).ready(function()
{
    $('[name*=objectType]').change(function()
    {
        if($(this).prop('checked')) 
        {
            $('[name*=objectType]').not(this).prop('checked', false).change();
            $('.traderTR').hide();
        }
        else
        {
            $('.traderTR').show();
        }
        $('#' + $(this).val()).parents('tr').toggle($(this).prop('checked'))
        if($(this).val() != 'customer') $('#customer').parents('tr').toggle($(this).prop('checked'));
    })

    $('[name*=objectType]').each(function()
    {
        if($(this).prop('checked')) $(this).change();
    });

    /* Toggle create trader items. */
    $('[name*=createTrader]').change(function()
    {
        if($(this).prop('checked')) 
        {
            $(this).parents('.input-group').find('select').hide();
            $(this).parents('.input-group').find('[id*=trader][id*=_chosen]').hide();
            $(this).parents('.input-group').find('input[type=text][id*=traderName]').show().focus();
            $(this).parents('.input-group-addon').find('.icon-question').hide();
        }
        else
        {
            $(this).parents('.input-group').find('[id*=trader][id*=_chosen]').show();
            $(this).parents('.input-group').find('input[type=text][id*=traderName]').hide();
        }
    })

    /* Highlight submenu. */
    if(config.requestType == 'GET')
    {
        if(v.mode == 'in')
        {
            $('#mainNavbar li').removeClass('active').find("[href*='=" + v.mode + "']").not('[href*=mode\\=invest]').parent().addClass('active');
        }
        else
        {
            $('#mainNavbar li').removeClass('active').find("[href*='=" + v.mode + "']").parent().addClass('active');
        }
    }
    else
    {
        $('#mainNavbar li').removeClass('active');
        if(v.mode == 'in')
        {
            $('#mainNavbar li').find("[href*='trade-browse-in.html']").parent().addClass('active');
        }
        else
        {
            $('#mainNavbar li').find('[href*=' + v.mode + ']').parent().addClass('active');
        }
    }
})

/**
 * Get contract of a trader. 
 * 
 * @param  int    $traderID 
 * @access public
 * @return void
 */
function getContract(traderID)
{
    if(traderID == '') return false;
    $('.contractTD select').empty().load(createLink('crm.contract', 'getOptionMenu', 'traderID=' + traderID));
}
