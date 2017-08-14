<div id="author" class="option">
	<div class="left">
		<strong><?php _e( 'Author', $this->base->plugin->name ); ?></strong>
	</div>
	<div class="right">
		<select name="<?php echo $this->base->plugin->name; ?>[author]" size="1">
			<?php
			if ( $authors && count( $authors ) > 0 ) {
        		foreach ( $authors as $author ) {
        			?>
        			<option value="<?php echo $author->ID; ?>"<?php selected( $author->ID, $this->settings['author'] ); ?>>
        				<?php echo $author->user_nicename; ?>
        			</option>
        			<?php
        		}
        	}
			?>	
		</select>
	</div>	
</div>

<div class="option">
	<label for="rotate-authors">
    	<div class="left">
    		<strong><?php _e( 'Rotate?', $this->base->plugin->name ); ?></strong>
    	</div>
    	<div class="right">
    		<input type="checkbox" id="rotate-authors" name="<?php echo $this->base->plugin->name; ?>[rotateAuthors]" value="1"<?php checked( $this->settings['rotateAuthors'], 1 ); ?> data-conditional="author" data-conditional-display="false" />
    	
        	<span class="description">
        		<?php _e( 'If checked, will choose a WordPress User at random for each Page/Post generated.', $this->base->plugin->name ); ?>
        	</span>
    	</div>
	</label>
</div>