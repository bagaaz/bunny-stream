<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registra as configurações do plugin.
 */
function bunny_stream_register_settings() {
    register_setting( 'bunny_stream_settings', 'bunny_stream_settings' );

    add_settings_section(
        'bunny_stream_section',
        'Configurações do Bunny Stream',
        null,
        'bunny_stream'
    );

    add_settings_field(
        'bunny_stream_api_key',
        'API Key',
        'bunny_stream_api_key_callback',
        'bunny_stream',
        'bunny_stream_section'
    );
}
add_action( 'admin_init', 'bunny_stream_register_settings' );

/**
 * Exibe o campo de API Key no painel.
 */
function bunny_stream_api_key_callback() {
    $options = get_option( 'bunny_stream_settings' );
    ?>
    <input type="text" name="bunny_stream_settings[api_key]" value="<?php echo esc_attr( $options['api_key'] ?? '' ); ?>" class="regular-text" />
    <?php
}

/**
 * Adiciona a página do plugin no menu do WordPress.
 */
function bunny_stream_menu() {
    add_menu_page(
        'Configuração do Bunny Stream',
        'Bunny Stream',
        'manage_options',
        'bunny-stream',
        'bunny_stream_page',
        'dashicons-video-alt3',
        90
    );
}
add_action( 'admin_menu', 'bunny_stream_menu' );

/**
 * Enfileira scripts e estilos para a área administrativa do plugin.
 */
function bunny_stream_enqueue_admin_scripts( $hook ) {
    // Verifica se estamos na página do plugin
    if ( 'toplevel_page_bunny-stream' !== $hook ) {
        return;
    }

    // Enfileira o script admin.js
    wp_enqueue_script(
        'bunny-stream-admin-js',
        plugin_dir_url( __FILE__ ) . '../assets/js/admin.js',
        array(),
        '1.0.0',
        true
    );
}
add_action( 'admin_enqueue_scripts', 'bunny_stream_enqueue_admin_scripts' );

/**
 * Página de configuração do plugin.
 */
function bunny_stream_page() {
    ?>
    <div class="wrap">
        <h1>Configuração do Bunny Stream</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'bunny_stream_settings' );
            do_settings_sections( 'bunny_stream' );
            submit_button();
            ?>
        </form>

        <h2>Bibliotecas Sincronizadas</h2>
        <div id="bunny-libraries" style="margin-top: 20px;"></div>
        <button id="sync-libraries" class="button button-primary" style="margin-top:20px;">Sincronizar Bibliotecas</button>

        <h2>Vídeos da Biblioteca</h2>
        <div id="bunny-videos" style="margin-top: 20px;"></div>
    </div>
    <?php
}
