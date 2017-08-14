<!-- Template -->
<div class="option <?php echo $hierarchical_post_types_class; ?>">
	<div class="full">
    	<strong><?php _e( 'Template', $this->base->plugin->name ); ?></strong>
    </div>
	<div class="full">
    	<select name="<?php echo $this->base->plugin->name; ?>[pageTemplate]" size="1">
    		<option value="default"<?php selected( $this->settings['pageTemplate'], 'default' ); ?>>
    			<?php _e( 'Default Template', $this->base->plugin->name ); ?>
    		</option>
    		<?php page_template_dropdown( $this->settings['pageTemplate'] ); ?>
		</select>
	</div>
</div>