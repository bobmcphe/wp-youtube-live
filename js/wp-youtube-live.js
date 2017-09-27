/**
 * Set variables and event listener
 */
var wpYTdata = [{
    'action': 'load_youtube_live',
    'isAjax': true,
}],
    wpYTevent = document.createEvent('Event');
wpYTevent.initEvent('wpYouTubeLiveStarted', true, true);

/**
 * Handle auto-refresh
 */
if (wpYouTubeLive.auto_refresh == 'true') {
    var checkAgainTimer = setInterval(function() {
        wpYTdata.requestType = 'refresh';
        wpYTcheckAgain(wpYTdata);
    }, wpYouTubeLive.refreshInterval * 1000);
}

/**
 * Check for live-stream
 * @param {object} data info to pass to WP
 */
function wpYTsendRequest(wpYTdata) {
    jQuery('.wp-youtube-live .spinner').show();
    jQuery.ajax({
        method: "POST",
        url: wpYouTubeLive.ajaxUrl,
        data: wpYTdata
    })
    .done(function(response) {
        var requestData = JSON.parse(response);
        if (requestData.error) {
            jQuery('.wp-youtube-live-error').append(requestData.error).show();
        } else if (requestData.live) {
            jQuery('.wp-youtube-live').replaceWith(requestData.content).addClass('live');
            jQuery('.wp-youtube-live-error').hide();
            window.dispatchEvent(wpYTevent);
        }
    })
    .always(function() {
        jQuery('.wp-youtube-live .spinner').hide();
    })
}

/**
 * Check if a live stream has been loaded
 * @param {object} data parameters for callback function
 */
function wpYTcheckAgain(wpYTdata) {
    console.log('checking again...');
    if (jQuery('.wp-youtube-live').hasClass('live')) {
        console.log('aborting check since video is live');
        clearInterval(checkAgainTimer);
    } else {
        console.log('sending request...');
        wpYTsendRequest(wpYTdata);
    }
}

/**
 * Handle autorefresh
 */
jQuery(document).ready(function(){
    // run an initial check to clear caches
    wpYTsendRequest(wpYTdata);

    jQuery('body').on('click', 'button#check-again', function(event) {
        event.preventDefault();
        wpYTdata.requestType = 'refresh';
        wpYTcheckAgain(wpYTdata);
    });
});

/**
 * Play video when it is ready
 * @param {object} event YouTube player event
 */
function wpYTonPlayerReady(event) {
    event.target.playVideo();
}

/**
 * Get fallback behavior from server when video ends
 * @param {object} event YouTube player event
 */
function wpYTonPlayerStateChange(event) {
    console.log(event.data);
    if (event.data == 0) {
        jQuery('.wp-youtube-live').removeClass('live').addClass('completed');
        wpYTcheckAgain();
    }
}
