$(document).ready(function () {

			jQuery(document).ready(function($) {
                // For sub-menu collapse (Bootstrap 2)
				$('.page-sidebar-custom .main-sidebar-nav a[data-toggle="collapse"]').on('click', function(e) {
				});
                $('.page-sidebar-custom .main-sidebar-nav ul.collapse').on('show.bs.collapse shown.bs.collapse', function () {
                    $(this).prev('a').attr('aria-expanded', 'true').find('.rotate-icon').addClass('rotated');
                }).on('hide.bs.collapse hidden.bs.collapse', function () {
                    $(this).prev('a').attr('aria-expanded', 'false').find('.rotate-icon').removeClass('rotated');
                });
                 $('.page-sidebar-custom .main-sidebar-nav ul.collapse.in').each(function(){
                    var triggerLink = $(this).prev('a');
                    triggerLink.find('.rotate-icon').addClass('rotated');
                    triggerLink.attr('aria-expanded', 'true');
                 });
                 $('.page-sidebar-custom .main-sidebar-nav ul.collapse:not(.in)').each(function(){
                    var triggerLink = $(this).prev('a');
                     triggerLink.attr('aria-expanded', 'false'); 
                    triggerLink.find('.rotate-icon').removeClass('rotated');
                 });
                var $sidebar = $('.page-sidebar-custom');
                var $overlay = $('.sidebar-overlay'); 
                var $mobileToggleButton = $('.sidebar-mobile-toggle-btn'); 
                var $body = $('body');
                $mobileToggleButton.on('click', function(e) {
                    e.stopPropagation(); 
                    $sidebar.toggleClass('open-mobile');
                    $overlay.toggleClass('active');
                    $body.toggleClass('sidebar-open-overlay-active'); 
                });
                $overlay.on('click', function() {
                    $sidebar.removeClass('open-mobile');
                    $overlay.removeClass('active');
                    $body.removeClass('sidebar-open-overlay-active');
                });
			});
		

			jQuery(document).ready(function($) {
                // For sub-menu collapse (Bootstrap 2)
				$('.page-sidebar-custom .main-sidebar-nav a[data-toggle="collapse"]').on('click', function(e) {
				});
                $('.page-sidebar-custom .main-sidebar-nav ul.collapse').on('show.bs.collapse shown.bs.collapse', function () {
                    $(this).prev('a').attr('aria-expanded', 'true').find('.rotate-icon').addClass('rotated');
                }).on('hide.bs.collapse hidden.bs.collapse', function () {
                    $(this).prev('a').attr('aria-expanded', 'false').find('.rotate-icon').removeClass('rotated');
                });
                 $('.page-sidebar-custom .main-sidebar-nav ul.collapse.in').each(function(){
                    var triggerLink = $(this).prev('a');
                    triggerLink.find('.rotate-icon').addClass('rotated');
                    triggerLink.attr('aria-expanded', 'true');
                 });
                 $('.page-sidebar-custom .main-sidebar-nav ul.collapse:not(.in)').each(function(){
                    var triggerLink = $(this).prev('a');
                     triggerLink.attr('aria-expanded', 'false'); 
                    triggerLink.find('.rotate-icon').removeClass('rotated');
                 });
                var $sidebar = $('.page-sidebar-custom');
                var $overlay = $('.sidebar-overlay'); 
                var $mobileToggleButton = $('.sidebar-mobile-toggle-btn'); 
                var $body = $('body');
                $mobileToggleButton.on('click', function(e) {
                    e.stopPropagation(); 
                    $sidebar.toggleClass('open-mobile');
                    $overlay.toggleClass('active');
                    $body.toggleClass('sidebar-open-overlay-active'); 
                });
                $overlay.on('click', function() {
                    $sidebar.removeClass('open-mobile');
                    $overlay.removeClass('active');
                    $body.removeClass('sidebar-open-overlay-active');
                });
			});
		

			jQuery(document).ready(function($) {
                // For sub-menu collapse (Bootstrap 2)
				$('.page-sidebar-custom .main-sidebar-nav a[data-toggle="collapse"]').on('click', function(e) {
				});
                $('.page-sidebar-custom .main-sidebar-nav ul.collapse').on('show.bs.collapse shown.bs.collapse', function () {
                    $(this).prev('a').attr('aria-expanded', 'true').find('.rotate-icon').addClass('rotated');
                }).on('hide.bs.collapse hidden.bs.collapse', function () {
                    $(this).prev('a').attr('aria-expanded', 'false').find('.rotate-icon').removeClass('rotated');
                });
                 $('.page-sidebar-custom .main-sidebar-nav ul.collapse.in').each(function(){
                    var triggerLink = $(this).prev('a');
                    triggerLink.find('.rotate-icon').addClass('rotated');
                    triggerLink.attr('aria-expanded', 'true');
                 });
                 $('.page-sidebar-custom .main-sidebar-nav ul.collapse:not(.in)').each(function(){
                    var triggerLink = $(this).prev('a');
                     triggerLink.attr('aria-expanded', 'false'); 
                    triggerLink.find('.rotate-icon').removeClass('rotated');
                 });
                var $sidebar = $('.page-sidebar-custom');
                var $overlay = $('.sidebar-overlay'); 
                var $mobileToggleButton = $('.sidebar-mobile-toggle-btn'); 
                var $body = $('body');
                $mobileToggleButton.on('click', function(e) {
                    e.stopPropagation(); 
                    $sidebar.toggleClass('open-mobile');
                    $overlay.toggleClass('active');
                    $body.toggleClass('sidebar-open-overlay-active'); 
                });
                $overlay.on('click', function() {
                    $sidebar.removeClass('open-mobile');
                    $overlay.removeClass('active');
                    $body.removeClass('sidebar-open-overlay-active');
                });
			});
		
});
