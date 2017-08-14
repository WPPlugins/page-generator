<!-- Keywords -->
<div id="keywords-title">
	<select size="1" class="right wpzinc-tags" data-element="#title">
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