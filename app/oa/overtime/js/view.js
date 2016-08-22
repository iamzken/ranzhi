$(document).ready(function()
{
    $(document).on('click', '.switch-status', function()
    {
        var reload = $(this);
        $.getJSON(reload.attr('href'), function(data) 
        {
            if(data.result == 'success')
            {
                return location.reload();
            }
            else
            {
                alert(data.message);
            }
        });
        return false;
    });
})

