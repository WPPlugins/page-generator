<!-- Permalink -->
<div class="option">
	<div class="left">
		<strong><?php _e( 'Permalink', $this->base->plugin->name ); ?></strong>
	</div>
	<div class="right">
		<!-- Keywords -->
		<select size="1" class="right wpzinc-tags" data-element="#permalink">
		    <option value=""><?php _e( '--- Insert Keyword ---', $this->base->plugin->name ); ?></option>
		    <?php
		    foreach ( $this->keywords as $keyword ) {
		        ?>
		        <option value="{<?php echo $keyword->keyword; ?>}"><?php echo $keyword->keyword; ?></option>
		        <?php
		    }
		    ?>
		</select>	
	</div>
	<div class="full">
		<input type="text" id="permalink" name="<?php echo $this->base->plugin->name; ?>[permalink]" value="<?php echo $this->settings['permalink']; ?>" class="widefat" />
	
    	<p class="description">
    		<?php _e( 'Letters, numbers, underscores and dashes only.', $this->base->plugin->name ); ?>
    	</p>
	</div>
</div>