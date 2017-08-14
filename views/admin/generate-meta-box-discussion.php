 <div class="option">
	<label for="comments">
    	<div class="left">
    		<strong><?php _e( 'Allow comments?', $this->base->plugin->name ); ?></strong>
    	</div>
    	<div class="right">
    		<input type="checkbox" id="comments" name="<?php echo $this->base->plugin->name; ?>[comments]" value="1"<?php checked( $this->settings['comments'], 1 ); ?> />
    	</div>
	</label>
</div>

<div class="option">
	<label for="trackbacks">
    	<div class="left">
    		<strong><?php _e( 'Allow trackbacks and pingbacks.', $this->base->plugin->name ); ?></strong>
    	</div>
    	<div class="right">
    		<input type="checkbox" id="trackbacks" name="<?php echo $this->base->plugin->name; ?>[trackbacks]" value="1"<?php checked( $this->settings['trackbacks'], 1 ); ?> />
    	</div>
	</label>
</div>