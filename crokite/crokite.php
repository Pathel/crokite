<?php
/*
Plugin Name: Crokite
Plugin URI: http://dustinjones.site/crokite-plugin
Description: A plugin for demonstrating basic interaction with the EVE Online API and Zkillboard API.
Version: 0.1
Author: Pathel
Author URI: http://dustinjones.site
License: GPL2
*/

class wp_my_plugin extends WP_Widget {
	
	// Widget plugin constructor
	function wp_my_plugin() {
        parent::WP_Widget(false, $name = __('Crokite', 'wp_widget_plugin') );		
	}

	// Widget forms ...
	function form($instance) {
		// Pass data if given instance, otherwise prefill with empty strings.
		if ($instance) {
			$character_id = esc_attr($instance['character_id']);
		}
		else {
			$character_id = '';
		}
		?>
	    <p><label for="<?php echo $this->get_field_id('character_id'); ?>">Character ID: <input class="widefat" id="<?php echo $this->get_field_id('character_id'); ?>" name="<?php echo $this->get_field_name('character_id'); ?>" type="text" value="<?php echo attribute_escape($character_id); ?>" /></label></p>
		<?php	
	}

	// Update ...
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['character_id'] = strip_tags($new_instance['character_id']);

		// Hit the API, store decoded json result
		$kills_raw_json = file_get_contents('https://zkillboard.com/api/kills/characterID/'.$instance['character_id'].'/limit/3/');
		$kills = json_decode($kills_raw_json, true);
		$instance['kills'] = $kills;

		return $instance;
	}

	// Display widget ...
	function widget($args, $instance) {
		// Get options
		extract($args);
		$character_id = $instance['character_id'];
		// Display widget
   		echo '<div class="widget-text wp_widget_plugin_box">';

   		// If empty settings, show a placeholder 
   		if ($character_id == '') {
   			echo '<p>Please enter a character id in widget settings.</p>';
   		}

   		// Otherwise iterate through stored kills and display them
   		else {
   			$display_kills = $instance['kills'];
   			echo "<h3>Latest kills for ".$instance['character_id']."</h3>";   			
	        foreach ($display_kills as $kill) {
	            echo "<div class='kill-outer'>";
	            $kill_url = 'https://zkillboard.com/kill/'.$kill['killID'].'/';
            	echo "<a href='$kill_url'>";
	            
	            if ($kill['victim']['characterName'] == "") {
	                echo $kill['victim']['corporationName'].', '.$kill['victim']['allianceName'];
	            } else {
	                echo $kill['victim']['characterName'].', '.$kill['victim']['allianceName'] ;
	            }

	            echo "</a>";

	            echo "<br />";
	            echo "Shiptype: ".$kill['victim']['shipTypeID'];
	            echo "<br />";
	            echo "Damage Taken: ".$kill['victim']['damageTaken'];

	            echo "</div>";
	            echo "<br />";
	        }

   		}
   		echo '</div>';
   		echo $after_widget;
	}

}

// Register the widget.
add_action('widgets_init', create_function('', 'return register_widget("wp_my_plugin");'));

?>
