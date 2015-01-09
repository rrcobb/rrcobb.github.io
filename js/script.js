/*!
 * Start Bootstrap - Agnecy Bootstrap Theme (http://startbootstrap.com)
 * Code licensed under the Apache License v2.0.
 * For details, see http://www.apache.org/licenses/LICENSE-2.0.
 */

var Device = {
	MAX_MOBILE_WIDTH: 580,
	MAX_TABLET_WIDTH: 1040,
	width:  $(window).width(),
	height: $(window).height(),
	touch: (function() {
		var el = document.createElement('div');
		el.setAttribute('ongesturestart', 'return;');
	   return typeof el.ongesturestart === "function";
	})(),
	isMobile: function() {
		return this.width <= this.MAX_MOBILE_WIDTH;
	},
	isTablet: function() {
		return this.width <= this.MAX_TABLET_WIDTH && this.width > this.MAX_MOBILE_WIDTH;
	},
	refresh: function() {
		this.width = $(window).width();
		this.height = $(window).height();
	}
};

// jQuery for page scrolling feature - requires jQuery Easing plugin
$(function() {
    $('a.page-scroll').bind('click', function(event) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: $($anchor.attr('href')).offset().top
        }, 1500, 'easeInOutExpo');
        event.preventDefault();
    });

    //spam-free email links
	$mail = $('#mail');
	if ($mail.length > 0) {
		var addr = $mail.attr('href');
		$mail.attr('href', addr.replace('+at+', '@'));
	}

    if (Device.isMobile()) return;

    // desktop/tablet script goes here:

    // fade header banner to black
    var $wrapper = $('.wrapper');
    //var $container = $('.container');
  	$(window).on('scroll', function() {
	    $wrapper.css('background-color', function() {
	        var c = (($(window).scrollTop() + 300) / $(this).outerHeight())*(0.7);
	        if (c > 1) c = 1;
	    	return 'rgba(0,0,0,' + c + ')';
	    });

	    //paralax effect 
	    /*$container.css('top', function() {
	        return -($(window).scrollTop() >> 2);
	    });*/
	});

	$('nav.navbar-fixed-top').autoHidingNavbar({showOnBottom: false});
});

// Highlight the top nav as scrolling occurs
$('body').scrollspy({
    target: '.navbar-fixed-top'
});

// Closes the Responsive Menu on Menu Item Click
$('.navbar-collapse ul li a').click(function() {
    $('.navbar-toggle:visible').click();
});
