<div class="wrap">
	<h2 class="wpzinc">
    	<?php echo $this->base->plugin->displayName; ?> &raquo; <?php _e( 'Keywords', $this->base->plugin->name ); ?>
    	<a href="admin.php?page=<?php echo $page; ?>&amp;cmd=form" class="add-new-h2"><?php _e( 'Add Keyword', $this->base->plugin->name ); ?></a>
        
    	<?php
	    // Search Subtitle
	    if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
	    	?>
	    	<span class="subtitle"><?php _e( 'Search results for', $this->base->plugin->name ); ?> &#8220;<?php echo urldecode( $_REQUEST['s'] ); ?>&#8221;</span>
	    	<?php
	    }
	    ?>
    </h2>
           
    <?php
    // Notices
    foreach ( $this->notices as $type => $notices_type ) {
        if ( count( $notices_type ) == 0 ) {
            continue;
        }
        ?>
        <div class="<?php echo ( ( $type == 'success' ) ? 'updated' : $type ); ?> notice">
            <?php
            foreach ( $notices_type as $notice ) {
                ?>
                <p><?php echo $notice; ?></p>
                <?php
            }
            ?>
        </div>
        <?php
    }
    ?>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <!-- Content -->
            <div id="post-body-content">
                <div id="normal-sortables" class="meta-box-sortables ui-sortable publishing-defaults">  
                	<form action="admin.php?page=<?php echo $page; ?>" method="post">
                		<p class="search-box">
                	    	<label class="screen-reader-text" for="post-search-input"><?php _e(' Search Keywords', $this->base->plugin->name ); ?>:</label>
                	    	<input type="text" id="field-search-input" name="s" value="<?php echo ( isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : ''); ?>" />
                	    	<input type="submit" name="search" class="button" value="<?php _e( 'Search Keywords', $this->base->plugin->name ); ?>" />
                	    </p>
                	    
                		<?php   
                		// Output WP_List_Table
                		$keywords_table = new Page_Generator_Pro_Keywords_Table();
                		$keywords_table->prepare_items();
                		$keywords_table->display(); 
                		?>	
                	</form>
                </div>
            </div>
            <!-- /Content -->

            <!-- Sidebar -->
            <div id="postbox-container-1" class="postbox-container">
                <?php require( $this->base->plugin->folder . '/_modules/dashboard/views/sidebar-upgrade.php' ); ?>      
            </div>
            <!-- /Sidebar -->
        </div>
    </div>

    <!-- Upgrade -->
    <div class="metabox-holder columns-1">
        <div id="post-body-content">
            <?php require( $this->base->plugin->folder . '/_modules/dashboard/views/footer-upgrade.php' ); ?>
        </div>
    </div>
</div>