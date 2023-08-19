<?php
/**
 * SK Text Highlighter
 *
 * @package       SKTH
 * @author        Shyam Sahani
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   SK Text Highlighter
 * Plugin URI:   
 * Description:   Highlight and store selected text in database with color choices.
 * Version:       1.0.0
 * Author:        Shyam
 * Author URI:    https://sknetking9.blogspot.com
 * Text Domain:   text-highlighter
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with SK Text Highlighter. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'sk_highlighter',			'SK Text Highlighter' );

// Plugin version
define( 'SKTH_VERSION',		'1.0.0' );

// Plugin Root File
define( 'SKTH_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'SKTH_PLUGIN_BASE',	plugin_basename( SKTH_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'SKTH_PLUGIN_DIR',	plugin_dir_path( SKTH_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'SKTH_PLUGIN_URL',	plugin_dir_url( SKTH_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once SKTH_PLUGIN_DIR . 'core/class-text-highlighter.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Shyam
 * @since   1.0.0
 * @return  object|Text_Highlighter
 */
function SKTH() {
	return Text_Highlighter::instance();
}

SKTH();