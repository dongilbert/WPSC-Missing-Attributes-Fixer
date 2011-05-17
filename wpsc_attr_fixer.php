<?php

function wpsc_image_size_action( $buffer ) {
	$buffer = str_replace('" />', '"/>', $buffer);
	
	$dom = new DOMDocument();
	$dom->preserveWhiteSpace = true;
	$dom->loadXML($buffer);
	$images = $dom->getElementsByTagName('img');
	
	foreach($images as $image)
	{
		$src = $image->getAttribute('src');
		$src = str_replace(get_bloginfo('url'), '', $src);
		$image_size = getimagesize(get_bloginfo('url') . $src);
		$width = $image_size[0];
		$height = $image_size[1];
		$image->setAttribute('width', $width);
		$image->setAttribute('height', $height);
		if(!$image->getAttribute('alt')) {
			$image->setAttribute('alt', get_bloginfo('name'));
		}
	}
	/*
	$links = $dom->getElementsByTagName('a');
	
	foreach($links as $link)
	{
		if(!$link->getAttribute('title')) {
			$link->setAttribute('title', get_bloginfo('name'));
		}
	}
	*/
	$buffer = $dom->saveHTML();
	$buffer = str_replace(array("</meta>&raquo;", "</meta>\n&raquo;"), "</meta>", $buffer);
	$buffer = str_replace(array('></br>', '></meta>', '></img>', '></link>'), '/>', $buffer);
	return $buffer;
}

function wpsc_image_size_actions() {
	global $cache_wpsc_image_size;
	if( $cache_wpsc_image_size == '1' ) {
		add_filter( 'wpsupercache_buffer', 'wpsc_image_size_action' );
	}
}
add_cacheaction( 'add_cacheaction', 'wpsc_image_size_actions' );

//Add Image Sizes to img tags.
function wpsc_image_size_admin() {
	global $cache_wpsc_image_size, $wp_cache_config_file, $valid_nonce;
	
	$cache_wpsc_image_size = $cache_wpsc_image_size == '' ? '0' : $cache_wpsc_image_size;

	if(isset($_POST['cache_wpsc_image_size']) && $valid_nonce) {
		$cache_wpsc_image_size = (int)$_POST['cache_wpsc_image_size'];
		wp_cache_replace_line('^ *\$cache_wpsc_image_size', "\$cache_wpsc_image_size = '$cache_wpsc_image_size';", $wp_cache_config_file);
		$changed = true;
	} else {
		$changed = false;
	}
	$id = 'wpsc_image_size-section';
	?>
		<fieldset id="<?php echo $id; ?>" class="options"> 
		<h4><?php _e( 'Append Image Sizes', 'wp-super-cache' ); ?></h4>
		<form name="wp_manager" action="<?php echo $_SERVER[ "REQUEST_URI" ]; ?>" method="post">
		<label><input type="radio" name="cache_wpsc_image_size" value="1" <?php if( $cache_wpsc_image_size ) { echo 'checked="checked" '; } ?>/> <?php _e( 'Enabled', 'wp-super-cache' ); ?></label>
		<label><input type="radio" name="cache_wpsc_image_size" value="0" <?php if( !$cache_wpsc_image_size ) { echo 'checked="checked" '; } ?>/> <?php _e( 'Disabled', 'wp-super-cache' ); ?></label>
		<p><?php _e( 'Enables or disables plugin to append the width and height to an img tag.', 'wp-super-cache' ); ?></p>
		<?php
		if ($changed) {
			if ( $cache_wpsc_image_size )
				$status = __( "enabled" );
			else
				$status = __( "disabled" );
			echo "<p><strong>" . sprintf( __( "Append Image Sizes is now %s", 'wp-super-cache' ), $status ) . "</strong></p>";
		}
	echo '<div class="submit"><input ' . SUBMITDISABLED . 'type="submit" value="' . __( 'Update', 'wp-super-cache' ) . '" /></div>';
	wp_nonce_field('wp-cache');
	?>
	</form>
	</fieldset>
	<?php

}
add_cacheaction( 'cache_admin_page', 'wpsc_image_size_admin' );
