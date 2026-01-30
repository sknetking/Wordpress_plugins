<?php
/**
 * POS Core Functions
 */

if ( ! defined('ABSPATH') ) exit;

/*--------------------------------------------------------------
ACCESS CONTROL
--------------------------------------------------------------*/
function pos_user_can_access() {
	return current_user_can('manage_woocommerce') || current_user_can('shop_manager');
}

/*--------------------------------------------------------------
PRODUCT SEARCH WITH AUTOCOMPLETE
--------------------------------------------------------------*/
add_action('wp_ajax_pos_product_search', function () {
	// Verify nonce
	if (!wp_verify_nonce($_POST['nonce'], 'pos_nonce')) {
		wp_die('Security check failed');
	}
	
	$term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
	$products_per_page = get_option('pos_products_per_page', 20);
	
	// If no search term, get all products
	if (empty($term)) {
		$args = [
			'post_type' => 'product',
			'posts_per_page' => -1, // Get all products
			'post_status' => 'publish',
			'meta_query' => [
				[
					'key' => '_stock_status',
					'value' => 'instock',
					'compare' => '='
				]
			]
		];
	} else {
		// First try exact SKU match
		$args = [
			'post_type' => 'product',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => '_sku',
					'value' => $term,
					'compare' => 'LIKE'
				],
				[
					'key' => '_stock_status',
					'value' => 'instock',
					'compare' => '='
				]
			]
		];
		
		$q = new WP_Query($args);
		$data = [];
		
		// If no exact SKU match, try partial search
		if ($q->post_count === 0) {
			$args = [
				'post_type' => 'product',
				's' => $term,
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'meta_query' => [
					[
						'key' => '_stock_status',
						'value' => 'instock',
						'compare' => '='
					]
				]
			];
		}
	}
	
	$q = new WP_Query($args);
	$data = [];

	foreach ($q->posts as $p) {
		$product = wc_get_product($p->ID);
		if (!$product) continue;
		
		$stock_qty = $product->get_stock_quantity();
		$stock_status = $product->get_stock_status();
		
		// Get product thumbnail
		$image_id = $product->get_image_id();
		$image_url = wp_get_attachment_image_url($image_id, [100, 100]);
		if (!$image_url) {
			$image_url = wc_placeholder_img_src([100, 100]);
		}
		
		$data[] = [
			'id' => $product->get_id(),
			'name' => $product->get_name(),
			'sku' => $product->get_sku() ?: 'N/A',
			'price' => wc_get_price_to_display($product),
			'stock_qty' => $stock_qty,
			'stock_status' => $stock_status,
			'manage_stock' => $product->get_manage_stock(),
			'image' => $image_url,
			'label' => $product->get_name() . ' (' . ($product->get_sku() ?: 'N/A') . ')',
			'value' => $product->get_id()
		];
	}

	wp_send_json($data);
});

add_action('wp_ajax_nopriv_pos_product_search', function () {
	wp_send_json_error('Access denied');
});

/*--------------------------------------------------------------
CREATE ORDER
--------------------------------------------------------------*/
add_action('wp_ajax_pos_create_order', function () {
	// Verify nonce
	if (!wp_verify_nonce($_POST['nonce'], 'pos_nonce')) {
		wp_die('Security check failed');
	}

	if ( ! pos_user_can_access() ) wp_die();

	// Debug mode
	$debug_mode = get_option('pos_debug_mode', false);
	if ($debug_mode) {
		error_log('POS Order Creation Started');
		error_log('POST Data: ' . print_r($_POST, true));
		error_log('GST Enabled: ' . ($_POST['gst_enabled'] ?? 'not set'));
		error_log('GST Rate: ' . ($_POST['gst_rate'] ?? 'not set'));
		error_log('Discount: ' . ($_POST['discount'] ?? 'not set'));
	}

	try {
		// Stock validation
		foreach ($_POST['cart'] as $item) {
			$product = wc_get_product($item['id']);
			if ($product->get_manage_stock()) {
				$stock_qty = $product->get_stock_quantity();
				if ($stock_qty < (int)$item['qty']) {
					wp_send_json_error("Insufficient stock for {$product->get_name()}. Available: {$stock_qty}, Requested: {$item['qty']}");
				}
			}
		}

		if ($debug_mode) {
			error_log('Stock validation passed');
		}

		$order = wc_create_order();

		if ($debug_mode) {
			error_log('Order created: ' . $order->get_id());
		}

		$order->set_billing_first_name( sanitize_text_field($_POST['customer']) );
		$order->update_meta_data('_billing_whatsapp', sanitize_text_field($_POST['whatsapp']) );

		foreach ($_POST['cart'] as $item) {
			$order->add_product( wc_get_product($item['id']), (int)$item['qty'] );
		}

		if ($debug_mode) {
			error_log('Products added to order');
		}

		if (!empty($_POST['discount'])) {
			$discount_amount = floatval($_POST['discount']);
			if ($debug_mode) {
				error_log('Discount Processing Debug:');
				error_log('Raw discount value: ' . $_POST['discount']);
				error_log('Parsed discount amount: ₹' . $discount_amount);
			}
			
			if ($discount_amount > 0) {
				$fee = new WC_Order_Item_Fee();
				$fee->set_name('POS Discount');
				$fee->set_amount(-$discount_amount);
				$fee->set_tax_status('none'); // Discount should not be taxed
				$fee->set_total(-$discount_amount);
				$order->add_item($fee);
				
				if ($debug_mode) {
					error_log('Discount fee added: -₹' . $discount_amount);
				}
			}
		}

		// Add GST if enabled
		if (!empty($_POST['gst_enabled']) && $_POST['gst_enabled'] == 'true') {
			$gst_rate = floatval($_POST['gst_rate']);
			if ($gst_rate > 0) {
				$subtotal = 0;
				foreach ($_POST['cart'] as $item) {
					$product = wc_get_product($item['id']);
					$price = wc_get_price_to_display($product);
					$subtotal += $price * (int)$item['qty'];
				}
				
				$discount = floatval($_POST['discount']) ?: 0;
				$taxable_amount = max(0, $subtotal - $discount);
				$gst_amount = $taxable_amount * ($gst_rate / 100);
				
				if ($debug_mode) {
					error_log('GST Calculation Debug:');
					error_log('Cart subtotal: ₹' . $subtotal);
					error_log('Discount: ₹' . $discount);
					error_log('Taxable amount: ₹' . $taxable_amount);
					error_log('GST rate: ' . $gst_rate . '%');
					error_log('GST amount: ₹' . $gst_amount);
				}
				
				if ($gst_amount > 0) {
					$fee = new WC_Order_Item_Fee();
					$fee->set_name("GST ({$gst_rate}%)");
					$fee->set_amount($gst_amount);
					$fee->set_tax_status('taxable'); // GST should be taxable
					$fee->set_total($gst_amount);
					$order->add_item($fee);
					
					if ($debug_mode) {
						error_log('GST fee added: ₹' . $gst_amount . ' at ' . $gst_rate . '%');
					}
				}
			}
		}

		if ($debug_mode) {
			error_log('Fees and GST added');
		}

		$order->set_payment_method('cod');
		$order->set_payment_method_title('Cash');
		
		// Calculate totals to ensure fees are included
		$order->calculate_totals();
		
		$order->set_status('completed');
		$order->save();

		// Stock is automatically reduced by WooCommerce when order status is completed
			
		$invoice_url = pos_get_invoice_url($order);
		
		if ($debug_mode) {
			error_log('Invoice URL generated: ' . $invoice_url);
		}

		$wa = preg_replace('/\D/', '', $_POST['whatsapp']);
		
		// WhatsApp API integration
		$whatsapp_api_enabled = get_option('pos_whatsapp_api_enabled', false);
		$whatsapp_api_key = get_option('pos_whatsapp_api_key', '');
		$whatsapp_api_url = get_option('pos_whatsapp_api_url', '');
		
		$msg = "Invoice for Order #{$order->get_id()}\n{$invoice_url}";
		
		if ($whatsapp_api_enabled && !empty($whatsapp_api_key) && !empty($wa)) {
			// Use WhatsApp API
			$api_response = pos_send_whatsapp_api($wa, $msg, $whatsapp_api_key, $whatsapp_api_url);
			$wa_link = $api_response ? 'Message sent via API' : "https://wa.me/{$wa}?text=" . urlencode($msg);
		} else {
			// Fallback to manual WhatsApp link
			$wa_link = "https://wa.me/{$wa}?text=" . urlencode($msg);
		}

		if ($debug_mode) {
			error_log('WhatsApp link generated');
		}

		// Clear any output buffers that might have errors
		while (ob_get_level()) {
			ob_end_clean();
		}
		
		$response_data = [
			'order_id' => $order->get_id(),
			'invoice_url' => $invoice_url,
			'whatsapp_link' => $wa_link
		];

		if ($debug_mode) {
			error_log('Response data: ' . print_r($response_data, true));
		}
		
		wp_send_json_success($response_data);

	} catch (Exception $e) {
		// Log the error
		error_log('POS Order Creation Error: ' . $e->getMessage());
		error_log('Stack trace: ' . $e->getTraceAsString());
		
		// Clear any output buffers
		while (ob_get_level()) {
			ob_end_clean();
		}
		
		wp_send_json_error('Order creation failed: ' . $e->getMessage());
	} catch (Error $e) {
		// Log the error
		error_log('POS Order Creation Fatal Error: ' . $e->getMessage());
		error_log('Stack trace: ' . $e->getTraceAsString());
		
		// Clear any output buffers
		while (ob_get_level()) {
			ob_end_clean();
		}
		
		wp_send_json_error('System error occurred. Please check logs.');
	}
});

add_action('wp_ajax_nopriv_pos_create_order', function () {
	wp_send_json_error('Access denied');
});

/*--------------------------------------------------------------
WHATSAPP API FUNCTION
--------------------------------------------------------------*/
function pos_send_whatsapp_api($phone, $message, $api_key, $api_url) {
	if (empty($api_url) || empty($api_key)) return false;
	
	$data = [
		'phone' => $phone,
		'message' => $message,
		'apikey' => $api_key
	];
	
	$response = wp_remote_post($api_url, [
		'body' => $data,
		'timeout' => 30,
		'headers' => ['Content-Type' => 'application/json']
	]);
	
	if (is_wp_error($response)) return false;
	
	$body = wp_remote_retrieve_body($response);
	$result = json_decode($body, true);
	
	return isset($result['success']) && $result['success'] === true;
}

/*--------------------------------------------------------------
GET PDF INVOICE URL (WP OVERNIGHT PLUGIN)
--------------------------------------------------------------*/
function pos_get_invoice_url($order) {
	try {
		// Get fresh order instance to ensure we have the latest data
		$order = wc_get_order($order->get_id());
		$order_key = $order->get_order_key();

		// Construct the secure guest-access URL
		$invoice_url = site_url() . '/wcpdf/invoice/' . $order->get_id() . '/' . $order_key . '/pdf';
		
		return $invoice_url;
		
	} catch (Exception $e) {
		error_log('POS Invoice URL Error: ' . $e->getMessage());
		// Return admin URL as fallback
		return get_admin_url(null, 'post.php?post=' . $order->get_id() . '&action=edit');
	}
}