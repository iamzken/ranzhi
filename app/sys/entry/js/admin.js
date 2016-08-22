$(document).ready(function()
{
    $.setAjaxDeleter('.entry-deleter', function(response)
    {   
        if(response.result == 'success')
        {   
            if(response.categories)
            {
                var categories = JSON.parse(response.categories);
                $.refreshCategoryMenu(categories);
            }
            if(response.entries) 
            {   
                v.entries = JSON.parse(response.entries);
                $.refreshDesktop(v.entries, true);
            }   
        }   
    }); 
})
