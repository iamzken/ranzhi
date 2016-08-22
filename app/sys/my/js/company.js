$(document).ready(function()
{
    $('.submit').click(function()
    {
        var dept    = $('#dept').val();
        var account = $('#account').val();
        var begin   = $('#begin').val();
        var end     = $('#end').val();
        begin = begin.replace(/-/g, '');
        end = end.replace(/-/g, '');

        var url = createLink('my', 'company', "type=" + v.type + "&dept=" + dept + "&account=" + account + "&begin=" + begin + "&end=" + end);
        location.href = url;
    });
    $('table.datatable').datatable();

    $(window).scroll(function()
    {
        fixScrollWrapper();
    }).resize(function()
    {
        fixScrollWrapper();
    });
    
    fixScrollWrapper();
});

function fixScrollWrapper()
{
    var tbHeight   = parseFloat($('div.datatable').css('height'));
    var wHeight    = $(window).height();
    var wScrollTop = $(window).scrollTop();
    $('div.datatable>div.scroll-wrapper').css('bottom', tbHeight - wHeight - wScrollTop + 60);
}
