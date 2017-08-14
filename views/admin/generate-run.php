<div class="wrap">
    <h2 class="wpzinc">
        <?php echo $this->base->plugin->displayName; ?>
        &raquo; 
        <?php echo sprintf( __( 'Generating &quot;%s&quot;', $this->base->plugin->name ), $settings['title'] ); ?>
    </h2>

    <p>
    	<?php 
        _e( 'Please be patient while content is generated. This can take a while if your server is slow (inexpensive hosting) 
        or if you have a lot of keywords and high number of items to generate. 
    	Do not navigate away from this page until this script is done or all items will not be generated.
    	You will be notified via this page when the process is completed.', $this->base->plugin->name ); ?>
    </p>

    <!-- Progress Bar -->
    <div id="progress-bar"></div>
    <div id="progress">
        <span id="progress-number">0</span>
        <span> / <?php echo $settings['numberOfPosts']; ?></span>
    </div>

    <!-- Status Updates -->
    <div id="log">
        <ul></ul>
    </div>

    <p>
        <!-- Cancel Button -->
        <a href="post.php?post=<?php echo $id; ?>&amp;action=edit" class="button button-red page-generator-pro-generate-cancel-button">
            <?php _e( 'Stop Generation', $this->base->plugin->name ); ?>
        </a>

        <!-- Return Button (display when generation routine finishes -->
        <a href="post.php?post=<?php echo $id; ?>&amp;action=edit" class="button button-primary page-generator-pro-generate-return-button">
            <?php _e( 'Return to Group', $this->base->plugin->name ); ?>
        </a>
    </p>

    <!-- Triggers AJAX request to run numberOfPosts -->
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var page_generator_pro_cancelled = false;
            $('#progress-bar').synchronous_request({
                url: ajaxurl,
                number_requests: <?php echo $settings['numberOfPosts'] + $settings['resumeIndex']; ?>,
                offset: <?php echo $settings['resumeIndex']; ?>,
                data: {
                    id:     <?php echo $id; ?>,
                    action: 'page_generator_pro_generate'   
                },
                onRequestSuccess:function(response, currentIndex) {
                    // Update counter
                    $( '#progress-number' ).text( ( currentIndex + 1 ) );

                    if (response.success) {
                        $('#log ul').append('<li>Created <a href="'+response.data.url+'" target="_blank">'+response.data.url+'</a></li>');
                    
                        // Run the next request, unless the user clicked the 'Stop Generation' button
                        if ( page_generator_pro_cancelled == true ) {
                            this.onFinished();
                            return false;
                        }

                        // Run the next request
                        return true;
                    } else {
                        // Something went wrong
                        $('#log ul').append('<li class="error">' + response.data + '</a></li>'); 

                        // Don't run any more requests
                        return false;
                    }
                },
                onRequestError: function(xhr, textStatus, e, currentIndex) {
                    // Update counter
                    $( '#progress-number' ).text( ( currentIndex + 1 ) );

                    $('#log ul').append('<li class="error">' + xhr.status + ' ' + xhr.statusText + '</li>');

                    // Don't run any more requests
                    return false;
                },
                onFinished: function() {
                    // If the user clicked the 'Stop Generation' button, show that in the log.
                    if ( page_generator_pro_cancelled == true ) {
                        $('#log ul').append('<li>Process cancelled by user</li>');
                    } else {
                        $('#log ul').append('<li>Finished</li>');
                    }

                    // Hide the 'Stop Generation' button
                    $('a.page-generator-pro-generate-cancel-button').hide();

                    // Show the 'Return to Group' button
                    $('a.page-generator-pro-generate-return-button').removeClass('page-generator-pro-generate-return-button');
                }
            });

            // Sets the page_generator_pro_cancelled flag to true when the user clicks the 'Stop Generation' button
            $('a.page-generator-pro-generate-cancel-button').on('click', function(e) {
                e.preventDefault();
                page_generator_pro_cancelled = true;
            });
        });
    </script>
</div>