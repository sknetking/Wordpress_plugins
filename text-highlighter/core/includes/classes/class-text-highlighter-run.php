<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Text_Highlighter_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		SKTH
 * @subpackage	Classes/Text_Highlighter_Run
 * @author		Shyam
 * @since		1.0.0
 */
class Text_Highlighter_Run{

	/**
	 * Our Text_Highlighter_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		$this->add_hooks();
		$this->create_table();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks(){
	
		add_action( 'plugin_action_links_' . SKTH_PLUGIN_BASE, array( $this, 'add_plugin_action_link' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts_and_styles' ), 20 );
	
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	* Adds action links to the plugin list table
	*
	* @access	public
	* @since	1.0.0
	*
	* @param	array	$links An array of plugin action links.
	*
	* @return	array	An array of plugin action links.
	*/
	public function add_plugin_action_link( $links ) {

		$links['our_shop'] = sprintf( '<a href="%s" title="Settings" style="font-weight:700;">%s</a>', '/wp-admin/options-general.php?page=text_highlighter', __( 'Settings', 'text-highlighter' ) );

		return $links;
	}

	public function create_table() {
		global $wpdb;
	  $table_name = $wpdb->prefix . 'highlighted_texts';
	  $charset_collate = $wpdb->get_charset_collate();
  
	  
	  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != "highlighted_texts") {
	  // SQL query to create the table
	  $sql = "CREATE TABLE $table_name (
		  id INT NOT NULL AUTO_INCREMENT,
		  ip_address VARCHAR(50) NOT NULL,
		  post_id INT NOT NULL,
		  selected_text TEXT NOT NULL,
		  PRIMARY KEY (id)
	  ) $charset_collate;";
  
	  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	  $result = dbDelta($sql);
  
	  if (is_wp_error($result)) {
		  error_log("Error creating table: " . $result->get_error_message());
	  }
		}
  	}


	/**
	 * Enqueue the frontend related scripts and styles for this plugin.
	 * All of the added scripts andstyles will be available on every page within the frontend.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function enqueue_frontend_scripts_and_styles() {
		global $post;
		wp_enqueue_style( 'SKTH-frontend-styles', SKTH_PLUGIN_URL . 'core/includes/assets/css/frontend-styles.css', array(), SKTH_VERSION, 'all' );
		wp_enqueue_script( 'SKTH-frontend-scripts', SKTH_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js',SKTH_VERSION, false );
		wp_enqueue_script( 'highlight', SKTH_PLUGIN_URL . 'core/includes/assets/js/highlighting.js');

		wp_localize_script('SKTH-frontend-scripts', 'ajax_obj', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'postID' => $post->ID,
		));
	}

}
