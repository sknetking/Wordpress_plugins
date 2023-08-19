<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Text_Highlighter_Helpers
 *
 * This class contains repetitive functions that
 * are used globally within the plugin.
 *
 * @package		SKTH
 * @subpackage	Classes/Text_Highlighter_Helpers
 * @author		Shyam
 * @since		1.0.0
 */
class Text_Highlighter_Helpers{
	 public function __construct(){
		add_action('wp_ajax_highlighter_gatText',array($this,'response_show_selectedText'));
		add_action('wp_ajax_nopriv_highlighter_gatText',array($this,'response_show_selectedText'));

		add_action('wp_ajax_highlighter_save_text',array($this,'highlighter_ajax_handler'));
		add_action('wp_ajax_nopriv_highlighter_save_text',array($this,'highlighter_ajax_handler'));
		
		add_action('wp_ajax_remove_selection_action',array($this,'remove_selection'));
		add_action('wp_ajax_nopriv_remove_selection_action',array($this,'remove_selection'));

	}
	 public function response_show_selectedText(){
		global $wpdb;

		$table_name = $wpdb->prefix . 'highlighted_texts';
		// $results = $wpdb->get_results(
		// 	$wpdb->prepare("SELECT * FROM $table_name where post_id={$_POST['post_id']} and ip_address={$_POST['ip_address']}"),
		// 	ARRAY_A);
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM $table_name where post_id={$_POST['post_id']} and ip_address='".$_POST['ip_address']."'"),
			ARRAY_A);

		if (!empty($results)) {
			$data = array();
			foreach ($results as $row) {
				$row_data = array(
					'ht_id'=>$row['id'],
					'ip_address' => $row['ip_address'],
					'post_id'    => $row['post_id'],
					'selected_text' => $row['selected_text'],
				);
				// Push the row data into the main $data array
				$data[] = $row_data;
			}
			echo json_encode($data);
		}
		wp_die();
	 }

	 
	public  function highlighter_ajax_handler() {
		
		// Check if the request is an AJAX request
		if (wp_doing_ajax()) {
			// Sanitize and validate the incoming data
			$selected_text = sanitize_text_field($_POST['selected_text']);
			$post_id = intval($_POST['post_id']);
			$user_ip = sanitize_text_field($_POST['ip_address']);
	
			// You can add further validation or processing of data here
			//print_r($_POST);
			// Save the data in the database (you'll need to create a custom database table for this)
			// Example: 
			global $wpdb;
			$table_name = $wpdb->prefix . 'highlighted_texts';
			$data = array(
				'id'=>null,
				'ip_address'=>$user_ip,
			    'post_id' => $post_id,
				'selected_text' => $selected_text,
			   );
			   
				if($selected_text && $post_id && $user_ip != '' )
				{
						$wpdb->insert($table_name, $data);
				}
		

			/** Retrieve data from table   */
			$results = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM $table_name where post_id= '".$post_id."' AND ip_address='".$user_ip."'"),
				ARRAY_A);
			
			//	print_r($results);

			if (!empty($results)) {
				$data = array();
				foreach ($results as $row) {
					// Access individual columns using their names
					$id = $row['id'];
					$ip_address = $row['ip_address'];
					$post_id = $row['post_id'];
					$selected_text = $row['selected_text'];
			
					// Create an associative array for each row
					$row_data = array(
						'id' => $id,
						'ip_address' => $ip_address,
						'post_id' => $post_id,
						'selected_text' => $selected_text,
					);
					// Push the row data into the main $data array
					$data[] = $row_data;
				
				}
			
				// Send the data as JSON response
				echo json_encode($data);
			} else {
				// If there are no results, send an empty JSON response with success status
				wp_send_json_success(array('message'=>'data not found'));
			}
			// Send response back to JavaScript
			//wp_send_json_success('Text highlighted and saved successfully!');
			wp_die();
		}
	
		// If the request is not AJAX, you can handle it accordingly
	}
	/*  remove_selection from current page */
	public function remove_selection(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'highlighted_texts';
		$id = $_POST['sel_id'];
			$sql = "DELETE FROM $table_name WHERE id=$id";
			if ($wpdb->query($sql) === TRUE) {
			wp_send_json("Record deleted successfully");
			}
		//$wpdb->prepare("DELETE * FROM $table_name WHERE id=57");
		wp_die();
	}

	
}
