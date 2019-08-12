<?php

/*
Plugin Name: Make Tables Responsive
Description: Automatically makes the HTML tables in your posts, pages, and widgets to be responsive (mobile-friendly).
Author: Nikolay Nikolov
Author URI: https://nikolaydev.com/
Text Domain: make-tables-responsive
Domain Path: /languages
Version: 1.5.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( "MAKE_TABLES_RESPONSIVE_VERSION", "1.5.1" );

// Registers, enqueues, and localizes the script we use in the plugin settings page.
add_action( 'admin_enqueue_scripts', 'mtr_add_scripts_for_settings' );

// Adds a submenu for out settings page to the options admin menu.
add_action( 'admin_menu', 'mtr_plugin_settings_menu' );

// Displays some CSS code on the plugin settings page.
add_action( 'admin_head', 'mtr_add_css_on_settings_page' );

/**
 * Filters the content of posts and pages and if it sees that it contains HTML tables it makes the HTML changes needed for them to become responsive tables.
 * (These changes are not enough, we also display CSS code in another function).
 */
add_filter( 'the_content', 'mtr_change_the_tables', 99999999999 );

/**
 * Filters the content of widgets (text, custom HTML and maybe some third party widgets) and if it sees that it contains HTML tables it makes
 * the HTML changes needed for them to become responsive tables.
 * (These changes are not enough, we also display CSS code in another function).
 */
add_filter( 'widget_text', 'mtr_change_the_tables', 99999999999 );

// Displays the CSS code for the front end part of the site, taking into account the plugin settings.
add_action( 'wp_head', 'mtr_add_css_on_front_end', 99999999999 );

/*
 * Displays the CSS code for the front end part of the site (taking into account the plugin settings)
 * for AMP pages made with https://wordpress.org/plugins/accelerated-mobile-pages/.
 */
add_action( 'amp_post_template_css', 'mtr_add_css_on_front_end', 99999999999 );

// Adds a link to the settings page in the plugin action links.
add_filter( 'plugin_action_links', 'mtr_add_settings_plugin_action_link', 10, 2 );

// Registers, enqueues, and localizes the script we use in the plugin settings page.
function mtr_add_scripts_for_settings() {
    if ( isset( $_GET['page'] ) && 'make-tables-responsive' == $_GET['page'] ) {
        wp_enqueue_style( 'wp-color-picker' );
        wp_register_script( 'mtr-settings-script-handle', plugins_url( 'scripts/settings.js', __FILE__ ),
            array( 'wp-color-picker' ), MAKE_TABLES_RESPONSIVE_VERSION, false );
        $localize = array(
            'confirmMultiReset' => esc_js( __( 'Are you sure? Your current multi-row settings will be permanently lost.', 'make-tables-responsive' ) ),
            'confirmSingleReset' => esc_js( __( 'Are you sure? Your current single-row settings will be permanently lost.', 'make-tables-responsive' ) ),
            'confirmGlobalReset' => esc_js( __( 'Are you sure? Your current global settings will be permanently lost.', 'make-tables-responsive' ) ),
        );
        wp_localize_script( 'mtr-settings-script-handle', 'localizedMTR', $localize );
        wp_enqueue_script( 'mtr-settings-script-handle' );
    }
}

// Adds a submenu for out settings page to the options admin menu.
function mtr_plugin_settings_menu() {
    add_options_page(
        esc_html__( 'Make Tables Responsive', 'make-tables-responsive' ),
        esc_html__( 'Make Tables Responsive', 'make-tables-responsive' ),
        'manage_options',
        'make-tables-responsive',
        'mtr_admin_settings_page'
    );
}

// Creates the plugin settings admin page.
function mtr_admin_settings_page() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'make-tables-responsive' ) );
    }

    $status = 'normal';
    $class_multi = 'mtr-active';
    $class_single = '';
    $class_global = '';

    if ( isset( $_POST['mtr-multi-submit'] ) ) {

        check_admin_referer( 'mtr-multi-save-settings-nonce' );

        do {

            $status = "saved";

            if ( ! is_numeric( $_POST['mtr-enable-on-screen-size-below'] ) || $_POST['mtr-enable-on-screen-size-below'] < 2 ) {
                $status = 'invalid';
                break;
            }

            if ( ! mtr_is_hex_color( $_POST['mtr-even-row-background-color'] ) || ! mtr_is_hex_color( $_POST['mtr-even-row-cell-border-color'] )
                || ! mtr_is_hex_color( $_POST['mtr-odd-row-background-color'] ) || ! mtr_is_hex_color( $_POST['mtr-odd-row-cell-border-color'] ) ) {
                $status = 'invalid';
                break;
            }

            if ( '' != $_POST['mtr-limit-left-side'] && ( ! is_numeric( $_POST['mtr-limit-left-side'] )
                || $_POST['mtr-limit-left-side'] < 1 || $_POST['mtr-limit-left-side'] > 100 ) ) {
                $status = 'invalid';
                break;
            }

            if ( '' != $_POST['mtr-limit-right-side'] && ( ! is_numeric( $_POST['mtr-limit-right-side'] )
                || $_POST['mtr-limit-right-side'] < 1 || $_POST['mtr-limit-right-side'] > 100 ) ) {
                $status = 'invalid';
                break;
            }

            $disable_styling = '';
            if ( isset( $_POST['mtr-disable-styling'] ) ) {
                $disable_styling = 'checked';
            }

            $hide_tfoot = '';
            if ( isset( $_POST['mtr-hide-tfoot'] ) ) {
                $hide_tfoot = 'checked';
            }

            $rtl = '';
            if ( isset( $_POST['mtr-rtl'] ) ) {
                $rtl = 'checked';
            }

            $enable_in_widgets = '';
            if ( isset( $_POST['mtr-enable-in-widgets'] ) ) {
                $enable_in_widgets = 'checked';
            }

            $enable_in_content = '';
            if ( isset( $_POST['mtr-enable-in-content'] ) ) {
                $enable_in_content = 'checked';
            }

            update_option( 'mtr-enable-on-screen-size-below', intval( $_POST['mtr-enable-on-screen-size-below'] ) );
            update_option( 'mtr-enable-in-content', mtr_sanitize_checkbox_checked( $enable_in_content ) );
            update_option( 'mtr-enable-in-widgets', mtr_sanitize_checkbox_checked( $enable_in_widgets ) );
            update_option( 'mtr-hide-tfoot', mtr_sanitize_checkbox_checked( $hide_tfoot ) );
            update_option( 'mtr-limit-left-side', mtr_sanitize_number_or_empty( $_POST['mtr-limit-left-side'] ) );
            update_option( 'mtr-limit-right-side', mtr_sanitize_number_or_empty( $_POST['mtr-limit-right-side'] ) );
            update_option( 'mtr-rtl', mtr_sanitize_checkbox_checked( $rtl ) );
            update_option( 'mtr-disable-styling', mtr_sanitize_checkbox_checked( $disable_styling ) );
            update_option( 'mtr-even-row-background-color', sanitize_hex_color( $_POST['mtr-even-row-background-color'] ) );
            update_option( 'mtr-even-row-cell-border-color', sanitize_hex_color( $_POST['mtr-even-row-cell-border-color'] ) );
            update_option( 'mtr-odd-row-background-color', sanitize_hex_color( $_POST['mtr-odd-row-background-color'] ) );
            update_option( 'mtr-odd-row-cell-border-color', sanitize_hex_color( $_POST['mtr-odd-row-cell-border-color'] ) );

        } while( false );


    } elseif ( isset( $_POST['mtr-single-submit'] ) ) {

        check_admin_referer( 'mtr-save-single-settings-nonce' );

        $class_multi = '';
        $class_single = 'mtr-active';
        $class_global = '';

        do {

            $status = "saved";

            if ( ! is_numeric( $_POST['mtr-single-enable-on-screen-size-below'] ) || $_POST['mtr-single-enable-on-screen-size-below'] < 2 ) {
                $status = 'invalid';
                break;
            }

            if ( ! is_numeric( $_POST['mtr-single-row-columns'] ) || $_POST['mtr-single-row-columns'] < 1 ) {
                $status = 'invalid';
                break;
            }

            if ( ! in_array( $_POST['mtr-single-row-layout'],
                Array( '1-column', '2-columns', '3-columns', '4-columns', 'fluid-row' ), true ) ) {
                $status = 'invalid';
                break;
            }

            if ( ! in_array( $_POST['mtr-single-row-cell-align'], Array( 'no-change', 'left', 'center', 'right' ), true ) ) {
                $status = 'invalid';
                break;
            }

            if ( ! mtr_is_hex_color( $_POST['mtr-single-even-row-background-color'] ) || ! mtr_is_hex_color( $_POST['mtr-single-odd-row-background-color'] )
                || ! mtr_is_hex_color( $_POST['mtr-single-row-cell-border-color'] ) ) {
                $status = 'invalid';
                break;
            }

            $enable_in_content = '';
            if ( isset( $_POST['mtr-single-enable-in-content'] ) ) {
                $enable_in_content = 'checked';
            }

            $enable_in_widgets = '';
            if ( isset( $_POST['mtr-single-enable-in-widgets'] ) ) {
                $enable_in_widgets = 'checked';
            }

            $disable_styling = '';
            if ( isset( $_POST['mtr-single-disable-styling'] ) ) {
                $disable_styling = 'checked';
            }

            update_option( 'mtr-single-enable-on-screen-size-below', intval( $_POST['mtr-single-enable-on-screen-size-below'] ) );
            update_option( 'mtr-single-row-columns', intval( $_POST['mtr-single-row-columns'] ) );
            update_option( 'mtr-single-enable-in-content', mtr_sanitize_checkbox_checked( $enable_in_content ) );
            update_option( 'mtr-single-enable-in-widgets', mtr_sanitize_checkbox_checked( $enable_in_widgets ) );
            update_option( 'mtr-single-row-layout', sanitize_html_class( $_POST['mtr-single-row-layout'] ) );
            update_option( 'mtr-single-row-cell-align', sanitize_html_class( $_POST['mtr-single-row-cell-align'] ) );
            update_option( 'mtr-single-disable-styling', mtr_sanitize_checkbox_checked( $disable_styling ) );
            update_option( 'mtr-single-even-row-background-color', sanitize_hex_color( $_POST['mtr-single-even-row-background-color'] ) );
            update_option( 'mtr-single-odd-row-background-color', sanitize_hex_color( $_POST['mtr-single-odd-row-background-color'] ) );
            update_option( 'mtr-single-row-cell-border-color', sanitize_hex_color( $_POST['mtr-single-row-cell-border-color'] ) );


        } while( false );


    } elseif ( isset( $_POST['mtr-global-submit'] ) ) {

        check_admin_referer( 'mtr-save-global-settings-nonce' );

        $class_multi = '';
        $class_single = '';
        $class_global = 'mtr-active';

        do {

            $status = "saved";

            if ( mtr_sanitize_html_class_or_id_list( $_POST['mtr-exclude-html-classes'] ) != $_POST['mtr-exclude-html-classes'] ||
                mtr_sanitize_html_class_or_id_list( $_POST['mtr-exclude-html-ids'] ) != $_POST['mtr-exclude-html-ids'] ) {
                $status = 'invalid';
                break;
            }

            if ( ! mtr_is_comma_separated_numbers( mtr_strip_whitespace( $_POST['mtr-exclude-post-page-ids'] ) )
                && ! empty( $_POST['mtr-exclude-post-page-ids'] ) ) {
                $status = 'invalid';
                break;
            }

            if ( ! mtr_is_comma_separated_numbers( mtr_strip_whitespace( $_POST['mtr-enable-only-post-page-ids'] ) )
                && ! empty( $_POST['mtr-enable-only-post-page-ids'] ) ) {
                $status = 'invalid';
                break;
            }

            update_option( 'mtr-exclude-html-classes', mtr_sanitize_html_class_or_id_list( $_POST['mtr-exclude-html-classes'] ) );
            update_option( 'mtr-exclude-html-ids', mtr_sanitize_html_class_or_id_list( $_POST['mtr-exclude-html-ids'] ) );
            update_option( 'mtr-exclude-post-page-ids', mtr_sanitize_list_of_numbers( $_POST['mtr-exclude-post-page-ids'] ) );
            update_option( 'mtr-enable-only-post-page-ids', mtr_sanitize_list_of_numbers( $_POST['mtr-enable-only-post-page-ids'] ) );

        } while( false );

    } elseif ( isset( $_POST['mtr-multi-reset-hidden'] ) && 'multi' === $_POST['mtr-multi-reset-hidden'] ) {

        check_admin_referer( 'mtr-multi-save-settings-nonce' );

        delete_option( 'mtr-enable-on-screen-size-below' );
        delete_option( 'mtr-enable-in-content' );
        delete_option( 'mtr-enable-in-widgets' );
        delete_option( 'mtr-hide-tfoot' );
        delete_option( 'mtr-limit-left-side' );
        delete_option( 'mtr-limit-right-side' );
        delete_option( 'mtr-rtl' );
        delete_option( 'mtr-disable-styling' );
        delete_option( 'mtr-even-row-background-color' );
        delete_option( 'mtr-even-row-cell-border-color' );
        delete_option( 'mtr-odd-row-background-color' );
        delete_option( 'mtr-odd-row-cell-border-color' );

        $status = "saved";

    } elseif ( isset( $_POST['mtr-single-reset-hidden'] ) && 'single' === $_POST['mtr-single-reset-hidden'] ) {

        check_admin_referer( 'mtr-save-single-settings-nonce' );

        $class_multi = '';
        $class_single = 'mtr-active';
        $class_global = '';

        delete_option( 'mtr-single-enable-on-screen-size-below' );
        delete_option( 'mtr-single-row-columns' );
        delete_option( 'mtr-single-enable-in-content' );
        delete_option( 'mtr-single-enable-in-widgets' );
        delete_option( 'mtr-single-row-layout' );
        delete_option( 'mtr-single-row-cell-align' );
        delete_option( 'mtr-single-disable-styling' );
        delete_option( 'mtr-single-even-row-background-color' );
        delete_option( 'mtr-single-odd-row-background-color' );
        delete_option( 'mtr-single-row-cell-border-color' );

        $status = "saved";

    } elseif ( isset( $_POST['mtr-global-reset-hidden'] ) && 'global' === $_POST['mtr-global-reset-hidden'] ) {

        check_admin_referer( 'mtr-save-global-settings-nonce' );

        $class_multi = '';
        $class_single = '';
        $class_global = 'mtr-active';

        delete_option( 'mtr-exclude-html-classes' );
        delete_option( 'mtr-exclude-html-ids' );
        delete_option( 'mtr-exclude-post-page-ids' );
        delete_option( 'mtr-enable-only-post-page-ids' );

        $status = "saved";

    }

    $settings = mtr_get_settings_array();

    ?>

    <div class="wrap">

        <div class="mtr-logo-div mtr-white-box">
            <b>
                <img class="mtr-logo" src="<?php echo esc_url( mtr_plugin_image_url( 'logo32px.png' ) ); ?>" />
                <?php esc_html_e( 'Make Tables Responsive', 'make-tables-responsive' ) ?>
            </b>
        </div>

        <?php
        if ( 'saved' == $status ) {
            echo '<div class="mtr-done-message mtr-white-box mtr-100-percent"><strong>'
                . esc_html__( 'Done', 'make-tables-responsive' ) . '</strong></div>';
        } elseif( 'invalid' == $status ) {
            echo '<div class="mtr-error-message mtr-white-box mtr-100-percent"><strong>'
                . esc_html__( 'Invalid Data', 'make-tables-responsive' ) . '</strong></div>';
        }
        ?>

        <div id="mtr-settings-menu">
            <a id="mtr-menu-link-multi" class="mtr-menu-link <?php echo esc_attr( $class_multi ); ?> mtr-white-box" href="javascript:mtr_multi_row_settings()">
                <?php esc_html_e( 'Multi-Row Tables', 'make-tables-responsive' ); ?>
            </a>
            <a id="mtr-menu-link-single" class="mtr-menu-link <?php echo esc_attr( $class_single ); ?> mtr-white-box" href="javascript:mtr_single_row_settings()">
                <?php esc_html_e( 'Single-Row Tables', 'make-tables-responsive' ); ?>
            </a>
            <a id="mtr-menu-link-global" class="mtr-menu-link <?php echo esc_attr( $class_global ); ?> mtr-white-box" href="javascript:mtr_global_settings()">
                <?php esc_html_e( 'Global Settings', 'make-tables-responsive' ); ?>
            </a>
        </div>

        <div id="mtr-multi-row-settings" class="mtr-white-box <?php echo esc_attr( $class_multi ); ?> mtr-100-percent">

            <form id="mtr-multi-settings-form" action="" method="post">

                <h2 class="mtr-margin-top-10"><?php esc_html_e( 'Affect Multi-Row Tables', 'make-tables-responsive' ); ?></h2>

                <p>
                    <label for="mtr-enable-on-screen-size-below">
                        <?php esc_html_e( 'Enable on screens smaller than:', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-enable-on-screen-size-below'] ); ?>"
                        id="mtr-enable-on-screen-size-below" name="mtr-enable-on-screen-size-below" /> px
                </p>
                <p>
                    <input type="checkbox" <?php echo mtr_sanitize_checkbox_checked( $settings['mtr-enable-in-content'] ); ?>
                        id="mtr-enable-in-content" name="mtr-enable-in-content" />
                    <label for="mtr-enable-in-content">
                        <?php
                            esc_html_e( 'Enable in post/page content', 'make-tables-responsive' );
                        ?>
                    </label>
                </p>
                <p>
                    <input type="checkbox" <?php echo mtr_sanitize_checkbox_checked( $settings['mtr-enable-in-widgets'] ); ?>
                        id="mtr-enable-in-widgets" name="mtr-enable-in-widgets" />
                    <label for="mtr-enable-in-widgets">
                        <?php
                            esc_html_e( 'Enable in widgets', 'make-tables-responsive' );
                        ?>
                    </label>
                </p>

                <hr>

                <h2><?php esc_html_e( 'Layout of Multi-Row Tables', 'make-tables-responsive' ); ?></h2>
                <p>
                    <input type="checkbox" <?php echo mtr_sanitize_checkbox_checked( $settings['mtr-hide-tfoot'] ); ?>
                        id="mtr-hide-tfoot" name="mtr-hide-tfoot" />
                    <label for="mtr-hide-tfoot">
                        <?php
                            esc_html_e( 'Hide the "tfoot" tag', 'make-tables-responsive' );
                        ?>
                    </label>
                </p>
                <p>
                    <label for="mtr-limit-left-side">
                        <?php esc_html_e( 'Limit the left side width to (leave empty for no limit):', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-limit-left-side'] ); ?>"
                        id="mtr-limit-left-side" name="mtr-limit-left-side" /> %
                </p>
                <p>
                    <label for="mtr-limit-right-side">
                        <?php esc_html_e( 'Limit the right side width to (leave empty for no limit):', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-limit-right-side'] ); ?>"
                        id="mtr-limit-right-side" name="mtr-limit-right-side" /> %
                </p>
                <p>
                    <input type="checkbox" <?php echo mtr_sanitize_checkbox_checked( $settings['mtr-rtl'] ); ?>
                        id="mtr-rtl" name="mtr-rtl" />
                    <label for="mtr-rtl">
                        <?php
                            esc_html_e( 'Put the column names on the right side (suitable for RTL languages)', 'make-tables-responsive' );
                        ?>
                    </label>
                </p>

                <hr>

                <h2><?php esc_html_e( 'Style of Multi-Row Tables', 'make-tables-responsive' ); ?></h2>
                <p>
                    <input type="checkbox" <?php echo mtr_sanitize_checkbox_checked( $settings['mtr-disable-styling'] ); ?>
                        id="mtr-disable-styling" name="mtr-disable-styling" />
                    <label for="mtr-disable-styling">
                        <?php
                            esc_html_e( 'Disable styling', 'make-tables-responsive' );
                        ?>
                    </label>
                </p>
                <p>
                    <label for="mtr-even-row-background-color">
                        <?php esc_html_e( 'Background color of cells in even rows:', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-even-row-background-color'] ); ?>"
                        id="mtr-even-row-background-color" name="mtr-even-row-background-color" />
                </p>
                <p>
                    <label for="mtr-even-row-cell-border-color">
                        <?php esc_html_e( 'Border color for cells in even rows:', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-even-row-cell-border-color'] ); ?>"
                        id="mtr-even-row-cell-border-color" name="mtr-even-row-cell-border-color" />
                </p>
                <p>
                    <label for="mtr-odd-row-background-color">
                        <?php esc_html_e( 'Background color of cells in odd rows:', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-odd-row-background-color'] ); ?>"
                        id="mtr-odd-row-background-color" name="mtr-odd-row-background-color" />
                </p>
                <p>
                    <label for="mtr-odd-row-cell-border-color">
                        <?php esc_html_e( 'Border color for cells in odd rows:', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-odd-row-cell-border-color'] ); ?>"
                        id="mtr-odd-row-cell-border-color" name="mtr-odd-row-cell-border-color" />
                </p>

                <hr>

                <p>
                    <input id="mtr-multi-submit" name="mtr-multi-submit" class="button button-primary" type="submit"
                        value="<?php esc_attr_e( 'Save Multi-Row Settings', 'make-tables-responsive' ); ?>" />
                    <input id="mtr-multi-reset-hidden" name="mtr-multi-reset-hidden" type="hidden" value="no" />
                    <input id="mtr-multi-reset" class="button" type="button" onclick="mtrResetSettings('multi')"
                        value="<?php esc_attr_e( 'Reset Multi-Row Settings', 'make-tables-responsive' ); ?>" />
                </p>
                <?php wp_nonce_field( 'mtr-multi-save-settings-nonce' ); ?>
            </form>
        </div>

        <div id="mtr-single-row-settings" class="mtr-white-box <?php echo esc_attr( $class_single ); ?> mtr-100-percent">

            <form id="mtr-single-settings-form" action="" method="post">

                <h2 class="mtr-margin-top-10"><?php esc_html_e( 'Affect Single-Row Tables', 'make-tables-responsive' ); ?></h2>
                <p>
                    <label for="mtr-single-enable-on-screen-size-below">
                        <?php esc_html_e( 'Enable on screens smaller than:', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-single-enable-on-screen-size-below'] ); ?>"
                        id="mtr-single-enable-on-screen-size-below" name="mtr-single-enable-on-screen-size-below" />
                    <?php esc_html_e( 'px', 'make-tables-responsive' ); ?>
                </p>
                <p>
                    <label for="mtr-single-row-columns">
                        <?php esc_html_e( 'Enable for tables with at least:', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-single-row-columns'] ); ?>"
                        id="mtr-single-row-columns" name="mtr-single-row-columns" />
                    <?php esc_html_e( 'columns', 'make-tables-responsive' ); ?>
                </p>
                <p>
                    <input type="checkbox" <?php echo mtr_sanitize_checkbox_checked( $settings['mtr-single-enable-in-content'] ); ?>
                        id="mtr-single-enable-in-content" name="mtr-single-enable-in-content" />
                    <label for="mtr-single-enable-in-content">
                        <?php
                            esc_html_e( 'Enable in post/page content', 'make-tables-responsive' );
                        ?>
                    </label>
                </p>
                <p>
                    <input type="checkbox" <?php echo mtr_sanitize_checkbox_checked( $settings['mtr-single-enable-in-widgets'] ); ?>
                        id="mtr-single-enable-in-widgets" name="mtr-single-enable-in-widgets" />
                    <label for="mtr-single-enable-in-widgets">
                        <?php
                            esc_html_e( 'Enable in widgets', 'make-tables-responsive' );
                        ?>
                    </label>
                </p>

                <hr>

                <h2><?php esc_html_e( 'Layout of Single-Row Tables', 'make-tables-responsive' ); ?></h2>
                <p>
                    <label for="mtr-single-row-layout">
                        <?php
                            esc_html_e( 'Layout:', 'make-tables-responsive' );
                        ?>
                    </label>
                    <br>
                    <?php
                        mtr_setting_select(
                            'mtr-single-row-layout',
                            Array( '1-column', '2-columns', '3-columns', '4-columns', 'fluid-row' ),
                            Array(
                                __( 'Make 1 column', 'make-tables-responsive' ),
                                __( 'Make 2 equal columns', 'make-tables-responsive' ),
                                __( 'Make 3 equal columns', 'make-tables-responsive' ),
                                __( 'Make 4 equal columns', 'make-tables-responsive' ),
                                __( 'Make a fluid row without styling', 'make-tables-responsive' ),
                            ),
                            $settings['mtr-single-row-layout']
                        );
                    ?>
                </p>
                <p>
                    <label for="mtr-single-row-cell-align">
                        <?php
                            esc_html_e( 'Align cell content:', 'make-tables-responsive' );
                        ?>
                    </label>
                    <br>
                    <?php
                        mtr_setting_select(
                            'mtr-single-row-cell-align',
                            Array( 'no-change', 'left', 'center', 'right' ),
                            Array(
                                __( 'No change', 'make-tables-responsive' ),
                                __( 'Left', 'make-tables-responsive' ),
                                __( 'Center', 'make-tables-responsive' ),
                                __( 'Right', 'make-tables-responsive' ),
                            ),
                            $settings['mtr-single-row-cell-align']
                        );
                    ?>
                </p>

                <hr>

                <h2><?php esc_html_e( 'Style of Single-Row Tables', 'make-tables-responsive' ); ?></h2>
                <p>
                    <input type="checkbox" <?php echo mtr_sanitize_checkbox_checked( $settings['mtr-single-disable-styling'] ); ?>
                        id="mtr-single-disable-styling" name="mtr-single-disable-styling" />
                    <label for="mtr-single-disable-styling">
                        <?php
                            esc_html_e( 'Disable styling', 'make-tables-responsive' );
                        ?>
                    </label>
                </p>
                <p>
                    <label for="mtr-single-even-row-background-color">
                        <?php esc_html_e( 'Background color of cells in even rows:', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-single-even-row-background-color'] ); ?>"
                        id="mtr-single-even-row-background-color" name="mtr-single-even-row-background-color" />
                </p>
                <p>
                    <label for="mtr-single-odd-row-background-color">
                        <?php esc_html_e( 'Background color of cells in odd rows:', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-single-odd-row-background-color'] ); ?>"
                        id="mtr-single-odd-row-background-color" name="mtr-single-odd-row-background-color" />
                </p>
                <p>
                    <label for="mtr-single-row-cell-border-color">
                        <?php esc_html_e( 'Border color for cells:', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-single-row-cell-border-color'] ); ?>"
                        id="mtr-single-row-cell-border-color" name="mtr-single-row-cell-border-color" />
                </p>

                <hr>

                <p>
                    <input id="mtr-single-submit" name="mtr-single-submit" class="button button-primary" type="submit"
                        value="<?php esc_attr_e( 'Save Single-Row Settings', 'make-tables-responsive' ); ?>" />
                    <input id="mtr-single-reset-hidden" name="mtr-single-reset-hidden" type="hidden" value="no" />
                    <input id="mtr-single-reset" class="button" type="button" onclick="mtrResetSettings('single')"
                        value="<?php esc_attr_e( 'Reset Single-Row Settings', 'make-tables-responsive' ); ?>" />
                </p>
                <?php wp_nonce_field( 'mtr-save-single-settings-nonce' ); ?>
            </form>
        </div>

        <div id="mtr-global-settings" class="mtr-white-box <?php echo esc_attr( $class_global ); ?> mtr-100-percent">

            <form id="mtr-global-settings-form" action="" method="post">

                <h2><?php esc_html_e( 'Global Settings', 'make-tables-responsive' ); ?></h2>
                <p>
                    <label for="mtr-exclude-html-classes">
                        <?php esc_html_e( 'Disable by class of the table tag (comma-separated list, without a dot):', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-exclude-html-classes'] ); ?>"
                        id="mtr-exclude-html-classes" name="mtr-exclude-html-classes" />
                </p>
                <p>
                    <label for="mtr-exclude-html-ids">
                        <?php esc_html_e( 'Disable by ID of the table tag (comma-separated list, without a hash):', 'make-tables-responsive' ); ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-exclude-html-ids'] ); ?>"
                        id="mtr-exclude-html-ids" name="mtr-exclude-html-ids" />
                </p>
                <p>
                    <label for="mtr-exclude-post-page-ids">
                        <?php
                            esc_html_e( 'Disable in post and page content by post/page ID (comma-separated list of numbers, does not affect widgets):',
                                'make-tables-responsive' );
                        ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-exclude-post-page-ids'] ); ?>"
                        id="mtr-exclude-post-page-ids" name="mtr-exclude-post-page-ids" />
                </p>
                <p>
                    <label for="mtr-enable-only-post-page-ids">
                        <?php
                            esc_html_e( 'Enable ONLY in post and page content by post/page ID (comma-separated list of numbers, '
                                . 'leave empty to enable everywhere, does not affect widgets):', 'make-tables-responsive' );
                        ?>
                    </label>
                    <br>
                    <input type="text" value="<?php echo esc_attr( $settings['mtr-enable-only-post-page-ids'] ); ?>"
                        id="mtr-enable-only-post-page-ids" name="mtr-enable-only-post-page-ids" />
                </p>

                <hr>

                <p>
                    <input id="mtr-global-submit" name="mtr-global-submit" class="button button-primary" type="submit"
                        value="<?php esc_attr_e( 'Save Global Settings', 'make-tables-responsive' ); ?>" />
                    <input id="mtr-global-reset-hidden" name="mtr-global-reset-hidden" type="hidden" value="no" />
                    <input id="mtr-global-reset" class="button" type="button" onclick="mtrResetSettings('global')"
                        value="<?php esc_attr_e( 'Reset Global Settings', 'make-tables-responsive' ); ?>" />
                </p>
                <?php wp_nonce_field( 'mtr-save-global-settings-nonce' ); ?>
            </form>
        </div>
    </div>

    <?php

}

// Displays some CSS code on the plugin settings page.
function mtr_add_css_on_settings_page() {
    if ( isset( $_GET['page'] ) && 'make-tables-responsive' == $_GET['page'] ) {
    ?>
    <style type="text/css">

    #mtr-enable-on-screen-size-below,
    #mtr-single-enable-on-screen-size-below,
    #mtr-single-row-columns,
    #mtr-limit-left-side,
    #mtr-limit-right-side {
        width: 50px;
        text-align: right;
    }

    .mtr-logo {
        float: left;
        height: 32px;
        margin-right: 4px;
    }

    .mtr-white-box {
        background: #fff;
        -webkit-box-shadow: 1px 1px 3px 0 rgba(0, 0, 0, 0.15);
        box-shadow: 1px 1px 3px 0 rgba(0, 0, 0, 0.15);
        box-sizing: border-box;
        float: left;
        padding: 10px 18px;
        margin-bottom: 15px;
    }

    .mtr-done-message {
        border-left: 5px solid #338844;
        color: #338844;
    }

    .mtr-error-message {
        border-left: 5px solid #cc8822;
        color: #cc8822;
    }

    .mtr-logo-div {
        padding: 15px;
        margin: 0 0 15px 0;
        width: 100%;
    }

    .mtr-logo-div b {
        padding: 0;
        margin: 0;
        line-height: 1.36;
        font-size: 22px;
        color: #222;
    }

    .mtr-100-percent {
        width: 100%;
    }

    .mtr-float-left {
        float: left;
    }

    .mtr-margin-top-10 {
        margin-top: 10px;
    }

    .mtr-menu-link,
    .mtr-menu-link:hover,
    .mtr-menu-link:active,
    .mtr-menu-link:visited {
        text-decoration: none;
        color: #73787d;
        margin-right: 10px;
        font-size: 16px;
        font-weight: 600;
        display: inline-block;
        border-left: 5px solid #ccc;

    }

    .mtr-menu-link:hover,
    .mtr-menu-link.mtr-active {
        border-left: 5px solid #d85171;
        color: #23282d;
    }


    #mtr-settings-menu {
        float: left;
    }

    #mtr-multi-row-settings,
    #mtr-single-row-settings,
    #mtr-global-settings {
        display: none;
    }

    #mtr-multi-row-settings.mtr-active,
    #mtr-single-row-settings.mtr-active,
    #mtr-global-settings.mtr-active {
        display: block;
    }

    .mtr-white-box hr {
        margin-top: 25px;
        margin-bottom: 21px;
    }

    </style>
    <?php
    }
}

/**
 * Filters the content of posts and pages and if it sees that it contains HTML tables it makes the HTML changes needed for them to become resposive tables.
 * (These changes are not enough, we also display CSS code in another function).
 * @param string $content
 * @return string
 */
function mtr_change_the_tables( $content ) {

    // We only make changes on the front-end if there are tables in the post/page and the numbers of opening and closing table tags are the same.
    if ( ! is_admin() && strpos( $content, '<table' ) !== false && substr_count( $content, '<table' ) == substr_count( $content, '</table>' ) ) {

        // We get the plugin settings we need
        $settings = mtr_get_settings_array();

        $current_filter = current_filter();

        // If the plugin is not enabled for the post/page content and we are in the content filter, we return the content without changes
        if ( 'the_content' === $current_filter && 'checked' !== $settings['mtr-enable-in-content'] && 'checked' !== $settings['mtr-single-enable-in-content'] ) {
            return $content;
        }

        // If the plugin is not enabled for widgets and we are in the widget text filter, we return the content without changes
        if ( 'widget_text' === $current_filter && 'checked' !== $settings['mtr-enable-in-widgets'] && 'checked' !== $settings['mtr-single-enable-in-widgets'] ) {
            return $content;
        }

        // This part skips posts and pages that are not enabled by ID if the setting to enable ONLY on chosen IDs is used
        if ( 'the_content' === $current_filter ) {
            $enable_only_ids_setting = mtr_strip_whitespace( $settings['mtr-enable-only-post-page-ids'] );
            if ( ! empty( $enable_only_ids_setting ) ) {
                $enable_ids = explode( ',', $enable_only_ids_setting );
                $current_id = get_the_ID();
                if ( ! in_array( $current_id, $enable_ids ) ) {
                    return $content;
                }
            }
        }

        // This part skips posts and pages that are disabled by ID
        if ( 'the_content' === $current_filter ) {
            $exclude_ids_setting = mtr_strip_whitespace( $settings['mtr-exclude-post-page-ids'] );
            if ( ! empty( $exclude_ids_setting ) ) {
                $exclude_ids = explode( ',', $exclude_ids_setting );
                $current_id = get_the_ID();
                foreach ( $exclude_ids as $exclude_id ) {
                    if ( $exclude_id == $current_id ) {
                        return $content;
                    }
                }
            }
        }

        /*
         * Here we make an array of the original HTML tables code, so we can replace it at the end with the new code. Getting the code with the DOM
         * functions seems to make some small changes to it (probably whitespace or something), and we cannot find and replace it.
         */
        $tables_exploded = array();
        $explode_open_result =  explode( '<table', $content);
        for ( $i = 1; $i < count( $explode_open_result ); $i++ ) {
            $explode_close_result =  explode( '</table>', $explode_open_result[ $i ] );
            $tables_exploded[] = '<table' . $explode_close_result[0] . '</table>';
        }

        // We load the content in a DOMDocument object
        $dom = new DOMDocument();
        $internalErrors = libxml_use_internal_errors( true );
        $dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );
        libxml_use_internal_errors( $internalErrors );

        $new_content = $content;

        // We get all the tables
        $tables = mtr_get_tags( $dom, 'table' );

        $table_count = 0;

        // We go through all the tables and make the changes
        foreach ( $tables as $table ) {

            $skip_table = 'no';

            $table_outer_html = mtr_get_outer_html( $table );

            /*
             * If we find a table inside a table, we stop everything and just return the original content for the whole post/page because the
             * $tables_exploded array is not correct now. Might make it in the future to only skip the table.
             */
            if ( substr_count( $table_outer_html, '<table' ) > 1 ) {
                return $content;
            }

            // If there are merged cells we skip the table, since it is not supported at this time
            if ( strpos( $table_outer_html, ' rowspan=' ) !== false || strpos( $table_outer_html, ' colspan=' ) !== false ) {
                $table_count++;
                continue;
            }

            // Here we check if we need to skip any tables that are excluded by class
            if ( ! empty( $settings['mtr-exclude-html-classes'] ) && $table->hasAttribute( 'class' ) ) {
                $table_class_attribute = $table->getAttribute( 'class' );
                $table_classes = explode( ' ', $table_class_attribute );
                $exclude_classes_setting = mtr_strip_whitespace( $settings['mtr-exclude-html-classes'] );
                $exclude_classes = explode( ',', $exclude_classes_setting );
                foreach ( $exclude_classes as $exclude_class ) {
                    foreach ( $table_classes as $table_class ) {
                        if ( $exclude_class == $table_class && ! empty( $table_class ) ) {
                            $skip_table = 'yes';
                            break 2;
                        }
                    }
                }
            }

            // Here we check if we need to skip any tables that are excluded by ID
            if ( ! empty( $settings['mtr-exclude-html-ids'] ) && $table->hasAttribute( 'id' ) && 'yes' != $skip_table ) {
                $table_id = $table->getAttribute( 'id' );
                $exclude_ids_setting = mtr_strip_whitespace( $settings['mtr-exclude-html-ids'] );
                $exclude_ids = explode( ',', $exclude_ids_setting );
                foreach ( $exclude_ids as $exclude_id ) {
                    if ( $exclude_id == $table_id && ! empty( $table_id ) ) {
                        $skip_table = 'yes';
                        break;
                    }
                }
            }

            // We skip the table if we need to
            if ( 'yes' == $skip_table ) {
                $table_count++;
                continue;
            }

            // Based on the tags used in the table, we determine its type and set a mode varialbe
            if ( mtr_contains_tag( $table, 'thead' ) ) {
                if ( mtr_contains_tag( $table, 'th' ) ) {
                    $mode = 'mtr-thead-th';
                } else {
                    $mode = 'mtr-thead-td';
                }
            } else {
                if ( mtr_contains_tag( $table, 'th' ) ) {
                    $mode = 'mtr-tr-th';
                } else {
                    $mode = 'mtr-tr-td';
                }
            }

            $column_names = Array();

            $dom_table = new DOMDocument();
            $internalErrors = libxml_use_internal_errors( true );
            $dom_table->loadHTML( mb_convert_encoding( $table_outer_html, 'HTML-ENTITIES', 'UTF-8' ) );
            libxml_use_internal_errors( $internalErrors );

            $one_row_table = 'no';

            // We may skip tables with only one row based on the settings
            if ( substr_count( $table_outer_html, '<tr' ) < 2 ) {
                $one_row_table = 'yes';
                if ( ( 'the_content' === $current_filter && 'checked' !== $settings['mtr-single-enable-in-content'] )
                    || ( 'widget_text' === $current_filter && 'checked' !== $settings['mtr-single-enable-in-widgets'] ) ) {
                    $table_count++;
                    continue;
                }
            }

            // If it is not a one row table
            if ( 'no' === $one_row_table ) {

                // We may skip tables with multiple rows based on the settings
                if ( ( 'the_content' === $current_filter && 'checked' !== $settings['mtr-enable-in-content'] )
                    || ( 'widget_text' === $current_filter && 'checked' !== $settings['mtr-enable-in-widgets'] ) ) {
                    $table_count++;
                    continue;
                }

                $mtr_class = 'mtr-table';

                // We get the rows
                $rows = mtr_get_tags( $dom_table, 'tr' );

                // Here we will get the names of the columns from the first row cell, the foreach will run only once. Any html will be stripped from these cells.
                foreach ( $rows as $row ) {
                    $first_row_inner_html = mtr_get_inner_html( $row );
                    $dom_first_row = new DOMDocument();
                    $internalErrors = libxml_use_internal_errors( true );
                    $dom_first_row->loadHTML( mb_convert_encoding( $first_row_inner_html, 'HTML-ENTITIES', 'UTF-8' ) );
                    libxml_use_internal_errors( $internalErrors );

                    $th_column_names_cells = mtr_get_tags( $dom_first_row, 'th' );
                    $td_column_names_cells = mtr_get_tags( $dom_first_row, 'td' );

                    $first_row_td_count = substr_count( $first_row_inner_html, '<td' );
                    $first_row_thead_count = substr_count( $first_row_inner_html, '<thead' );
                    $first_row_th_count = substr_count( $first_row_inner_html, '<th' ) - $first_row_thead_count;

                    // If we found a table with both td and th tags in the first row we will skip it
                    if ( $first_row_td_count > 0 && $first_row_th_count > 0 ) {
                        $skip_table = 'yes';
                        break;
                    }

                    if ( $first_row_th_count > 0 ) {
                        foreach ( $th_column_names_cells as $th_column_names_cell ) {
                            $current_cell_inner_html = mtr_get_inner_html( $th_column_names_cell );
                            $column_names[] = esc_attr( strip_tags( $current_cell_inner_html ) );
                        }
                    } else {
                        foreach ( $td_column_names_cells as $td_column_names_cell ) {
                            $current_cell_inner_html = mtr_get_inner_html( $td_column_names_cell );
                            $column_names[] = esc_attr( strip_tags( $current_cell_inner_html ) );
                        }
                    }

                    break;
                }

                // If we found a table with both td and th tags in the first row we skip it
                if ( 'yes' == $skip_table ) {
                    $table_count++;
                    continue;
                }

                $columns_count = count( $column_names );

                $td_count_cells = substr_count( $table_outer_html, '<td' );
                $thead_count = substr_count( $table_outer_html, '<thead' );
                $th_count_cells = substr_count( $table_outer_html, '<th' ) - $thead_count;

                /*
                 * If the number of cells is less than the columns or if the number of cells is not a multiple of the number of columns,
                 * we will skip the table. This means that some columns use "th" tags and some "td" tags which we do not support.
                 */
                if ( $td_count_cells < $columns_count || ( $td_count_cells % $columns_count ) !== 0 ) {
                    $table_count++;
                    continue;
                }
                if ( ( $th_count_cells > 0 && $th_count_cells < $columns_count ) || ( $th_count_cells % $columns_count ) !== 0 ) {
                    $table_count++;
                    continue;
                }

                /*
                 * In this section we get all the cells with TD tags and then we check if there any merged cells.
                 * If yes, we skip this table. The plugin does not support these tables for now.
                 * We also set a data-mtr-content attribute to store the data for the column names.
                 */
                $td_cells = mtr_get_tags( $dom_table, 'td' );
                $loop_count = 0;
                $mtr_td_class = 'mtr-td-tag';
                foreach ( $td_cells as $td_cell ) {
                    if ( $td_cell->hasAttribute( 'rowspan' ) || $td_cell->hasAttribute( 'colspan' ) ) {
                        $skip_table = 'yes';
                        break;
                    }
                    if ( $loop_count == $columns_count ) {
                        $loop_count = 0;
                    }
                    if ( ! $td_cell->hasAttribute( 'data-mtr-content' ) ) {
                        $td_cell->setAttribute( 'data-mtr-content', $column_names[ $loop_count ] );
                    }

                    // We add a class to each td tag so we can better force our CSS rules upon others
                    if ( $td_cell->hasAttribute( 'class' ) ) {
                        $class_attribute = $td_cell->getAttribute( 'class' );
                        if ( strpos( $class_attribute, $mtr_td_class ) === false ) {
                            $td_cell->setAttribute( 'class', $class_attribute . ' ' . $mtr_td_class );
                        }
                    } else {
                        $td_cell->setAttribute( 'class', $mtr_td_class );
                    }

                    /*
                     * Based on the settings we might limit the width of right side with CSS so longer cell content displays on the same row as the left side.
                     * To do this we surround the cell content with a div.
                     */
                    if ( ! empty( $settings['mtr-limit-right-side'] ) ) {
                        $cell_inner = mtr_get_inner_html( $td_cell );
                        if ( strpos( $cell_inner, 'mtr-cell-content' ) === false ) {
                            mtr_set_inner_html( $td_cell, '<div class="mtr-cell-content">' . $cell_inner . '</div>' );
                        }
                    }

                    $loop_count++;
                }

                // If we found a table with merged cells we skip it
                if ( 'yes' == $skip_table ) {
                    $table_count++;
                    continue;
                }

                /*
                 * In this section we get all the cells with TH tags and then we check if there any merged cells.
                 * If yes, we skip this table. The plugin does not support these tables for now.
                 * We also set a data-mtr-content attribute to store the data for the column names.
                 */
                $th_cells = mtr_get_tags( $dom_table, 'th' );
                $loop_count = 0;
                $mtr_th_class = 'mtr-th-tag';
                foreach ( $th_cells as $th_cell ) {
                    if ( $th_cell->hasAttribute( 'rowspan' ) || $th_cell->hasAttribute( 'colspan' ) ) {
                        $skip_table = 'yes';
                        break;
                    }
                    if ( $loop_count == $columns_count ) {
                        $loop_count = 0;
                    }
                    if ( ! $th_cell->hasAttribute( 'data-mtr-content' ) ) {
                        $th_cell->setAttribute( 'data-mtr-content', $column_names[ $loop_count ] );
                    }

                    // We add a class to each th tag so we can better force our CSS rules upon others
                    if ( $th_cell->hasAttribute( 'class' ) ) {
                        $class_attribute = $th_cell->getAttribute( 'class' );
                        if ( strpos( $class_attribute, $mtr_th_class ) === false ) {
                            $th_cell->setAttribute( 'class', $class_attribute . ' ' . $mtr_th_class );
                        }
                    } else {
                        $th_cell->setAttribute( 'class', $mtr_th_class );
                    }

                    /*
                     * Based on the settings we might limit the width of right side with CSS so longer cell content displays on the same row as the left side.
                     * To do this we surround the cell content with a div.
                     */
                    if ( ! empty( $settings['mtr-limit-right-side'] ) ) {
                        $cell_inner = mtr_get_inner_html( $th_cell );
                        if ( strpos( $cell_inner, 'mtr-cell-content' ) === false ) {
                            mtr_set_inner_html( $th_cell, '<div class="mtr-cell-content">' . $cell_inner . '</div>' );
                        }
                    }

                    $loop_count++;
                }

                // If we found a table with merged cells we skip it
                if ( 'yes' == $skip_table ) {
                    $table_count++;
                    continue;
                }

            // If we go in the "else" it is a one row table and we have not skipped it
            } else {
                $mtr_class = 'mtr-one-row-table';
                $td_count_cells = substr_count( $table_outer_html, '<td' );
                if ( $td_count_cells < $settings['mtr-single-row-columns'] ) {
                    $table_count++;
                    continue;
                }

                // We add a class to each td tag so we can better force our CSS rules upon others
                $td_cells = mtr_get_tags( $dom_table, 'td' );
                $mtr_td_class = 'mtr-td-tag';
                foreach ( $td_cells as $td_cell ) {
                    if ( $td_cell->hasAttribute( 'class' ) ) {
                        $class_attribute = $td_cell->getAttribute( 'class' );
                        if ( strpos( $class_attribute, $mtr_td_class ) === false ) {
                            $td_cell->setAttribute( 'class', $class_attribute . ' ' . $mtr_td_class );
                        }
                    } else {
                        $td_cell->setAttribute( 'class', $mtr_td_class );
                    }
                }

                // We add a class to each th tag so we can better force our CSS rules upon others
                $th_cells = mtr_get_tags( $dom_table, 'th' );
                $mtr_th_class = 'mtr-th-tag';
                foreach ( $th_cells as $th_cell ) {
                    if ( $th_cell->hasAttribute( 'class' ) ) {
                        $class_attribute = $th_cell->getAttribute( 'class' );
                        if ( strpos( $class_attribute, $mtr_th_class ) === false ) {
                            $th_cell->setAttribute( 'class', $class_attribute . ' ' . $mtr_th_class );
                        }
                    } else {
                        $th_cell->setAttribute( 'class', $mtr_th_class );
                    }
                }
            }

            // These are the new tables (changed old tables)
            $new_tables = mtr_get_tags( $dom_table, 'table' );

            // We add our class to the new tables and also get their HTML code
            foreach ( $new_tables as $new_table ) {
                if ( $new_table->hasAttribute( 'class' ) ) {
                    $class_attribute = $new_table->getAttribute( 'class' );
                    if ( strpos( $class_attribute, $mtr_class ) === false ) {
                        $new_table->setAttribute( 'class', $class_attribute . ' ' . $mtr_class . ' ' . $mode );
                    }
                } else {
                    $new_table->setAttribute( 'class', $mtr_class .' ' . $mode );
                }
                $new_table_outer_html = mtr_get_outer_html( $new_table );

                // For some reason body tags appear, I don't why, but I remove them here
                $new_table_outer_html = str_replace( '<body>', '', $new_table_outer_html );
                $new_table_outer_html = str_replace( '</body>', '', $new_table_outer_html );
            }

            // We replace the original table with the new table in the $new_content variable
            $new_content = str_replace( $tables_exploded[ $table_count ], $new_table_outer_html, $new_content );

            $table_count++;
        }

        // We return the changed content
        return $new_content;
    }

    // We return the content without change, since there are no tables in the post/page or the numbers of opening and closing table tags are not the same.
    return $content;
}

// Displays the CSS code for the front end part of the site, taking into account the plugin settings.
function mtr_add_css_on_front_end() {

    $settings = mtr_get_settings_array();

    if ( 'wp_head' === current_filter() ) {
        echo '
    <!-- BEGIN - Make Tables Responsive -->
    <style type="text/css">
        ';
    }

    echo '
    /* Multi-row tables */
    @media (max-width: ' . intval( $settings['mtr-enable-on-screen-size-below'] - 1 ) .  'px) {

        .mtr-table tbody,
        .mtr-table {
        	width: 100% !important;
            display: table !important;
        }

        .mtr-table tr,
        .mtr-table .mtr-th-tag,
        .mtr-table .mtr-td-tag {
        	display: block !important;
            clear: both !important;
            height: auto !important;
        }

        .mtr-table .mtr-td-tag,
        .mtr-table .mtr-th-tag {
        	text-align: right !important;
            width: auto !important;
            box-sizing: border-box !important;
            overflow: auto !important;
        }
    ';

    if ( empty( $settings['mtr-rtl'] ) ) {
        echo '
        .mtr-table .mtr-cell-content {
        	text-align: right !important;
        }
        ';
    } else {
        echo '
        .mtr-table .mtr-cell-content {
        	text-align: left !important;
            float: left !important;
        }
        ';
    }

    if ( empty( $settings['mtr-disable-styling'] ) ) {
        echo '
        .mtr-table tbody,
        .mtr-table tr,
        .mtr-table {
        	border: none !important;
            padding: 0 !important;
        }

        .mtr-table .mtr-td-tag,
        .mtr-table .mtr-th-tag {
        	border: none;
        }

        .mtr-table tr:nth-child(even) .mtr-td-tag,
        .mtr-table tr:nth-child(even) .mtr-th-tag {
            border-bottom: 1px solid ' . sanitize_hex_color( $settings['mtr-even-row-cell-border-color'] ) . ' !important;
            border-left: 1px solid ' . sanitize_hex_color( $settings['mtr-even-row-cell-border-color'] ) . ' !important;
            border-right: 1px solid ' . sanitize_hex_color( $settings['mtr-even-row-cell-border-color'] ) . ' !important;
            border-top: none !important;
        }

        .mtr-table tr:nth-child(odd) .mtr-td-tag,
        .mtr-table tr:nth-child(odd) .mtr-th-tag {
            border-bottom: 1px solid ' . sanitize_hex_color( $settings['mtr-odd-row-cell-border-color'] ) . ' !important;
            border-left: 1px solid ' . sanitize_hex_color( $settings['mtr-odd-row-cell-border-color'] ) . ' !important;
            border-right: 1px solid ' . sanitize_hex_color( $settings['mtr-odd-row-cell-border-color'] ) . ' !important;
            border-top: none !important;
        }

        .mtr-table tr:first-of-type td:first-of-type,
        .mtr-table tr:first-of-type th:first-of-type {
            border-top: 1px solid ' . sanitize_hex_color( $settings['mtr-odd-row-cell-border-color'] ) . ' !important;
        }

        .mtr-table.mtr-thead-td tr:nth-of-type(2) td:first-child,
        .mtr-table.mtr-thead-td tr:nth-of-type(2) th:first-child,
        .mtr-table.mtr-tr-th tr:nth-of-type(2) td:first-child,
        .mtr-table.mtr-tr-th tr:nth-of-type(2) th:first-child,
        .mtr-table.mtr-tr-td tr:nth-of-type(2) td:first-child,
        .mtr-table.mtr-tr-td tr:nth-of-type(2) th:first-child {
            border-top: 1px solid ' . sanitize_hex_color( $settings['mtr-even-row-cell-border-color'] ) . ' !important;
        }

        .mtr-table tr:nth-child(even),
        .mtr-table tr:nth-child(even) .mtr-td-tag,
        .mtr-table tr:nth-child(even) .mtr-th-tag {
            background: ' . sanitize_hex_color( $settings['mtr-even-row-background-color'] ) . ' !important;
        }

        .mtr-table tr:nth-child(odd),
        .mtr-table tr:nth-child(odd) .mtr-td-tag,
        .mtr-table tr:nth-child(odd) .mtr-th-tag {
            background: ' . sanitize_hex_color( $settings['mtr-odd-row-background-color'] ) . ' !important;
        }

        .mtr-table .mtr-td-tag,
        .mtr-table .mtr-td-tag:first-child,
        .mtr-table .mtr-th-tag,
        .mtr-table .mtr-th-tag:first-child {
            padding: 5px 10px !important;
        }
        ';
    }

    if ( empty( $settings['mtr-rtl'] ) ) {
        echo '
        .mtr-table td[data-mtr-content]:before,
        .mtr-table th[data-mtr-content]:before {
        	display: inline-block !important;
        	content: attr(data-mtr-content) !important;
        	float: left !important;
            text-align: left !important;
            white-space: pre-line !important;
        }
        ';
    } else {
        echo '
        .mtr-table td[data-mtr-content]:before,
        .mtr-table th[data-mtr-content]:before {
        	display: inline-block !important;
        	content: attr(data-mtr-content) !important;
        	float: right !important;
            text-align: right !important;
            white-space: pre-line !important;
        }
        ';
    }

    echo '
        .mtr-table thead,
        .mtr-table.mtr-tr-th tr:first-of-type,
        .mtr-table.mtr-tr-td tr:first-of-type,
        .mtr-table colgroup {
        	display: none !important;
        }
    ';

    if ( ! empty( $settings['mtr-hide-tfoot'] ) ) {
        echo '
        .mtr-table tfoot {
        	display: none !important;
        }
        ';
    }

    if ( ! empty( $settings['mtr-limit-right-side'] ) ) {
        if ( empty( $settings['mtr-rtl'] ) ) {
            echo '
        .mtr-cell-content {
            max-width: ' . intval( $settings['mtr-limit-right-side'] ) .  '% !important;
            display: inline-block !important;
        }
            ';
        } else {
            echo '
        .mtr-table td[data-mtr-content]:before,
        .mtr-table th[data-mtr-content]:before {
            max-width: ' . intval( $settings['mtr-limit-right-side'] ) .  '% !important;
        }
            ';
        }
    }

    if ( ! empty( $settings['mtr-limit-left-side'] ) ) {
        if ( empty( $settings['mtr-rtl'] ) ) {
            echo '
        .mtr-table td[data-mtr-content]:before,
        .mtr-table th[data-mtr-content]:before {
            max-width: ' . intval( $settings['mtr-limit-left-side'] ) .  '% !important;
        }
            ';
        } else {
            echo '
        .mtr-cell-content {
            max-width: ' . intval( $settings['mtr-limit-left-side'] ) .  '% !important;
            display: inline-block !important;
        }
            ';
        }
    }

    echo '
    }
    ';

    echo '
    /* Single-row tables */
    @media (max-width: ' . intval( $settings['mtr-single-enable-on-screen-size-below'] - 1 ) .  'px) {

        .mtr-one-row-table .mtr-td-tag,
        .mtr-one-row-table .mtr-th-tag {
            box-sizing: border-box !important;
        }

        .mtr-one-row-table colgroup {
        	display: none !important;
        }

    ';

    if ( empty( $settings['mtr-single-disable-styling'] )
        && in_array( $settings['mtr-single-row-layout'], array( '1-column', '2-columns', '3-columns', '4-columns' ), true ) ) {
        echo '
        .mtr-one-row-table tbody,
        .mtr-one-row-table tr,
        .mtr-one-row-table {
        	border: none !important;
            padding: 0 !important;
            width: 100% !important;
            display: block;
        }

        .mtr-one-row-table .mtr-td-tag,
        .mtr-one-row-table .mtr-th-tag {
            border: none;
        }
        ';
    }

    if ( in_array( $settings['mtr-single-row-cell-align'], array( 'left', 'right', 'center' ), true ) ) {
        echo '
        .mtr-one-row-table .mtr-td-tag,
        .mtr-one-row-table .mtr-th-tag {
        	text-align: ' . sanitize_html_class( $settings['mtr-single-row-cell-align'] ) . ' !important;
        }
        ';
    }

    if ( 'fluid-row' === $settings['mtr-single-row-layout'] ) {
        echo '
        .mtr-one-row-table .mtr-td-tag,
        .mtr-one-row-table .mtr-th-tag {
        	display: inline-block !important;
        }
        ';
    } elseif ( '1-column' === $settings['mtr-single-row-layout'] ) {
        echo '
        .mtr-one-row-table .mtr-td-tag,
        .mtr-one-row-table .mtr-th-tag {
        	display: block !important;
            width: 100% !important;
        }
        ';
        if ( empty( $settings['mtr-single-disable-styling'] ) ) {
            echo '
            .mtr-one-row-table td:nth-child(odd),
            .mtr-one-row-table th:nth-child(odd) {
                background: ' . sanitize_hex_color( $settings['mtr-single-odd-row-background-color'] ) . ' !important;
            }

            .mtr-one-row-table td:nth-child(even),
            .mtr-one-row-table th:nth-child(even) {
                background: ' . sanitize_hex_color( $settings['mtr-single-even-row-background-color'] ) . ' !important;
            }

            .mtr-one-row-table th:last-child,
            .mtr-one-row-table td:last-child {
                border-bottom: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }

            .mtr-one-row-table .mtr-td-tag,
            .mtr-one-row-table .mtr-th-tag {
                border-left: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
                border-right: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }
            ';
        }
    } elseif ( '2-columns' === $settings['mtr-single-row-layout'] ) {
        echo '
        .mtr-one-row-table .mtr-td-tag,
        .mtr-one-row-table .mtr-th-tag {
        	display: block !important;
            width: 50% !important;
        }
        .mtr-one-row-table tr {
            display: flex !important;
            flex-wrap: wrap !important;
        }
        ';
        if ( empty( $settings['mtr-single-disable-styling'] ) ) {
            echo '
            .mtr-one-row-table td:nth-child(4n+1),
            .mtr-one-row-table th:nth-child(4n+1),
            .mtr-one-row-table td:nth-child(4n+2),
            .mtr-one-row-table th:nth-child(4n+2) {
                background: ' . sanitize_hex_color( $settings['mtr-single-odd-row-background-color'] ) . ' !important;
            }

            .mtr-one-row-table th:nth-child(2n+1),
            .mtr-one-row-table td:nth-child(2n+1) {
                border-right: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
                border-left: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }

            .mtr-one-row-table td:nth-child(2n+2),
            .mtr-one-row-table th:nth-child(2n+2) {
                border-right: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }

            /* last two */
            .mtr-one-row-table td:nth-last-child(-n+2),
            .mtr-one-row-table th:nth-last-child(-n+2) {
                border-bottom: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }
            ';
        }
    } elseif ( '3-columns' === $settings['mtr-single-row-layout'] ) {
        echo '
        .mtr-one-row-table .mtr-td-tag,
        .mtr-one-row-table .mtr-th-tag {
        	display: block !important;
            width: 33.33% !important;
        }
        .mtr-one-row-table tr {
            display: flex !important;
            flex-wrap: wrap !important;
        }
        ';
        if ( empty( $settings['mtr-single-disable-styling'] ) ) {
            echo '
            .mtr-one-row-table td:nth-child(6n+1),
            .mtr-one-row-table th:nth-child(6n+1),
            .mtr-one-row-table td:nth-child(6n+2),
            .mtr-one-row-table th:nth-child(6n+2),
            .mtr-one-row-table td:nth-child(6n+3),
            .mtr-one-row-table th:nth-child(6n+3) {
                background: ' . sanitize_hex_color( $settings['mtr-single-odd-row-background-color'] ) . ' !important;
            }

            .mtr-one-row-table td:nth-child(3n+1),
            .mtr-one-row-table th:nth-child(3n+1),
            .mtr-one-row-table td:nth-child(3n+2),
            .mtr-one-row-table th:nth-child(3n+2) {
                border-right: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }

            .mtr-one-row-table td:nth-child(3n+3),
            .mtr-one-row-table th:nth-child(3n+3) {
                border-right: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }

            .mtr-one-row-table th:nth-child(3n+1),
            .mtr-one-row-table td:nth-child(3n+1) {
                border-left: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }

            /* last three */
            .mtr-one-row-table td:nth-last-child(-n+3),
            .mtr-one-row-table th:nth-last-child(-n+3) {
                border-bottom: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }
            ';
        }
    } elseif ( '4-columns' === $settings['mtr-single-row-layout'] ) {
        echo '
        .mtr-one-row-table .mtr-td-tag,
        .mtr-one-row-table .mtr-th-tag {
        	display: block !important;
            width: 25% !important;
        }
        .mtr-one-row-table tr {
            display: flex !important;
            flex-wrap: wrap !important;
        }
        ';
        if ( empty( $settings['mtr-single-disable-styling'] ) ) {
            echo '
            .mtr-one-row-table td:nth-child(8n+1),
            .mtr-one-row-table th:nth-child(8n+1),
            .mtr-one-row-table td:nth-child(8n+2),
            .mtr-one-row-table th:nth-child(8n+2),
            .mtr-one-row-table td:nth-child(8n+3),
            .mtr-one-row-table th:nth-child(8n+3),
            .mtr-one-row-table td:nth-child(8n+4),
            .mtr-one-row-table th:nth-child(8n+4) {
                background: ' . sanitize_hex_color( $settings['mtr-single-odd-row-background-color'] ) . ' !important;
            }

            .mtr-one-row-table td:nth-child(4n+1),
            .mtr-one-row-table th:nth-child(4n+1),
            .mtr-one-row-table td:nth-child(4n+2),
            .mtr-one-row-table th:nth-child(4n+2),
            .mtr-one-row-table td:nth-child(4n+3),
            .mtr-one-row-table th:nth-child(4n+3) {
                border-right: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }

            .mtr-one-row-table td:nth-child(4n+4),
            .mtr-one-row-table th:nth-child(4n+4) {
                border-right: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }

            .mtr-one-row-table td:nth-child(4n+1),
            .mtr-one-row-table th:nth-child(4n+1) {
                border-left: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }

            /* last four */
            .mtr-one-row-table td:nth-last-child(-n+4),
            .mtr-one-row-table th:nth-last-child(-n+4) {
                border-bottom: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            }
            ';
        }
    }

    if ( empty( $settings['mtr-single-disable-styling'] )
        && in_array( $settings['mtr-single-row-layout'], array( '1-column', '2-columns', '3-columns', '4-columns' ), true ) ) {
        echo '
        .mtr-one-row-table .mtr-td-tag,
        .mtr-one-row-table .mtr-th-tag {
            border-top: 1px solid ' . sanitize_hex_color( $settings['mtr-single-row-cell-border-color'] ) . ' !important;
            padding: 5px 10px !important;
        }
        ';
    }

    echo '
    }';

    if ( 'wp_head' === current_filter() ) {
        echo '
    </style>
    <!-- END - Make Tables Responsive -->
        ';
    }         
}

/**
 * Adds a link to the settings page in the plugin action links.
 * @param array $actions
 * @param string $plugin_file
 * @return array
 */
function mtr_add_settings_plugin_action_link( $actions, $plugin_file ) {

    if ( 'make-tables-responsive/make-tables-responsive.php' === $plugin_file ) {

        $settings_action = '<a href="' . esc_url( admin_url( 'options-general.php?page=make-tables-responsive' ) ) . '">'
            . esc_html__( 'Settings', 'make-tables-responsive' ) . '</a>';

        // We add the action link to the array of links
        $actions = mtr_add_element_to_array( $actions, 'mtr-admin-settings', $settings_action, 'deactivate' );

    }

    // We return the modified array of action links
    return $actions;
}

/**
 * Returns an array of the default plugin settings.
 * @return Array
 */
function mtr_get_default_settings_array() {
    return Array (

        // Multi-row settings
        'mtr-enable-on-screen-size-below' => 651,
        'mtr-enable-in-content' => 'checked',
        'mtr-enable-in-widgets' => '',
        'mtr-hide-tfoot' => '',
        'mtr-limit-left-side' => 49,
        'mtr-limit-right-side' => 49,
        'mtr-rtl' => '',
        'mtr-disable-styling' => '',
        'mtr-even-row-background-color' => '#ffffff',
        'mtr-even-row-cell-border-color' => '#dddddd',
        'mtr-odd-row-background-color' => '#dddddd',
        'mtr-odd-row-cell-border-color' => '#bbbbbb',

        // Single-row settings
        'mtr-single-enable-on-screen-size-below' => 651,
        'mtr-single-row-columns' => 4,
        'mtr-single-enable-in-content' => 'checked',
        'mtr-single-enable-in-widgets' => '',
        'mtr-single-row-layout' => '2-columns',
        'mtr-single-row-cell-align' => 'no-change',
        'mtr-single-disable-styling' => '',
        'mtr-single-even-row-background-color' => '#ffffff',
        'mtr-single-odd-row-background-color' => '#dddddd',
        'mtr-single-row-cell-border-color' => '#bbbbbb',

        // Global Settings
        'mtr-exclude-html-classes' => '',
        'mtr-exclude-html-ids' => '',
        'mtr-exclude-post-page-ids' => '',
        'mtr-enable-only-post-page-ids' => '',
    );
}

/**
 * Returns an array of all the current plugin settings.
 * @return Array
 */
function mtr_get_settings_array() {
    $default_settings = mtr_get_default_settings_array();
    $settings = Array();
    foreach ( $default_settings as $name => $default_value ) {
        $settings[ $name ] = get_option( $name, $default_value );
    }
    return $settings;
}

/**
 * Checks if the provided variable contains only comma separated numbers.
 * @param string $string
 * @return int
 */
function mtr_is_comma_separated_numbers( $string ) {
    return preg_match( '/^\d+(?:,\d+)*$/', $string );
}

/**
 * Checks if the provided variable is a valid HEX color value.
 * @param string $string
 * @return int
 */
function mtr_is_hex_color( $string ) {
    return preg_match( '/^#[a-f0-9]{6}$/i', $string );
}

/**
 * Removes all spaces, tabs, new lines from a string
 * @param string $string
 * @return string
 */
function mtr_strip_whitespace( $string ) {
    return preg_replace( '/\s+/', '', $string );
}

/**
 * Sets the inner HTML of a DOMNode object to a given content.
 * @param DOMNode object $element
 * @param string $content
 */
function mtr_set_inner_html( DOMNode $element, $content ) {
    $DOM_inner_HTML = new DOMDocument();
    $internal_errors = libxml_use_internal_errors( true );
    $DOM_inner_HTML->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );
    libxml_use_internal_errors( $internal_errors );
    $content_node = $DOM_inner_HTML->getElementsByTagName('body')->item(0);
    $content_node = $element->ownerDocument->importNode( $content_node, true );
    while ( $element->hasChildNodes() ) {
        $element->removeChild( $element->firstChild );
    }
    $element->appendChild( $content_node );
}

/**
 * Returns the inner HTML of a DOMNode object.
 * @param DOMNode object $element
 * @return string
 */
function mtr_get_inner_html( DOMNode $element ) {
    $inner_HTML = "";
    $children  = $element->childNodes;
    foreach ( $children as $child ) {
        $inner_HTML .= $element->ownerDocument->saveHTML( $child );
    }
    return $inner_HTML;
}

/**
 * Returns the outer HTML of a DOMNode object.
 * @param DOMNode object $element
 * @return string
 */
function mtr_get_outer_html( DOMNode $element ) {
    return $element->ownerDocument->saveHTML( $element );
}

/**
 * Checks if the DOMNode object contains a given tag and returns true if yes.
 * @param DOMNode object $element
 * @param string $tag
 * @return bool
 */
function mtr_contains_tag( DOMNode $element, $tag ) {
    $tags = $element->getElementsByTagName( $tag );
    if ( $tags->length > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Returns a DOMNodeList object containing all the elements that match the given tag.
 * @param DOMNode object $element
 * @param string $tag
 * @return DOMNodeList object
 */
function mtr_get_tags( DOMNode $element, $tag ) {
    return $element->getElementsByTagName( $tag );
}

/**
 * Returns an url of an image in the images folder based on a given file name.
 * @param string $filename
 * @return string
 */
function mtr_plugin_image_url( $filename ) {
    return plugin_dir_url( __FILE__ ) . 'images/' . $filename;
}

/**
 * Sanitizes a comma-separated list of HTML classes od IDs, but is more strict than official standarts. It only allows letters, numbers, spaces and -_,
 * @param string $string
 * @return string
 */
function mtr_sanitize_html_class_or_id_list( $string ) {
    return preg_replace( '/[^A-Za-z0-9_\-, ]/', '', $string );
}

/**
 * Sanitizes a comma-separated list of numbers.
 * @param string $string
 * @return string
 */
function mtr_sanitize_list_of_numbers( $string ) {
    return preg_replace( '/[^0-9, ]/', '', $string );
}

/**
 * Sanitizes a value that is allowed to be either a number or empty.
 * @param string $string
 * @return mixed
 */
function mtr_sanitize_number_or_empty( $string ) {
    if ( '' === $string ) {
        return $string;
    } else {
        return intval( $string );
    }
}

/**
 * Sanitizes a value that is allowed to be either a the string 'checked' or empty string.
 * @param string $string
 * @return string
 */
function mtr_sanitize_checkbox_checked( $string ) {
    if ( 'checked' === $string ) {
        return $string;
    } else {
        return '';
    }
}

/**
 * Display an html select form element to use in a plugin settings page.
 * @param string $name
 * @param array $option_values
 * @param mixed $option_names
 * @param string $current_value
 */
function mtr_setting_select( $name, $option_values, $option_names, $current_value ) {

    // Based on the $option_names argument we could use the option values as option names
    if ( ! is_array( $option_names ) && 'same-as-values' == $option_names ) {
        $option_names = $option_values;
    }

    // Output the select tag
    echo '<select id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" size="1">';

    // Go through all values
    for ( $i = 0; $i < count( $option_values ); $i++ ) {

        // When we see the current value in the database, we will output the selected attribute. Otherwise we just output the option tag with the value and name.
        if ( $current_value === $option_values[ $i ] ) {
            $selected = 'selected="selected"';
        } else {
            $selected = '';
        }
        echo '<option value="' . esc_attr( $option_values[ $i ] ) . '" ' . $selected . ' >' . esc_html( $option_names[ $i ] ) . '</option>';

    }
    echo '</select>';
}

/**
 * Adds a new element in an array on the exact place we want (if possible).
 * We use this when adding a custom column or an action link on some places in the admin panel.
 * @param array $original_array
 * @param string $add_element_key
 * @param mixed $add_element_value
 * @param string $add_before_key
 * @return array
 */
function mtr_add_element_to_array( $original_array, $add_element_key, $add_element_value, $add_before_key ) {

    // This variable shows if we were able to add the element where we wanted
    $is_added = 0;

    // This will be the new array, it will include our element placed where we want
    $new_array = array();

    // We go through all the current elements and we add our new element on the place we want
    foreach( $original_array as $key => $value ) {

        // We put the element before the key we want
        if ( $key == $add_before_key ) {
      	    $new_array[ $add_element_key ] = $add_element_value;

            // We were able to add the element where we wanted so no need to add it again later
            $is_added = 1;
        }

        // All the normal elements remain and are added to the new array we made
        $new_array[ $key ] = $value;
    }

    // If we failed to add the element earlier (because the key we tried to add it in front of is gone) we add it now to the end
    if ( 0 == $is_added ) {
        $new_array[ $add_element_key ] = $add_element_value;
    }

    // We return the new array we made
    return $new_array;
}
