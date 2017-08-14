<div class="option">
	<?php 
	// Nonce field
	wp_nonce_field( 'save_generate', $this->base->plugin->name . '_nonce' ); 
	?>

	<!-- Save Options -->
	<div id="publishing-action">
		<!-- 
		#submitpost is required, so WordPress can unload the beforeunload.edit-post JS event.
		If we didn't do this, the user would always get a JS alert asking them if they want to navigate
		away from the page as they may lose their changes
		-->
		<div id="submitpost">
			<?php
			// Save
			if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) {
				// Publish
				?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish' ) ?>" />
				<?php submit_button( __( 'Save' ), 'primary button-large', 'publish', false ); ?>
				<?php
			} else {
				// Update
				?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ) ?>" />
				<?php submit_button( __( 'Save' ), 'primary button-large', 'save', false ); ?>
				<?php
			}

			// Test & Generate
			?>

			<?php submit_button( __( 'Test' ), 'primary button-large', 'test', false ); ?>

			<?php submit_button( __( 'Generate' ), 'primary button-large', 'generate', false ); ?>
		</div>
	</div>
</div>