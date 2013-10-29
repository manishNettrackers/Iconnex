function showSubwindow(link, targetwindow)
{
	var url = link.href;

	url += "&clear_session=1";

	set_loading_status (true);
	$.ajax(
	{
		type: "GET",
		url: url,
		success: function(result)
		{
			// Set up any pre-sending stuff like initializing progress indicators
			targetframe = $('#' + targetwindow + "frame");
			targetresults = $('#' + targetwindow);
			targetframe.css('display', 'inline' );
        	$(targetresults).attr('innerHTML',result);
			targettitle = $('#' + targetwindow + "title");
			$(targetframe).find('.swLinkMenu').css('display', 'none');
			$(targetframe).find('.swRepTitle').each(function(){
                                    $(targettitle).attr('innerHTML', $(this).attr('innerHTML'));
                                    $(this).css('display', 'none');
                            });
			$(targetframe).find('.swRepPage').each(function(){
                                    $(this).removeClass('swRepPage');
                            });
			$(targetframe).find('.swMntForm').each(function(){
                                    $(this).removeClass('swMntForm');
                            });

            $("form").on("submit", function(event){
                alert("oooo");
            });

			set_loading_status (false);
		},
		error: function(x, e)
		{
            set_loading_status (false);
            alert("Error - " + x.statusText);
		}
	});
};

