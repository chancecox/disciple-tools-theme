<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Hook_Theme extends Disciple_Tools_Hook_Base {

    public function hooks_theme_modify( $location, $status ) {
        global $file;
        if ( false !== strpos( $location, 'theme-editor.php?file=' ) ) {
            if ( isset( $_POST['_wpnonce'] ) && isset( $_POST['action'] ) && isset( $_POST['theme'] ) ) {
                // We're doing nonce verification later, and it's OK if the
                // action name is built on POST parameters.
                if ( isset( $_POST['theme'] ) ) {
                    // WordPress.CSRF.NonceVerification.NoNonceVerification
                    // @phpcs:ignore
                    $stylesheet = sanitize_text_field( wp_unslash( $_POST['theme'] ) );
                } else {
                    $stylesheet = get_stylesheet();
                }
                if ( ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'edit-theme_' . $file . $stylesheet ) ) {
                    throw new Exception( "Could not verify nonce" );
                }
                if ( 'update' === $_POST['action'] ) {
                    $aal_args = [
                        'action'         => 'file_updated',
                        'object_type'    => 'Theme',
                        'object_subtype' => 'theme_unknown',
                        'object_id'      => 0,
                        'object_name'    => 'file_unknown',
                    ];

                    if ( ! empty( $_POST['file'] ) ) {
                        $aal_args['object_name'] = sanitize_text_field( wp_unslash( $_POST['file'] ) );
                    }

                    if ( ! empty( $_POST['theme'] ) ) {
                        $aal_args['object_subtype'] = sanitize_text_field( wp_unslash( $_POST['theme'] ) );
                    }

                    dt_activity_insert( $aal_args );
                }
            }
        }

        // We are need return the instance, for complete the filter.
        return $location;
    }

    public function hooks_switch_theme( $new_name, WP_Theme $new_theme ) {
        dt_activity_insert(
            [
                'action'         => 'activated',
                'object_type'    => 'Theme',
                'object_subtype' => $new_theme->get_stylesheet(),
                'object_id'      => 0,
                'object_name'    => $new_name,
            ]
        );
    }

    public function hooks_theme_customizer_modified( WP_Customize_Manager $obj ) {
        $aal_args = [
            'action'         => 'updated',
            'object_type'    => 'Theme',
            'object_subtype' => $obj->theme()->display( 'Name' ),
            'object_id'      => 0,
            'object_name'    => 'Theme Customizer',
        ];

        if ( 'customize_preview_init' === current_filter() ) {
            $aal_args['action'] = 'accessed';
        }

        dt_activity_insert( $aal_args );
    }

    public function hooks_theme_deleted() {
        $backtrace_history = debug_backtrace();

        $delete_theme_call = null;
        foreach ( $backtrace_history as $call ) {
            if ( isset( $call['function'] ) && 'delete_theme' === $call['function'] ) {
                $delete_theme_call = $call;
                break;
            }
        }

        if ( empty( $delete_theme_call ) ) {
            return;
        }

        $name = $delete_theme_call['args'][0];

        dt_activity_insert(
            [
                'action' => 'deleted',
                'object_type' => 'Theme',
                'object_name' => $name,
            ]
        );
    }

    /**
     * @param Theme_Upgrader $upgrader
     * @param array $extra
     */
    public function hooks_theme_install_or_update( $upgrader, $extra ) {
        if ( ! isset( $extra['type'] ) || 'theme' !== $extra['type'] ) {
            return;
        }

        if ( 'install' === $extra['action'] ) {
            $slug = $upgrader->theme_info();
            if ( ! $slug ) {
                return;
            }

            wp_clean_themes_cache();
            $theme   = wp_get_theme( $slug );
            $name    = $theme->name;
            $version = $theme->version;

            dt_activity_insert(
                [
                    'action' => 'installed',
                    'object_type' => 'Theme',
                    'object_name' => $name,
                    'object_subtype' => $version,
                ]
            );
        }

        if ( 'update' === $extra['action'] ) {
            if ( isset( $extra['bulk'] ) && true == $extra['bulk'] ) {
                $slugs = $extra['themes'];
            } else {
                $slugs = [ $upgrader->skin->theme ];
            }

            foreach ( $slugs as $slug ) {
                $theme      = wp_get_theme( $slug );
                $stylesheet = $theme['Stylesheet Dir'] . '/style.css';
                $theme_data = get_file_data( $stylesheet, [ 'Version' => 'Version' ] );

                $name    = $theme['Name'];
                $version = $theme_data['Version'];

                dt_activity_insert(
                    [
                        'action' => 'updated',
                        'object_type' => 'Theme',
                        'object_name' => $name,
                        'object_subtype' => $version,
                    ]
                );
            }
        }
    }

    public function __construct() {
        add_filter( 'wp_redirect', [ &$this, 'hooks_theme_modify' ], 10, 2 );
        add_action( 'switch_theme', [ &$this, 'hooks_switch_theme' ], 10, 2 );
        add_action( 'delete_site_transient_update_themes', [ &$this, 'hooks_theme_deleted' ] );
        add_action( 'upgrader_process_complete', [ &$this, 'hooks_theme_install_or_update' ], 10, 2 );

        // Theme customizer
        add_action( 'customize_save', [ &$this, 'hooks_theme_customizer_modified' ] );
        //add_action( 'customize_preview_init', array( &$this, 'hooks_theme_customizer_modified' ) );

        parent::__construct();
    }

}
