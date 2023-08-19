<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Text_Highlighter' ) ) :

	/**
	 * Main Text_Highlighter Class.
	 *
	 * @package		SKTH
	 * @subpackage	Classes/Text_Highlighter
	 * @since		1.0.0
	 * @author		Shyam
	 */
	final class Text_Highlighter {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Text_Highlighter
		 */
		private static $instance;

		/**
		 * SKTH helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Text_Highlighter_Helpers
		 */
		public $helpers;

		/**
		 * SKTH settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Text_Highlighter_Settings
		 */
		public $settings;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'text-highlighter' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'text-highlighter' ), '1.0.0' );
		}

		/**
		 * Main Text_Highlighter Instance.
		 *
		 * Insures that only one instance of Text_Highlighter exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Text_Highlighter	The one true Text_Highlighter
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Text_Highlighter ) ) {
				self::$instance					= new Text_Highlighter;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Text_Highlighter_Helpers();
				self::$instance->settings		= new Text_Highlighter_Settings();
			
				//Fire the plugin logic
				new Text_Highlighter_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'SKTH/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function includes() {
			require_once SKTH_PLUGIN_DIR . 'core/includes/classes/class-text-highlighter_ajax.php';
			require_once SKTH_PLUGIN_DIR . 'core/includes/classes/class-text-highlighter-settings.php';

			require_once SKTH_PLUGIN_DIR . 'core/includes/classes/class-text-highlighter-run.php';
			
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'text-highlighter', FALSE, dirname( plugin_basename( SKTH_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.