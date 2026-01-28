<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SK_CFE_Checkout_Handler {

    public function __construct() {
        add_action( 'woocommerce_checkout_process', array( $this, 'validate_custom_fields' ) );
        add_action( 'wp_ajax_sk_cfe_upload_file', array( $this, 'ajax_upload_file' ) );
        add_action( 'wp_ajax_nopriv_sk_cfe_upload_file', array( $this, 'ajax_upload_file' ) );
    }

    public function save_custom_fields( $order_id, $data ) {
        $field_manager = new SK_CFE_Field_Manager();
        $custom_fields = $field_manager->get_custom_fields();

        foreach ( $custom_fields as $field_id => $field_data ) {
            $field_value = $field_manager->get_field_value( $field_id );

            if ( $field_data['type'] === 'file' && isset( $_FILES[$field_id] ) && $_FILES[$field_id]['error'] === UPLOAD_ERR_OK ) {
                $field_value = $this->handle_file_upload( $_FILES[$field_id], $order_id, $field_id );
            }

            if ( ! empty( $field_value ) ) {
                // Use WC data store for HPOS compatibility
                $order = wc_get_order( $order_id );
                if ( $order ) {
                    $order->update_meta_data( $field_id, $field_value );
                    $order->save();
                }
            }
        }
    }

    public function validate_custom_fields() {
        $field_manager = new SK_CFE_Field_Manager();
        $custom_fields = $field_manager->get_custom_fields();

        foreach ( $custom_fields as $field_id => $field_data ) {
            if ( $field_data['required'] === 'yes' ) {
                $field_value = $field_manager->get_field_value( $field_id );

                if ( $field_data['type'] === 'file' ) {
                    if ( ! isset( $_FILES[$field_id] ) || $_FILES[$field_id]['error'] !== UPLOAD_ERR_OK ) {
                        wc_add_notice( sprintf( __( '%s is a required field.', 'sk-checkout-field-editor' ), $field_data['label'] ), 'error' );
                    }
                } elseif ( empty( $field_value ) ) {
                    wc_add_notice( sprintf( __( '%s is a required field.', 'sk-checkout-field-editor' ), $field_data['label'] ), 'error' );
                }
            }

            // Validate email fields
            if ( $field_data['type'] === 'email' && ! empty( $field_value ) ) {
                if ( ! is_email( $field_value ) ) {
                    wc_add_notice( sprintf( __( '%s must be a valid email address.', 'sk-checkout-field-editor' ), $field_data['label'] ), 'error' );
                }
            }

            // Validate phone fields using latest WC validation
            if ( $field_data['type'] === 'tel' && ! empty( $field_value ) ) {
                if ( ! WC_Validation::is_phone( $field_value ) ) {
                    wc_add_notice( sprintf( __( '%s must be a valid phone number.', 'sk-checkout-field-editor' ), $field_data['label'] ), 'error' );
                }
            }

            // Validate number fields
            if ( $field_data['type'] === 'number' && ! empty( $field_value ) ) {
                if ( ! is_numeric( $field_value ) ) {
                    wc_add_notice( sprintf( __( '%s must be a valid number.', 'sk-checkout-field-editor' ), $field_data['label'] ), 'error' );
                }
            }

            // Validate postcode if field type is postcode
            if ( $field_data['type'] === 'postcode' && ! empty( $field_value ) ) {
                $country = isset( $_POST['billing_country'] ) ? $_POST['billing_country'] : ( isset( $_POST['shipping_country'] ) ? $_POST['shipping_country'] : WC()->customer->get_billing_country() );
                if ( ! WC_Validation::is_postcode( $field_value, $country ) ) {
                    wc_add_notice( sprintf( __( '%s is not a valid postcode.', 'sk-checkout-field-editor' ), $field_data['label'] ), 'error' );
                }
            }
        }
    }

    private function handle_file_upload( $file, $order_id, $field_id ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        $upload_overrides = array(
            'test_form' => false,
            'mimes' => array(
                'jpg|jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf',
                'doc|docx' => 'application/msword',
                'txt' => 'text/plain',
            ),
        );

        $uploaded_file = wp_handle_upload( $file, $upload_overrides );

        if ( isset( $uploaded_file['error'] ) ) {
            wc_add_notice( sprintf( __( 'File upload error: %s', 'sk-checkout-field-editor' ), $uploaded_file['error'] ), 'error' );
            return false;
        }

        if ( isset( $uploaded_file['file'] ) ) {
            $attachment = array(
                'post_mime_type' => $uploaded_file['type'],
                'post_title' => sanitize_file_name( $file['name'] ),
                'post_content' => '',
                'post_status' => 'inherit',
                'post_parent' => $order_id,
            );

            $attach_id = wp_insert_attachment( $attachment, $uploaded_file['file'], $order_id );
            
            if ( $attach_id ) {
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                
                return $attach_id;
            }
        }

        return false;
    }

    public function ajax_upload_file() {
        check_ajax_referer( 'sk_cfe_nonce', 'nonce' );

        if ( ! isset( $_FILES['file'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'sk-checkout-field-editor' ) ) );
        }

        $file = $_FILES['file'];
        $field_id = sanitize_text_field( $_POST['field_id'] );

        $upload_overrides = array(
            'test_form' => false,
            'mimes' => array(
                'jpg|jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf',
                'doc|docx' => 'application/msword',
                'txt' => 'text/plain',
            ),
        );

        $uploaded_file = wp_handle_upload( $file, $upload_overrides );

        if ( isset( $uploaded_file['error'] ) ) {
            wp_send_json_error( array( 'message' => $uploaded_file['error'] ) );
        }

        if ( isset( $uploaded_file['file'] ) ) {
            $attachment = array(
                'post_mime_type' => $uploaded_file['type'],
                'post_title' => sanitize_file_name( $file['name'] ),
                'post_content' => '',
                'post_status' => 'inherit',
            );

            $attach_id = wp_insert_attachment( $attachment, $uploaded_file['file'] );
            
            if ( $attach_id ) {
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                
                wp_send_json_success( array(
                    'attachment_id' => $attach_id,
                    'url' => wp_get_attachment_url( $attach_id ),
                    'filename' => get_the_title( $attach_id ),
                ) );
            }
        }

        wp_send_json_error( array( 'message' => __( 'Upload failed.', 'sk-checkout-field-editor' ) ) );
    }

    public function display_custom_fields_in_admin( $order ) {
        $field_manager = new SK_CFE_Field_Manager();
        $custom_fields = $field_manager->get_custom_fields();

        $has_custom_fields = false;

        foreach ( $custom_fields as $field_id => $field_data ) {
            if ( $field_data['show_in_order_details'] === 'yes' ) {
                $field_value = $order->get_meta( $field_id );
                
                if ( ! empty( $field_value ) ) {
                    if ( ! $has_custom_fields ) {
                        echo '<h3>' . __( 'Custom Fields', 'sk-checkout-field-editor' ) . '</h3>';
                        echo '<div class="sk-cfe-order-fields">';
                        $has_custom_fields = true;
                    }

                    echo '<p><strong>' . esc_html( $field_data['label'] ) . ':</strong> ';
                    
                    if ( $field_data['type'] === 'file' && is_numeric( $field_value ) ) {
                        $file_url = wp_get_attachment_url( $field_value );
                        $filename = get_the_title( $field_value );
                        echo '<a href="' . esc_url( $file_url ) . '" target="_blank">' . esc_html( $filename ) . '</a>';
                    } elseif ( $field_data['type'] === 'checkbox' ) {
                        echo $field_value === 'yes' ? __( 'Yes', 'sk-checkout-field-editor' ) : __( 'No', 'sk-checkout-field-editor' );
                    } elseif ( in_array( $field_data['type'], array( 'select', 'radio' ) ) && ! empty( $field_data['options'] ) ) {
                        // For select and radio fields, show the option text instead of the key
                        $options = $field_data['options'];
                        if ( isset( $options[$field_value] ) ) {
                            echo esc_html( $options[$field_value] );
                        } else {
                            echo esc_html( $field_value ); // Fallback to show the value if option not found
                        }
                    } else {
                        echo esc_html( $field_value );
                    }
                    
                    echo '</p>';
                }
            }
        }

        if ( $has_custom_fields ) {
            echo '</div>';
        }
    }

    public function add_custom_fields_to_email( $fields, $sent_to_admin, $order ) {
        $field_manager = new SK_CFE_Field_Manager();
        $custom_fields = $field_manager->get_custom_fields();

        foreach ( $custom_fields as $field_id => $field_data ) {
            if ( $field_data['show_in_email'] === 'yes' ) {
                $field_value = $order->get_meta( $field_id );
                
                if ( ! empty( $field_value ) ) {
                    $display_value = $field_value;
                    
                    if ( $field_data['type'] === 'file' && is_numeric( $field_value ) ) {
                        $file_url = wp_get_attachment_url( $field_value );
                        $filename = get_the_title( $field_value );
                        $display_value = '<a href="' . esc_url( $file_url ) . '">' . esc_html( $filename ) . '</a>';
                    } elseif ( $field_data['type'] === 'checkbox' ) {
                        $display_value = $field_value === 'yes' ? __( 'Yes', 'sk-checkout-field-editor' ) : __( 'No', 'sk-checkout-field-editor' );
                    } elseif ( in_array( $field_data['type'], array( 'select', 'radio' ) ) && ! empty( $field_data['options'] ) ) {
                        // For select and radio fields, show the option text instead of the key
                        $options = $field_data['options'];
                        if ( isset( $options[$field_value] ) ) {
                            $display_value = $options[$field_value];
                        } else {
                            $display_value = $field_value; // Fallback to show the value if option not found
                        }
                    }

                    $fields[$field_id] = array(
                        'label' => $field_data['label'],
                        'value' => $display_value,
                    );
                }
            }
        }

        return $fields;
    }
}