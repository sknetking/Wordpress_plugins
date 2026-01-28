<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'default-fields';
?>

<div class="wrap sk-cfe-wrapper">
    <h1><?php _e( 'Checkout Field Editor', 'sk-checkout-field-editor' ); ?></h1>
    
    <div class="sk-cfe-notice">
        <p><?php _e( 'Manage your WooCommerce checkout fields with ease. Edit default fields or create custom ones.', 'sk-checkout-field-editor' ); ?></p>
    </div>

    <nav class="nav-tab-wrapper">
        <a href="?page=sk-cfe-settings&tab=default-fields" class="nav-tab <?php echo $active_tab === 'default-fields' ? 'nav-tab-active' : ''; ?>">
            <?php _e( 'Default Fields', 'sk-checkout-field-editor' ); ?>
        </a>
        <a href="?page=sk-cfe-settings&tab=custom-fields" class="nav-tab <?php echo $active_tab === 'custom-fields' ? 'nav-tab-active' : ''; ?>">
            <?php _e( 'Custom Fields', 'sk-checkout-field-editor' ); ?>
        </a>
    </nav>

    <div class="sk-cfe-content">
        <?php
        if ( $active_tab === 'default-fields' ) {
            require_once SK_CFE_PLUGIN_DIR . 'admin/tabs/default-fields.php';
        } elseif ( $active_tab === 'custom-fields' ) {
            require_once SK_CFE_PLUGIN_DIR . 'admin/tabs/custom-fields.php';
        }
        ?>
    </div>
</div>
