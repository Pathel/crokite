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

   			// Get the character name...
       		$xml_url = "http://api.eveonline.com/eve/CharacterInfo.xml.aspx?characterID=".$instance['character_id'];
       		$xmlstr = simplexml_load_file($xml_url);
			$character_name = $xmlstr->result->characterName;

   			echo "<h3>Latest kills for ".$character_name."</h3>";   			
	        foreach ($display_kills as $kill) {
	            echo "<div class='kill-outer'>";
	            $kill_url = 'https://zkillboard.com/kill/'.$kill['killID'].'/';

	            // Use the character ID from the zkillboard API to look up the ship name from the EVE XML api.
	            $killed_ship_id = $kill['victim']['shipTypeID'];
            	$xml_url = 'http://api.eveonline.com/eve/TypeName.xml.aspx?ids='.$killed_ship_id;
            	$xmlstr = simplexml_load_file($xml_url);
            	$ship_type_string = $xmlstr->result->rowset[0]->row['typeName'];

            	// Use the ID to get the ship image from the image server...

            	$image_url = "https://image.eveonline.com/Type/".$killed_ship_id."_64.png";
            	echo "<div>";
            	echo "<a href='$kill_url'>";
            	echo "<img src='$image_url'>";
            	echo "</a>";
            	echo "</div>";
            	echo "<a href='$kill_url'>";
	            
	            if ($kill['victim']['characterName'] == "") {
	                echo $kill['victim']['corporationName'].', '.$kill['victim']['allianceName'];
	            } else {
	                echo $kill['victim']['characterName'].', '.$kill['victim']['allianceName'] ;
	            }

	            echo "</a>";

	            echo "<br />";
	            echo "Shiptype: ".$ship_type_string;
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
