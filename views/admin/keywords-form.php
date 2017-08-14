<div class="wrap">
    <h2 class="wpzinc">
    	<?php echo $this->base->plugin->displayName; ?> &raquo; <?php _e( 'Keywords', $this->base->plugin->name ); ?>
    	<a href="admin.php?page=<?php echo $page; ?>&amp;cmd=form" class="add-new-h2"><?php _e( 'Add Keyword', $this->base->plugin->name ); ?></a>
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
    			<!-- Form Start -->
    			<form id="post" class="<?php echo $this->base->plugin->name; ?>" name="post" method="post" action="admin.php?page=<?php echo $page; ?>&amp;cmd=form<?php echo ( isset( $_GET['id'] ) ? '&id=' . absint( $_GET['id'] ) : '' ); ?>" enctype="multipart/form-data">		
	    	    	<div id="normal-sortables" class="meta-box-sortables ui-sortable">                        
		                <div id="keyword-panel" class="postbox">
		                    <h3 class="hndle"><?php _e( 'Keyword', $this->base->plugin->name ); ?></h3>
		                    <input type="hidden" name="id" id="id" value="<?php echo ( ( isset( $keyword ) && isset( $keyword['keywordID'] ) ) ? $keyword['keywordID'] : '' ); ?>" />
	
		                    <div class="option">
		                    	<div class="left">
		                    		<strong><?php _e( 'Keyword', $this->base->plugin->name ); ?></strong>
		                    	</div>
		                    	<div class="right">
		                    		<input type="text" name="keyword" value="<?php echo ( isset( $keyword['keyword'] ) ? $keyword['keyword'] : '' ); ?>" class="widefat" />
		                    	
			                    	<p class="description">
			                    		<?php _e( 'A unique template tag name, which can then be used when generating content.', $this->base->plugin->name ); ?>
			                    	</p>
		                    	</div>
		                    </div>
		                    
		                    <div class="option">
		                    	<div class="full">
		                    		<strong><?php _e( 'Terms', $this->base->plugin->name ); ?></strong>
		                    	</div>
		                    	<div class="full">
		                    		<textarea name="data" rows="10" class="widefat" style="height:300px"><?php echo ( isset( $keyword['data']) ? $keyword['data'] : '' ); ?></textarea>
		                    	
			                    	<p class="description">
			                    		<?php _e( 'Word(s) or phrase(s) which will be cycled through when generating content using the above keyword template tag.', $this->base->plugin->name ); ?>
			                    		<br />
			                    		<?php _e( 'One word / phase per line.', $this->base->plugin->name ); ?>
			                    	</p>
		                    	</div>
		                    </div>
		                    
		                    <div class="option">
	                    		<?php wp_nonce_field( 'save_keyword', $this->base->plugin->name . '_nonce' ); ?>
	                			<input type="submit" name="submit" value="<?php _e( 'Save', $this->base->plugin->name ); ?>" class="button button-primary" />
		                    </div>
		                </div>
					</div>
					<!-- /normal-sortables -->
			    </form>
			    <!-- /form end -->
    		</div>
    		<!-- /post-body-content -->

    		<!-- Sidebar -->
            <div id="postbox-container-1" class="postbox-container">
                <?php require( $this->base->plugin->folder . '/_modules/dashboard/views/sidebar-upgrade.php' ); ?>      
            </div>
    	</div>

    	<!-- Upgrade -->
    	<div class="metabox-holder columns-1">
    		<div id="post-body-content">
    			<?php require( $this->base->plugin->folder . '/_modules/dashboard/views/footer-upgrade.php' ); ?>
    		</div>
    	</div>
	</div>       
</div>