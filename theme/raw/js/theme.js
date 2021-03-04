/*jslint browser: true, nomen: true,  white: true */
/* global jQuery, $ */

jQuery(function($) {
"use strict";

    /*
     * Make sure an affix is always the width of its container element
     */
    function affixSize(){
        var affix = $('[data-spy="affix"]'),
            affixwidth,
            affixheight,
            i;

        for (i = 0; i < affix.length; i = i + 1) {
            affixwidth = $(affix[i]).parent().width();
            affixheight = $(affix[i]).height() + 100;

           $(affix[i]).width(affixwidth);

           // prevents short pages from bouncing the user back to the top
           $(affix[i]).parent().height(affixheight);

        }

        $(window).on('resize colresize', function(){
            var i;

            for (i = 0; i < affix.length; i = i + 1) {
                affixwidth = $(affix[i]).parent().width();
                affixheight = $(affix[i]).height() + 100;

                $(affix[i]).width(affixwidth);

                // prevents short pages from bouncing the user back to the top
                $(affix[i]).parent().height(affixheight);
            }
        });
    }

    /*
     * We need to know the number of site message in ordder to adjust positioning
     */
    function siteMessages() {
        var message;

        // Remove extra padding when there are no site message
        if ($('.site-messages').length === 0) {
            $('.header').addClass('no-site-messages');
            $('.main-nav').addClass('no-site-messages');
        }
        else if ($('.site-messages') !== undefined) {
            message = $('.site-messages').find('.site-message');
            $('.header').addClass('message-count-'+ message.length);
            $('.header').removeClass('no-site-messages');
        }
    }

    /**
     * Focus the first form element when forms are expanded
     */
    function focusOnOpen() {
        $('[data-action~="focus-on-open"]').on('shown.bs.collapse', function() {
            $(this).find('form input').first().trigger("focus");
        });
    }

    /*
     * Clear form when a form is collapsed
     */
    function resetOnCollapse() {
        $('[data-action~="reset-on-collapse"]').on('d-none.bs.collapse', function () {
            var i,
                forms =$(this).find('form');
            for (i = 0; i < forms.length; i = i + 1) {
                forms[i].reset();
            }
        });
    }

    function attachTooltip() {
        $('[data-toggle="tooltip"]').tooltip({
            container: 'body',
            placement: 'right',
            viewport: 'body'
        });
    }

    /*
     * Calculate carousel(image gallery) height
     */
    window.carouselHeight = function () {
        var carousel = $('.carousel'),
            i, j,
            carouselItem,
            height;

        carousel.removeClass('carousel-ready');

        for (i = 0; i < carousel.length; i = i + 1) {

            $(carousel[i]).find('.item').addClass('inline');

            height = 0;
            carouselItem = $(carousel[i]).find('.item');

            for (j = 0; j < carouselItem.length; j = j + 1) {
                if ($(carouselItem[j]).height() > height) {
                    height = $(carouselItem[j]).height();
                }
            }

            $(carousel[i]).find('.item').removeClass('inline');

            $(carousel[i]).height(height);
            $(carousel[i]).addClass('carousel-ready');
        }
    }

    /*
     * Initialise masonry for thumbnail gallery
     */
    function initThumbnailMasonry() {
         $('.js-masonry.thumbnails').masonry({
             itemSelector: '.thumb'
         });
     }

    function initUserThumbnailMasonry() {
        $('.js-masonry.user-thumbnails').masonry({
             itemSelector: '.user-icon'
        });
    }

    function handleInputDropdown(context) {
        var val = context.find('select').find('option:selected').text();
        if (val.length > 40) {
            val = val.substring(0, 40) + '...';
        }

        context.find('.js-with-dropdown input').attr('placeholder', val);
        if (context.find('.js-dropdown-context').length > 0) {
            context.find('.js-dropdown-context').html('(' + val + ')');
        }
        else {
            context.find('.js-with-dropdown label').append('<em class="js-dropdown-context text-midtone text-small">('+ val + ')</em>');
        }
    }

    function attachInputDropdown() {
        // Sets a new context for every .js-dropdown-group item
        $('.js-dropdown-group').each(function () {
            var context = $(this);
            handleInputDropdown(context);
        })

        $('.js-dropdown-group select').on('change', function(){
            var context = $(this).closest('.js-dropdown-group');
            handleInputDropdown(context);
        });
    }

    function setupCustomDropdown() {
        /*
         * Custom dropdown creates a fake select box that can have items of an
         * arbitrary length (unlike attachInputDropdown which uses a select).
         * For screenreaders, it works like a UL of links.
         */

         /* Utilize bootstrap functions for showing/ hiding the list when
         *  user presses the 'Enter' key (keyCode 13)
         */
        $('.custom-dropdown > .picker').keydown(function(e){
            if (e.keyCode == 13) {
                $(this).parent().children('ul').collapse('toggle');
            }
        });
    }

    function calculateObjectVideoAspectRatio() {
        var allVideos = $('.mediaplayer object > object'),
            i;
        for (i = 0; i < allVideos.length; i = i + 1) {
            $(allVideos[i]).attr('data-aspectRatio', allVideos[i].height / allVideos[i].width);
        }
    }

    function responsiveObjectVideo() {
        var allVideos = $('.mediaplayer object > object'),
            i,
            fluidEl,
            newWidth;
        for (i = 0; i < allVideos.length; i = i + 1) {
            fluidEl = $(allVideos[i]).parents('.mediaplayer-container'),
            newWidth = $(fluidEl).width();
            $(allVideos[i]).removeAttr('height').removeAttr('width');
            $(allVideos[i]).width(newWidth).height(newWidth * $(allVideos[i]).attr('data-aspectRatio'));
        }
    }

    $(window).on('resize colresize', function(){
        carouselHeight();
        initThumbnailMasonry();
        initUserThumbnailMasonry();
        responsiveObjectVideo()
    });

    $(window).on('versioningload', function() {
        carouselHeight();
        initThumbnailMasonry();
        initUserThumbnailMasonry();
        responsiveObjectVideo()
    });

    if (document.readyState === "complete") {
      carouselHeight();
      initUserThumbnailMasonry();
    }

    $('.block.collapse').on('shown.bs.collapse', function() {
        carouselHeight();
    });

    $('.block.collapse').on('click',function(e) {
        var dialog = $('.modal-dialog'),
            dialogParent = $(e.target).closest('.modal-dialog').length;
        // If we have a link to a comments dialog within a collapsible block
        // We need to close all of them except for the one we just
        // clicked for
        var collapseModalTarget = $(e.target).data('target');
        var wantedDialog = $(collapseModalTarget);
        if (e.target !== dialog && !wantedDialog.length && !dialogParent) {
            $(this).find('button.close').trigger('click');
        }
    });

    $('.navbar-main .navbar-collapse.collapse').on('show.bs.collapse', function(event) {
        event.stopPropagation();
        $('.navbar-collapse.collapse.show').collapse('hide');
    });

    $('.navbar-main .child-nav.collapse').on('show.bs.collapse', function(event) {
        event.stopPropagation();
        $('.child-nav.collapse.show').collapse('hide');
    });

    affixSize();
    siteMessages();
    focusOnOpen();
    resetOnCollapse();
    attachTooltip();
    calculateObjectVideoAspectRatio();
    responsiveObjectVideo()

    if ($('.js-dropdown-group').length > 0){
        attachInputDropdown();
    }

    if ($('.custom-dropdown') .length > 0) {
        setupCustomDropdown();
    }

    $(".js-select2 select").select2({});

    /*
     * Close navigation when ESC is pressed or focus is moved from navigation menu
     */
    $(document).on('click keyup', function(event) {
      // keyESCAPE constant is used since jQuerty.ui is not loaded on all pages
      var keyESCAPE = 27;
      if (
        (event.type == 'click' && !($(event.target).closest('.navbar-toggle, .navbar-form').length || event.which == 3)) ||
        (event.type == 'keyup' && event.keyCode == keyESCAPE)
      ) {
        $('.navbar-collapse.collapse.show').collapse('hide');
      }
    });

});
