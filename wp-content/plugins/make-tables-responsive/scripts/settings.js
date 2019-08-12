
// When the page loads it turns some of the fields into a color picker button
jQuery( document ).ready( function( $ ) {
    jQuery( '#mtr-even-row-background-color' ).wpColorPicker();
    jQuery( '#mtr-even-row-cell-border-color' ).wpColorPicker();
    jQuery( '#mtr-odd-row-background-color' ).wpColorPicker();
    jQuery( '#mtr-odd-row-cell-border-color' ).wpColorPicker();
    jQuery( '#mtr-single-row-cell-border-color' ).wpColorPicker();
    jQuery( '#mtr-single-even-row-background-color' ).wpColorPicker();
    jQuery( '#mtr-single-odd-row-background-color' ).wpColorPicker();
});

// Asks for confirmation and submits a form that resets the settings
function mtrResetSettings( type ) {
    if ( 'multi' === type ) {
        confirmResult = confirm( localizedMTR.confirmMultiReset );
    }
    if ( 'single' === type ) {
        confirmResult = confirm( localizedMTR.confirmSingleReset );
    }
    if ( 'global' === type ) {
        confirmResult = confirm( localizedMTR.confirmGlobalReset );
    }
    if ( true !== confirmResult ) {
        return;
    }
    document.getElementById( 'mtr-' + type + '-reset-hidden' ).value = type;
    document.getElementById( 'mtr-' + type + '-settings-form' ).submit();
}

// Shows the multi-row settings tab
function mtr_multi_row_settings() {
    jQuery( '#mtr-multi-row-settings' ).addClass( 'mtr-active' );
    jQuery( '#mtr-single-row-settings' ).removeClass( 'mtr-active' );
    jQuery( '#mtr-global-settings' ).removeClass( 'mtr-active' );
    jQuery( '#mtr-menu-link-multi' ).addClass( 'mtr-active' );
    jQuery( '#mtr-menu-link-single' ).removeClass( 'mtr-active' );
    jQuery( '#mtr-menu-link-global' ).removeClass( 'mtr-active' );
}

// Shows the single-row settings tab
function mtr_single_row_settings() {
    jQuery( '#mtr-multi-row-settings' ).removeClass( 'mtr-active' );
    jQuery( '#mtr-single-row-settings' ).addClass( 'mtr-active' );
    jQuery( '#mtr-global-settings' ).removeClass( 'mtr-active' );
    jQuery( '#mtr-menu-link-multi' ).removeClass( 'mtr-active' );
    jQuery( '#mtr-menu-link-single' ).addClass( 'mtr-active' );
    jQuery( '#mtr-menu-link-global' ).removeClass( 'mtr-active' );
}

// Shows the global settings tab
function mtr_global_settings() {
    jQuery( '#mtr-multi-row-settings' ).removeClass( 'mtr-active' );
    jQuery( '#mtr-single-row-settings' ).removeClass( 'mtr-active' );
    jQuery( '#mtr-global-settings' ).addClass( 'mtr-active' );
    jQuery( '#mtr-menu-link-multi' ).removeClass( 'mtr-active' );
    jQuery( '#mtr-menu-link-single' ).removeClass( 'mtr-active' );
    jQuery( '#mtr-menu-link-global' ).addClass( 'mtr-active' );
}
