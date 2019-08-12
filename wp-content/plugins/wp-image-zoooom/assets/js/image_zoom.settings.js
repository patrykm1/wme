jQuery(document).ready(function($) {
    $('input[type=color]').on('change', function() {
        $(this).siblings('span').text($(this).val());
    });

    function updateZoomer() {
        $(".zoomContainer").remove();

        $("#demo").unbind( 'touchstart touchmove touchend click mouseenter' );

        var zoom_options = {};

        // Get the settings
        var settings = {
            lensShape       : $("input[name=lensShape]:checked").val(),
            cursorType      : $("input[name=cursorType]:checked").val(),
            zwEasing        : parseInt($("#zwEasing").val()),
            lensSize        : parseInt($("#lensSize").val()),
            lensColour      : $("#lensColour").val(),
            lensOverlay     : $("#lensOverlay").is(':checked'),
            borderThickness : parseInt($("#borderThickness").val()),
            borderColor     : $("#borderColor").val(),
            borderRadius    : parseInt($("#borderRadius").val()),
            zwWidth         : parseInt($("#zwWidth").val()),
            zwHeight        : parseInt($("#zwHeight").val()),
            zwShadow        : parseInt($("#zwShadow").val()),
            zwPadding        : parseInt($("#zwPadding").val()),
            zwBorderThickness : parseInt($("#zwBorderThickness").val()),
            zwBorderColor   : $("#zwBorderColor").val(),
            zwBorderRadius  : parseInt($("#zwBorderRadius").val()),
            lensFade        : parseFloat($("#lensFade").val()) * 1000,
            zwFade          : parseFloat($("#zwFade").val()) * 1000,
            tint            : $("#tint").is(':checked'),
            tintColor       : $("#tintColor").val(),
            tintOpacity     : parseFloat($("#tintOpacity").val()),
        };

        validateInput( settings );

        if ( settings.tintOpacity > 1 ) {
            settings.tintOpacity = 1;
        } 
        if ( settings.tintOpacity < 0 ) {
            settings.tintOpacity = 0;
        }

        if ( settings.cursorType === 'zoom' ) {
            settings.cursorType = 'url(../images/cursor_type_zoom.svg) auto';
        }

        if ( settings.lensOverlay === true ) {
            settings.lensOverlay = '../images/lens-overlay-1.png';
        }

        // Configure the zoomer
        switch (settings.lensShape) {
            case 'none' :
                zoom_options = {
                    zoomType    : "inner",
                    cursor      : settings.cursorType,
                    easingAmount    : settings.zwEasing,
                };
                break;
            case 'square' :
            case 'round' :
                zoom_options = {
                    lensShape       : settings.lensShape,
                    zoomType        : 'lens',
                    lensSize        : settings.lensSize, 
                    borderSize      : settings.borderThickness, 
                    borderColour    : settings.borderColor, 
                    cursor          : settings.cursorType,
                    lensFadeIn      : settings.lensFade,
                    lensFadeOut     : settings.lensFade,
                };
                if ( settings.tint === true) {
                    zoom_options.tint = true;
                    zoom_options.tintColour = settings.tintColor;
                    zoom_options.tintOpacity = settings.tintOpacity;
                }
                break;
            case 'zoom_window' :
                zoom_options = {
                    lensShape       : 'square',
                    lensSize        : settings.lensSize, 
                    lensBorderSize  : settings.borderThickness, 
                    lensBorderColour: settings.borderColor, 
                    lensColour      : settings.lensColour,
                    lensOverlay     : settings.lensOverlay,
                    borderRadius    : settings.zwBorderRadius, 
                    cursor          : settings.cursorType,
                    zoomWindowWidth : settings.zwWidth,
                    zoomWindowHeight: settings.zwHeight,
                    zoomWindowShadow: settings.zwShadow,
                    borderSize      : settings.zwBorderThickness,
                    borderColour    : settings.zwBorderColor,
                    zoomWindowOffsetx: settings.zwPadding,
                    lensFadeIn      : settings.lensFade,
                    lensFadeOut     : settings.lensFade,
                    zoomWindowFadeIn      : settings.zwFade,
                    zoomWindowFadeOut     : settings.zwFade,
                    easingAmount    : settings.zwEasing,
                    zoomWindowPosition : 1,
                };
                if ( settings.tint === true) {
                    zoom_options.tint = true;
                    zoom_options.tintColour = settings.tintColor;
                    zoom_options.tintOpacity = settings.tintOpacity;
                }
                $("#demo_wrapper").css('text-align', 'left');
                break;

        }
        $("#demo").image_zoom(zoom_options);

        $(window).bind('resize', function() {
            $(window).resize(function() {
                clearTimeout(window.resizeEvt);
                window.resizeEvt = setTimeout(function() {
                    $(".zoomContainer").remove();
                    $("#demo").image_zoom(zoom_options);
                }, 300);
            });
        });

        enableDisableFields(settings);

    }

    $('[data-toggle="tooltip"]').tooltip();

    if ( $("#demo").length > 0 ) {
        updateZoomer();
    }
    

    function validateInput( settings ) {

        if ( isNaN(settings.zwEasing) || settings.zwEasing < 0 || settings.zwEasing > 200 ) {
            show_alert('<b>Animation Easing Effect</b> accepts integers between 0 and 200. Your value was stripped to 12');
            settings.zwEasing = 12;
            $("#zwEasing").val('12');
        }

        if ( isNaN(settings.lensSize) || settings.lensSize < 20 || settings.lensSize > 2000 ) {
           show_alert('<b>Lens Size</b> accepts integers between 20 and 2000. Your value was reset to 200');
           settings.lensSize = 200;
           $("#lensSize").val('200');
        }

        if ( isNaN(settings.borderThickness) || settings.borderThickness < 0 || settings.borderThickness > 200 ) {
            show_alert('<b>Border Thickness</b> accepts integers between 0 and 200. Your value was reset to 1');
            settings.borderThickness = 1;
            $("#borderThickness").val('1');
        }

        if (isNaN(settings.lensFade) || settings.lensFade < 0 || settings.lensFade > 10000) {
            show_alert('<b>Fade Time</b> accepts integers between 0 and 10. Your value was reset to 1');
            settings.lensFade = 1;
            $("#lensFade").val('1');
        }

        if (isNaN(settings.tintOpacity) || settings.tintOpacity < 0 || settings.tintOpacity > 1) {
            show_alert('<b>Tint Opacity</b> accepts a number between 0 and 1. Your value was reset to 0.5');
            settings.tintOpacity = 0.5;
            $("#tintOpacity").val('0.5');
        }

        if (isNaN(settings.zwWidth) || settings.zwWidth < 0 || settings.zwWidth > 2000) {
            show_alert('<b>Zoom Window Width</b> accepts a number between 0 and 2000. Your value was reset to 400');
            settings.zwWidth = 400;
            $("#zwWidth").val('400');
        }

        if (isNaN(settings.zwHeight) || settings.zwHeight < 0 || settings.zwHeight > 2000) {
            show_alert('<b>Zoom Window Height</b> accepts a number between 0 and 2000. Your value was reset to 360');
            settings.zwHeight = 360;
            $("#zwHeight").val('360');
        }

        if ( isNaN(settings.zwBorderThickness) || settings.zwBorderThickness < 0 || settings.zwBorderThickness > 200 ) {
            show_alert('<b>Border Thickness</b> accepts integers between 0 and 200. Your value was reset to 4');
            settings.zwBorderThickness = 4;
            $("#zwBorderThickness").val('4');
        }

        if ( isNaN(settings.zwBorderRadius) || settings.zwBorderRadius < 0 || settings.zwBorderRadius > 500 ) {
            show_alert('<b>Rounded Corners</b> accepts integers between 0 and 500. Your value was reset to 0');
            settings.zwBorderRadius = 0;
            $("#zwBorderRadius").val('0');
        }

        if ( isNaN(settings.zwFade) || settings.zwFade < 0 || settings.zwFade > 10000 ) {
            show_alert('<b>Fade Time</b> accepts integers between 0 and 10. Your value was reset to 0');
            settings.zwFade = 0;
            $("#zwFade").val('0');
        }


    }

    function enableDisableFields(settings) {
        // activate all fields
        $("#tab_lens, #tab_zoom_window").removeClass('disabled');
        $("#tab_lens a").attr('href', '#lens_settings');
        $("#tab_zoom_window a").attr('href', '#zoom_window_settings');
        $("#lensSize").removeAttr('disabled');
        $("#lensColour").removeAttr('disabled');
        $("#lensBgImage").removeAttr('disabled');
        $("#tintColor").removeAttr('disabled');
        $("#tintOpacity").removeAttr('disabled');
        $("#lensColour").removeAttr('disabled');
        $("#lensOverlay").removeAttr('disabled');

        // Enable/disable fields
        switch ( settings['lensShape'] ) {
            case 'none' :
                $("#tab_lens, #tab_zoom_window").addClass('disabled');
                $("#tab_lens a").attr('href', '');
                $("#tab_zoom_window a").attr('href', '');
                $("#lensColour").attr('disabled', 'disabled');
                $("#lensBgImage").attr('disabled', 'disabled');
                if ( $('#lens_settings.active').length > 0 || $('#zoom_window_settings.active').length > 0 ) {
                    $('#tab_general a').trigger('click');
                }
                break;
            case 'square' :
            case 'round' :
                $("#tab_zoom_window").addClass('disabled');
                $("#tab_zoom_window a").attr('href', '');
                $("#lensColour").attr('disabled', 'disabled');
                $("#lensBgImage").attr('disabled', 'disabled');
                if ( $('#zoom_window_settings.active').length > 0 ) {
                    $('#tab_lens a').trigger('click');
                }
                break;
            case 'zoom_window' :
                $("#lensSize").attr('disabled', 'disabled');
                break;
        }

        if (settings.tint === false) {
            $("#tintColor").attr('disabled', 'disabled');
            $("#tintOpacity").attr('disabled', 'disabled');
        } else {
            $("#lensColour").attr('disabled', 'disabled');
            $("#lensOverlay").attr('disabled', 'disabled');
        }

    }

    $(".form-group input").change(updateZoomer);


    function show_alert( message ) {
        $("#alert_messages").html( 
            '<div class="alert alert-dismissable alert-danger">' +
            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
            message +
            '</div>');
    }

});
