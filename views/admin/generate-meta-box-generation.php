<div class="option">
	<div class="left">
		<strong><?php _e( 'No. Posts', $this->base->plugin->name ); ?></strong>
	</div>
	<div class="right">
		<input type="number" name="<?php echo $this->base->plugin->name; ?>[numberOfPosts]" value="<?php echo $this->settings['numberOfPosts']; ?>" step="1" min="0" class="widefat" />
	</div>
	<p class="description">
		<?php _e( 'The number of Pages to generate. If zero or blank, all Pages will be generated.', $this->base->plugin->name ); ?>
	</p>
</div>