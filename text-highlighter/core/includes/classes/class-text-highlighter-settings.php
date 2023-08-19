<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Text_Highlighter_Settings
 *
 * This class contains all of the plugin settings.
 * Here you can configure the whole plugin data.
 *
 * @package		SKTH
 * @subpackage	Classes/Text_Highlighter_Settings
 * @author		Shyam
 * @since		1.0.0
 */
class Text_Highlighter_Settings{

	/**
	 * The plugin name
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	private $plugin_name;

	/**
	 * Our Text_Highlighter_Settings constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){

		$this->plugin_name = sk_highlighter;
		add_action('admin_menu',array($this,'custom_table_options_page'));

	}

	/**
	 * ######################
	 * ###
	 * #### CALLABLE FUNCTIONS
	 * ###
	 * ######################
	 */

	/**
	 * Return the plugin name
	 *
	 * @access	public
	 * @since	1.0.0
	 * @return	string The plugin name
	 */
	public function get_plugin_name(){
		return apply_filters( 'SKTH/settings/get_plugin_name', $this->plugin_name );
	}


	public function custom_table_options_page() {
		add_options_page(
			'Text Highlighter Settings', // Page title
			'Text Highlighter', // Menu title
			'manage_options', // Capability required to access the page
			'text_highlighter', // Menu slug (unique identifier)
			array($this,'display_custom_table_page') // Callback function to display the page content
		);
	}
	
	function display_custom_table_page() {
		// Load the custom table page template
		require_once SKTH_PLUGIN_DIR . 'core/includes/classes/class-show-data.php';
		$custom_table = new Custom_Table_List();
		// Process bulk actions, if any
		$custom_table->process_bulk_action();

		// Prepare the items to be displayed in the table
		$custom_table->prepare_items();
		?>
		<div class="wrap">
			<h1>Welcome to the Text Highlighter</h1>
			<p>Welcome to SK NetKing Visit our blog- <a href='https://sknetking9.blogspot.com'>Click Here.</a></p>
		<form method="post">
		<?php $custom_table->display(); ?>
		<?php wp_nonce_field('bulk-action-nonce'); ?>
		</form>
		</div>
	<?php
	// Display the table
		// $custom_table->display();
	}
	
}