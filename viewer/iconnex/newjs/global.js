// JavaScript Document
$(document).ready(function() {
	var iw = $('body').innerWidth();
	var ih = $('body').innerHeight();
	var leftside =$('.leftside').innerWidth();
	var leftsideH =$('.leftside').innerHeight();
	var rightcolumn = (innerWidth-leftside);
	var minheight = (innerHeight-190);
	
	var criteriacolH = $('#criteriacol').height();
	var container1H = (minheight-criteriacolH)-75;
	//alert(minheight);
	//alert(criteriacolH);
	//alert(container1H);
	$('.container1').css('height', container1H);
	$('.container .rightside .whitebox, .opendiv').css('min-height', minheight);
	//$('.container .rightside .whitebox').css('min-height', leftsideH);
	$('.arrowleft span').click(function () {
		$('.leftside').toggle();
		$('.arrowleft').toggleClass('closeLeft')
	});
	
	$('.popupWin .min-max').click(function () {
		//alert("hi");
		
		$('.popupWin').toggleClass('maxWidth');
		$('.popupWin .min-max').toggleClass('maxsize')
	});
	
	$('#popup1 .close12').click(function () {
		$('#popup1').fadeOut();
	});
	
	$('.penico').live('click', function () {
		var id = $(this).attr('id'); 
		$('#accordion').children().each(function(index){
			$(this).hide();
		})
		$('#accordionheader'+id).show();
		$('#accordionframe'+id).show();
		$('#popup1').fadeIn();

	});
	
	$('.navigation .my_modal_open').live('click', function () {
		$('#popup2').fadeIn();
	});
	
	$('#popup2 .close12').click(function () {
		$('#popup2').fadeOut();
	});
	
	$('#saveworkspace').live('click', function () {
		$('#popup3').fadeIn();
	});
	
	$('#popup3 .close12').click(function () {
		$('#popup3').fadeOut();
	});
	
	$('#popup4 .close12').click(function () {
		$('#popup4').fadeOut();
	});
	
	$('.jdmenu_option').live("click",function(e){
		e.stopPropagation();
		var label = $('a',$(this)).html();
		loadMenuQuery(label, false, false, false, false, false, false);
		return false;
	});/**/
	
	$('.popupWin .closeWin').click(function () {
		$('.popupWin').hide();
	});
	
	//Customize Dashboard
	$('.block').live("click",function(){
		$("#curDashboard").val($(this).attr("id"));
		var count	= $("p",$(this)).length;
		if (count == 1 )
			$('#dashcol2').parent().hide();
		else
			$('#dashcol2').parent().show();
		if( count >3)
		{
			var current = $('#dashboardrow1').children(':visible').length;
			var j 		= current
			for (var i = 0; i<(count -current); i++)
			{
				j++;
				$('#dashcol'+j).parent().show();
			}
		}
		else
		{
			$('#dashcol3').parent().hide();
			$('#dashcol4').parent().hide();
		}
		$("p",$(this)).each(function(index){
			var k = index+1;
			$('#dashcol'+k).parent().css("width",$(this).html());
			$('.dashheadgrid').trigger('click');
		})
		$('#popup2').fadeOut();
	})

	
	//Accordion
	$('.accordionblock .current').next().addClass("show_div");
	$('.accordion-header').live("click",function () {
	if($(this).is('.inactive-header')) {
	$('.active-header').toggleClass('active-header').toggleClass('inactive-header').next().slideToggle().toggleClass('open-content');
	$(this).toggleClass('active-header').toggleClass('inactive-header');
	$(this).next().slideToggle().toggleClass('open-content');
	$('.accordionblock .current').next().slideUp();
	$('.accordion-header').removeClass("current")
	}
	
	else {
	$(this).toggleClass('active-header').toggleClass('inactive-header');
	$(this).next().slideToggle().toggleClass('open-content');
	}
	});

	
	$('#criteriacol li .close').click(function () {
		$(this).parent().fadeOut()
	});
	
	//Accordion1
	$('#criteriacol li').toggleClass('inactive-header1');
	$('#criteriacol .opendiv').append('<span class="close">X</span>');
	$('#criteriacol li').click(function () {
		if($(this).is('.inactive-header1')) {
			$('.active-header1').toggleClass('active-header1').toggleClass('inactive-header1').next('.opendiv').animate({width:'toggle'},300).toggleClass('open-content1');
			$('.whitebox').animate({paddingLeft:'240'},300)
			$(this).toggleClass('active-header1').toggleClass('inactive-header1');
			$(this).next('.opendiv').animate({width:'toggle'},300).toggleClass('open-content1');
		}
		
		else {
			$('.whitebox').animate({paddingLeft:'15'},300)
			$(this).toggleClass('active-header1').toggleClass('inactive-header1');
			$(this).next('.opendiv').animate({width:'toggle'},300).toggleClass('open-content1');
		}
	});
	
	$('.opendiv .close').click(function () {
		//alert('hi')
		$('.opendiv').animate({width:'hide'},300);
		$('.whitebox').animate({paddingLeft:'15'},300)
		$('.criteriacol ul li.active-header1').toggleClass('inactive-header1').removeClass('active-header1')
	});
	
	return false;
	
	//End Accordion
	
});

$(window).bind("resize", UpdateFixLayout);
    function UpdateFixLayout( e ) {
    var winHeight = $(window).height();
    var winWidth = $(window).width();
	
	var leftside1 =$('.leftside').width();
	var leftsideH1 =$('.leftside').height();
    var rightcolumn1= (winWidth-leftside1);
	
	var minheight1 = (winHeight-190);
	//$('.container .rightside').css('width', rightcolumn1);
	
	var criteriacolH_1 = $('#criteriacol').height();
	var container1H_1 = (minheight1-criteriacolH_1)-75;
	$('.container1').css('height', container1H_1);
	
	$('.container .rightside .whitebox, .opendiv').css('min-height', minheight1);
	//$('.container .rightside .whitebox').css('min-height', leftsideH1);
	//$('.logos_block aside').css('width', logosW1);
}