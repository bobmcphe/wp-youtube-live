<?php

if (!defined('ABSPATH')) {
    exit;
}

include( 'EmbedYoutubeLiveStreaming.php' );

/**
 * Enqueue backend assets
 */
function youtube_live_backend_assets() {
    wp_register_script( 'wp-youtube-live-backend', plugin_dir_url( __FILE__ ) . '../js/wp-youtube-live-backend.min.js', array( 'jquery' ), NULL, true );
}
add_action( 'admin_enqueue_scripts', 'youtube_live_backend_assets' );

/**
 * Add settings page
 */
add_action( 'admin_menu', 'youtube_live_add_admin_menu' );
add_action( 'admin_init', 'youtube_live_settings_init' );

/**
 * Add settings page to admin menu
 */
function youtube_live_add_admin_menu() {
    add_submenu_page( 'options-general.php', 'YouTube Live', 'YouTube Live Settings', 'manage_options', 'youtube-live', 'youtube_live_options_page' );
}

/**
 * Add settings section and fields
 */
function youtube_live_settings_init() {
    register_setting( 'youtube_live_options', 'youtube_live_settings' );

    // API settings
    add_settings_section(
        'youtube_live_options_keys_section',
        __( 'YouTube Details', 'youtube_live' ),
        'youtube_live_api_settings_section_callback',
        'youtube_live_options'
    );

    add_settings_field(
        'youtube_live_api_key',
        __( 'YouTube API Key', 'youtube_live' ),
        'youtube_live_api_key_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'youtube_live_channel_id',
        __( 'YouTube Channel ID', 'youtube_live' ),
        'youtube_live_channel_id_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'youtube_subdomain',
        __( 'YouTube Subdomain', 'youtube_live' ),
        'youtube_live_subdomain_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'youtube_live_player_settings',
        __( 'Default Player Settings', 'youtube_live' ),
        'youtube_live_player_settings_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'fallback_behavior',
        __( 'Fallback Behavior', 'youtube_live' ),
        'fallback_behavior_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'auto_refresh',
        __( 'Auto-Refresh', 'youtube_live' ),
        'youtube_live_auto_refresh_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'youtube_live_debugging',
        __( 'Debugging', 'youtube_live' ),
        'youtube_live_debugging_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );
}

/**
 * Print API Key field
 */
function youtube_live_api_key_render() {
    wp_enqueue_script( 'wp-youtube-live-backend' );

    $options = get_option( 'youtube_live_settings' ); ?>
    <input type="text" name="youtube_live_settings[youtube_live_api_key]" placeholder="AIzaSyD4iE2xVSpkLLOXoyqT-RuPwURN3ddScAI" size="45" value="<?php echo $options['youtube_live_api_key']; ?>">

    <p>Don&rsquo;t have an API key?</p>
    <ol>
        <li>Go to the <a href="https://console.developers.google.com/apis/" target="_blank">Google APIs developers console</a> (create an account if necessary).</li>
        <li>Create a new project (if necessary).</li>
        <li>Enable the YouTube Data API v3.</li>
        <li>Go to Credentials, click the blue button, and choose &ldquo;API key&rdquo;.</li>
        <li>Enter referrers if you wish to limit use to your website(s) (highly recommended).</li>
        <li>Enter your API key above.</li>
    </ol>
    <p>See <a href="https://developers.google.com/youtube/registering_an_application" target="_blank">this page</a> for more information.</p>

    <?php
}

/**
 * Print Channel ID field
 */
function youtube_live_channel_id_render() {
    $options = get_option( 'youtube_live_settings' ); ?>
    <input type="text" name="youtube_live_settings[youtube_live_channel_id]" placeholder="UcZliPwLMjeJbhOAnr1Md4gA" size="45" value="<?php echo $options['youtube_live_channel_id']; ?>">

    <p>Go to <a href="https://youtube.com/account_advanced/" target="_blank">YouTube Advanced Settings</a> to find your YouTube Channel ID.</p>
    <?php
}

/**
 * Print subdomain field
 */
function youtube_live_subdomain_render() {
    $options = get_option( 'youtube_live_settings' ); ?>
    <label><select name="youtube_live_settings[subdomain]">
        <option value="www" <?php selected( $options['subdomain'], 'www' ); ?>>Default (www.youtube.com)</option>
        <option value="gaming" <?php selected( $options['subdomain'], 'gaming' ); ?>>Gaming (gaming.youtube.com)</option>
    </select></label>
    <?php
}

/**
 * Print player settings fields
 */
function youtube_live_player_settings_render() {
    $options = get_option( 'youtube_live_settings' );
    if ( ! array_key_exists( 'default_width', $options ) || is_null( $options['default_width'] ) ) {
        $options['default_width'] = 720;
    }
    if ( ! array_key_exists( 'default_height', $options ) || is_null( $options['default_height'] ) ) {
        $options['default_height'] = 480;
    }
    if ( ! array_key_exists( 'autoplay', $options ) ) {
        $options['autoplay'] = true;
    }
    if ( ! array_key_exists( 'show_related', $options ) ) {
        $options['show_related'] = false;
    }
    ?>
    <p>
        <label>Width: <input type="number" name="youtube_live_settings[default_width]" placeholder="720" value="<?php echo $options['default_width']; ?>">px</label><br/>
        <label>Height: <input type="number" name="youtube_live_settings[default_height]" placeholder="480" value="<?php echo $options['default_height']; ?>">px</label>
    </p>
    <p>
        Should the player auto-play when a live video is available? <label><input type="radio" name="youtube_live_settings[autoplay]" value="true" <?php checked( $options['autoplay'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[autoplay]" value="false" <?php checked( $options['autoplay'], 'false' ); ?>> No</label>
    </p>
    <p>
        Should the player show related videos when a video finishes? <label><input type="radio" name="youtube_live_settings[show_related]" value="true" <?php checked( $options['show_related'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[show_related]" value="false" <?php checked( $options['show_related'], 'false' ); ?>> No</label>
    </p>
    <?php
}

/**
 * Print fallback behavior fields
 */
function fallback_behavior_render() {
    $options = get_option( 'youtube_live_settings' );
    if ( ! array_key_exists( 'fallback_behavior', $options ) ) {
        $options['fallback_behavior'] = 'message';
    }
    if ( ! array_key_exists( 'fallback_message', $options ) ) {
        $options['fallback_message'] = '<p>Sorry, there&rsquo;s no live stream at the moment. Please check back later or take a look at <a target="_blank" href="https://youtube.com/channel/' . $youtube_options['youtube_live_channel_id'] . '">all our videos</a>.</p>
<p><button type="button" class="button" id="check-again">Check again</button><span class="spinner" style="display:none;"></span></p>';
    }
    ?>
    <p>
        <label for="youtube_live_settings[fallback_behavior]">If no live videos are available, what should be displayed?</label>
        <select name="youtube_live_settings[fallback_behavior]">
            <option value="message" <?php selected( $options['fallback_behavior'], 'message' ); ?>>Show a custom HTML message (no additional quota cost)</option>
            <option value="upcoming" <?php selected( $options['fallback_behavior'], 'upcoming' ); ?>>Show scheduled live videos (adds a quota unit cost of 100)</option>
            <option value="completed" <?php selected( $options['fallback_behavior'], 'completed' ); ?>>Show last completed live video (adds a quota unit cost of 100)</option>
            <option value="channel" <?php selected( $options['fallback_behavior'], 'channel' ); ?>>Show recent videos from your channel (adds a quota unit cost of at least 3)</option>
            <option value="playlist" <?php selected( $options['fallback_behavior'], 'playlist' ); ?>>Show a specified playlist (adds a quota unit cost of at least 3)</option>
            <option value="video" <?php selected( $options['fallback_behavior'], 'video' ); ?>>Show a specified video (no additional quota cost)</option>
            <option value="no_message" <?php selected( $options['fallback_behavior'], 'no_message' ); ?>>Show nothing at all (no additional quota cost)</option>
        </select>
    </p>

    <p class="fallback message">
        <label for="youtube_live_settings[fallback_message]">Custom HTML message:</label><br/>
        <textarea cols="50" rows="8" name="youtube_live_settings[fallback_message]" placeholder="<p>Sorry, there&rsquo;s no live stream at the moment. Please check back later or take a look at <a target='_blank' href='https://youtube.com/channel/<?php echo $options['youtube_live_channel_id']; ?>'>all our videos</a>.</p>
        <p><button type='button' class='button' id='check-again'>Check again</button><span class='spinner' style='display:none;'></span></p>."><?php echo $options['fallback_message']; ?></textarea>
    </p>

    <div class="fallback upcoming">
        <p>This option will fetch all your upcoming scheduled live videos from the YouTube API and cache them for 24 hours or until the first video is scheduled to begin, whichever is soonest. If you schedule more live videos, press the button below to manually flush the server’s cache. <strong>Note:</strong> if you have no upcoming scheduled videos, the last scheduled video will be shown instead.</p>

        <?php
        $redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );

        $upcoming_cache = get_transient( 'youtube-live-upcoming-videos' );
        ?>

        <div class="wp-youtube-live-upcoming-cache"><?php echo format_upcoming_videos( $upcoming_cache ); ?></div>

        <p>
            <button type="button" class="button-primary" id="updatewpYTUpcomingCache" data-action="updatewpYTUpcomingCache" data-nonce="<?php echo wp_create_nonce( 'wpYTcache_nonce' ); ?>">Clear Cached Upcoming Videos</button> (costs 100 quota units each time)<span class="spinner" style="visibility: hidden;float: none;"></span>
        </p>
        <!-- TODO: add secondary fallback if no upcoming videos are scheduled -->
    </div>

    <p class="fallback playlist">
        <label for="youtube_live_settings[fallback_playlist]">Fallback Playlist URL:</label><br/>
        <input type="text" name="youtube_live_settings[fallback_playlist]" size="45" placeholder="PLBCF2DAC6FFB574DE" value="<?php echo $options['fallback_playlist']; ?>" />
    </p>

    <p class="fallback video">
        <label for="youtube_live_settings[fallback_video]">Fallback Video URL:</label><br/>
        <input type="text" name="youtube_live_settings[fallback_video]" size="45" placeholder="https://youtu.be/dQw4w9WgXcQ" value="<?php echo $options['fallback_video']; ?>" />
    </p>

    <p>For more information on quota usage, read the <a href="https://github.com/macbookandrew/wp-youtube-live#quota-units">plugin documentation</a> as well as the <a href="https://developers.google.com/youtube/v3/getting-started#quota" target="_blank">YouTube API documentation</a>.</p>
    <?php
}

/**
 * Print auto-refresh field
 */
function youtube_live_auto_refresh_render() {
    $options = get_option( 'youtube_live_settings' );
    if ( ! array_key_exists( 'auto_refresh', $options ) ) {
        $options['auto_refresh'] = false;
    }
    ?>
    Should the player page automatically check every 30 seconds until a live video is available? <label><input type="radio" name="youtube_live_settings[auto_refresh]" value="true" <?php checked( $options['auto_refresh'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[auto_refresh]" value="false" <?php checked( $options['auto_refresh'], 'false' ); ?>> No</label>
    <p><strong>Warning:</strong> depending on how many users are on the page, this may overload your server with requests.</p>
    <?php
}

/**
 * Print debugging field
 */
function youtube_live_debugging_render() {
    $options = get_option( 'youtube_live_settings' );
    if ( ! array_key_exists( 'debugging', $options ) ) {
        $options['debugging'] = false;
    }
    ?>
    Show debugging information in an HTML comment for logged-in users? <label><input type="radio" name="youtube_live_settings[debugging]" value="true" <?php checked( $options['debugging'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[debugging]" value="false" <?php checked( $options['debugging'], 'false' ); ?>> No</label>
    <?php
}

/**
 * Print API settings field
 */
function youtube_live_api_settings_section_callback() {
    echo __( 'Enter your YouTube details below. Once you&rsquo;ve entered the required details below, add the shortcode <code>[youtube_live]</code> to any post/page to display the live player.', 'youtube_live' );
}

/**
 * Print settings form
 */
function youtube_live_options_page() { ?>
    <div class="wrap">
        <form action="options.php" method="post">
            <?php
            settings_fields( 'youtube_live_options' );
            do_settings_sections( 'youtube_live_options' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Manually clear upcoming video cache
 * @return string formatted HTML string
 */
function refresh_youtube_live_upcoming_cache() {
    if ( ! wp_verify_nonce( $_POST[ 'nonce' ], 'wpYTcache_nonce' ) ) {
        die( 'Invalid nonce.' . var_export( $_POST, true ) );
    }

    $youtube_options = get_option( 'youtube_live_settings' );
    $youtube_live = new EmbedYoutubeLiveStreaming( $youtube_options['youtube_live_channel_id'], $youtube_options['youtube_live_api_key'] );

    if ( $_POST['action'] === 'updatewpYTUpcomingCache' ) {
        if ( $youtube_live->clearUpcomingVideoInfo() ) {
            echo json_encode( format_upcoming_videos( get_transient( 'youtube-live-upcoming-videos' ) ) );
            die();
        }
    }
}
add_action( 'wp_ajax_updatewpYTUpcomingCache', 'refresh_youtube_live_upcoming_cache' );

/**
 * Return list of video IDs and start times
 * @param  array  $input possibly serialized array of $id => $start_time values
 * @return string HTML output
 */
function format_upcoming_videos( $input ) {
    if ( $input ) {
        $video_array = maybe_unserialize( $input );
    }

    global $wpdb;
    $transient_expire_time = $wpdb->get_col( $wpdb->prepare(
        'SELECT option_value FROM %1$soptions WHERE option_name = "%2$s";',
        $wpdb->prefix,
        '_transient_timeout_youtube-live-upcoming-videos'
    ), 0);

    $upcoming_list = '<h3>Cache Contents</h3>
    <p>Cache valid until ' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $transient_expire_time[0] ) . '.</p>
    <ul>';
    if ( is_array( $video_array ) && count( $video_array ) > 0 ) {
        foreach ( $video_array as $id => $start_time ) {
            $upcoming_list .= '<li>Video ID <code>'. $id . '</code> starting ' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $start_time ) . '</li>';
        }
    } else {
        $upcoming_list .= '<li>Cache is currently empty. Make sure you have some videos scheduled, then press the button below to manually update the cache.</li>';
    }
    $upcoming_list .= '</ul>';

    return $upcoming_list;
}
