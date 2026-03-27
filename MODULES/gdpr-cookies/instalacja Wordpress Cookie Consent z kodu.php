<?php
/**
 * Wczytaj pliki CSS i JS z katalogu /custom/ w katalogu wygenerowanej wtyczki do Cookie Policy - GDPR
 */
function enqueue_custom_assets_from_custom_folder() {
    $plugins_url = plugins_url(); . '/custom';

    // CSS
    wp_enqueue_style(
        'cookieconsent',
        $plugins_url . '/cookieconsent.min.css',
        array(),
        null
    );

    wp_enqueue_style(
        'iframemanager-css',
        $plugins_url . '/iframemanager.min.css',
        array(),
        null
    );

    // JS

    wp_enqueue_script(
        'iframemanager-js',
        $plugins_url . '/iframemanager.js',
        array(),
        null,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_assets_from_custom_folder' );

/**
 * Wczytaj cookieconsent-config.js jako moduł (type="module")
 */
function enqueue_cookieconsent_module_script() {
    $plugins_url = plugins_url(); . '/custom';

    wp_enqueue_script(
        'cookieconsent-config',
        $plugins_url . '/cookieconsent-config.js',
        array(),
        null,
        true // wczytaj w footerze
    );
}
add_action( 'wp_enqueue_scripts', 'enqueue_cookieconsent_module_script' );

/**
 * Dodaj type="module" do cookieconsent-config.js
 */
function add_type_module_to_cookieconsent( $tag, $handle, $src ) {
    if ( 'cookieconsent-config' === $handle ) {
        return '<script type="module" src="' . esc_url( $src ) . '"></script>';
    }
    return $tag;
}
add_filter( 'script_loader_tag', 'add_type_module_to_cookieconsent', 10, 3 );
