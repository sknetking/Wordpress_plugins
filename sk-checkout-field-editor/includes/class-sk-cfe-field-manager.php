<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SK_CFE_Field_Manager {

    private $default_settings;
    private $custom_fields;

    public function __construct() {
        $this->default_settings = get_option( 'sk_cfe_default_fields_settings', array() );
        $this->custom_fields = get_option( 'sk_cfe_custom_fields', array() );
    }

    public function modify_checkout_fields( $fields ) {
        // Debug: Log original fields
        error_log('SK CFE: Original checkout fields: ' . print_r($fields, true));
        
        // Modify default WooCommerce fields
        $fields = $this->modify_default_fields( $fields );
        
        // Debug: Log fields after default modification
        error_log('SK CFE: Fields after default modification: ' . print_r($fields, true));
        
        // Add custom fields
        $fields = $this->add_custom_fields( $fields );
        
        // Debug: Log final fields
        error_log('SK CFE: Final checkout fields: ' . print_r($fields, true));
        
        return $fields;
    }

    private function modify_default_fields( $fields ) {
        // Debug: Log the settings being loaded
        error_log('SK CFE: Default settings loaded: ' . print_r($this->default_settings, true));
        
        foreach ( $this->default_settings as $section => $section_fields ) {
            if ( ! isset( $fields[$section] ) ) {
                continue;
            }

            // Apply modifications and collect fields with order info
            $modified_fields = array();
            
            foreach ( $fields[$section] as $field_key => $field_data ) {
                if ( ! isset( $section_fields[$field_key] ) ) {
                    // Field not in settings, keep original
                    $modified_fields[$field_key] = $field_data;
                    $modified_fields[$field_key]['priority'] = isset( $field_data['priority'] ) ? $field_data['priority'] : 10;
                    
                    // Debug: Log original field priority
                    error_log('SK CFE: Original field ' . $field_key . ' has priority ' . $modified_fields[$field_key]['priority']);
                    
                    continue;
                }

                $field_settings = $section_fields[$field_key];

                // Enable/disable field
                if ( isset( $field_settings['enabled'] ) && $field_settings['enabled'] === 'no' ) {
                    continue; // Skip disabled fields
                }

                // Update label
                if ( ! empty( $field_settings['label'] ) ) {
                    $fields[$section][$field_key]['label'] = $field_settings['label'];
                }

                // Update required status
                if ( isset( $field_settings['required'] ) ) {
                    $fields[$section][$field_key]['required'] = $field_settings['required'] === 'yes';
                }

                // Update order/priority
                $order = isset( $field_settings['order'] ) ? intval( $field_settings['order'] ) : 10;
                $fields[$section][$field_key]['priority'] = $order;
                
                // Debug: Log field priority setting
                error_log('SK CFE: Set priority for ' . $field_key . ' to ' . $order);
                
                $modified_fields[$field_key] = $fields[$section][$field_key];
            }

            // Sort fields by priority (order)
            uasort( $modified_fields, function( $a, $b ) {
                $priority_a = isset( $a['priority'] ) ? $a['priority'] : 999;
                $priority_b = isset( $b['priority'] ) ? $b['priority'] : 999;
                return $priority_a - $priority_b;
            });

            // Debug: Log the sorted fields for this section
            error_log('SK CFE: Sorted fields for section ' . $section . ': ' . print_r($modified_fields, true));

            // Replace the section fields with sorted fields
            $fields[$section] = $modified_fields;
        }

        return $fields;
    }

    private function add_custom_fields( $fields ) {
        // Debug: Log custom fields being added
        error_log('SK CFE: Adding custom fields: ' . print_r($this->custom_fields, true));
        
        foreach ( $this->custom_fields as $field_id => $field_data ) {
            $field_config = $this->build_field_config( $field_id, $field_data );
            
            if ( $field_data['position'] === 'additional' ) {
                // Handle fields after additional notes separately
                add_action( 'woocommerce_after_order_notes', function() use ( $field_config, $field_id ) {
                    $this->render_custom_field( $field_config, $field_id );
                } );
            } else {
                // Add to billing or shipping section
                if ( ! isset( $fields[$field_data['position']] ) ) {
                    $fields[$field_data['position']] = array();
                }
                
                // Add the custom field with its priority
                $fields[$field_data['position']][$field_id] = $field_config;
                
                // Debug: Log each custom field being added
                error_log('SK CFE: Added custom field ' . $field_id . ' to ' . $field_data['position'] . ' with priority ' . $field_config['priority']);
            }
        }

        // Re-sort ALL fields (default + custom) by priority for each section
        foreach ( $fields as $section => $section_fields ) {
            if ( in_array( $section, array( 'billing', 'shipping' ) ) ) {
                // Debug: Log before sorting
                error_log('SK CFE: Before sorting section ' . $section . ': ' . print_r($fields[$section], true));
                
                uasort( $fields[$section], function( $a, $b ) {
                    $priority_a = isset( $a['priority'] ) ? $a['priority'] : 999;
                    $priority_b = isset( $b['priority'] ) ? $b['priority'] : 999;
                    
                    // Debug: Log comparison
                    error_log('SK CFE: Comparing priorities - A: ' . $priority_a . ', B: ' . $priority_b . ', Result: ' . ($priority_a - $priority_b));
                    
                    return $priority_a - $priority_b;
                });
                
                // Debug: Log after sorting
                error_log('SK CFE: After sorting section ' . $section . ': ' . print_r($fields[$section], true));
            }
        }

        return $fields;
    }

    private function build_field_config( $field_id, $field_data ) {
        $config = array(
            'label' => $field_data['label'],
            'required' => $field_data['required'] === 'yes',
            'priority' => isset( $field_data['order'] ) ? intval( $field_data['order'] ) : 10,
            'custom_field' => true,
        );

        // Debug: Log custom field config
        error_log('SK CFE: Building config for custom field ' . $field_id . ' with priority ' . $config['priority']);

        // Add CSS classes
        $classes = array();
        if ( ! empty( $field_data['class'] ) ) {
            $classes[] = sanitize_html_class( $field_data['class'] );
        }
        if ( $field_data['type'] === 'file' ) {
            $classes[] = 'sk-cfe-file-field';
        }
        
        if ( ! empty( $classes ) ) {
            $config['class'] = $classes;
        }

        // Add placeholder
        if ( ! empty( $field_data['placeholder'] ) ) {
            $config['placeholder'] = $field_data['placeholder'];
        }

        // Add default value
        if ( ! empty( $field_data['default'] ) ) {
            $config['default'] = $field_data['default'];
        }

        // Handle different field types
        switch ( $field_data['type'] ) {
            case 'select':
                $config['type'] = 'select';
                $config['options'] = ! empty( $field_data['options'] ) ? $field_data['options'] : array();
                break;
            
            case 'radio':
                $config['type'] = 'radio';
                $config['options'] = ! empty( $field_data['options'] ) ? $field_data['options'] : array();
                break;
            
            case 'checkbox':
                $config['type'] = 'checkbox';
                break;
            
            case 'textarea':
                $config['type'] = 'textarea';
                break;
            
            case 'file':
                $config['type'] = 'file';
                break;
            
            case 'hidden':
                $config['type'] = 'hidden';
                break;
            
            case 'postcode':
                $config['type'] = 'text';
                $config['class'][] = 'postcode';
                break;
            
            default:
                $config['type'] = $field_data['type'];
                break;
        }

        return $config;
    }

    private function render_custom_field( $field_config, $field_id ) {
        $field_type = $field_config['type'];
        $field_name = $field_id;
        $field_label = $field_config['label'];
        $required = $field_config['required'] ? 'required' : '';
        $placeholder = isset( $field_config['placeholder'] ) ? $field_config['placeholder'] : '';
        $default = isset( $field_config['default'] ) ? $field_config['default'] : '';
        $classes = isset( $field_config['class'] ) ? implode( ' ', $field_config['class'] ) : '';

        // Field types that support placeholder attribute
        $placeholder_supported_types = array( 'text', 'email', 'tel', 'number', 'password', 'textarea', 'postcode' );
        
        $field_args = array(
            'type' => $field_type,
            'label' => $field_label,
            'required' => $field_config['required'],
            'default' => $default,
            'class' => array( $classes ),
            'options' => isset( $field_config['options'] ) ? $field_config['options'] : array(),
        );
        
        // Only add placeholder for supported field types
        if ( in_array( $field_type, $placeholder_supported_types ) && ! empty( $placeholder ) ) {
            $field_args['placeholder'] = $placeholder;
        }

        woocommerce_form_field( $field_name, $field_args, isset( $_POST[$field_name] ) ? wc_clean( $_POST[$field_name] ) : $default );
    }

    public function get_custom_fields() {
        return $this->custom_fields;
    }

    public function get_field_value( $field_id, $order_id = null ) {
        if ( $order_id ) {
            $order = wc_get_order( $order_id );
            return $order ? $order->get_meta( $field_id ) : '';
        }
        return isset( $_POST[$field_id] ) ? wc_clean( wp_unslash( $_POST[$field_id] ) ) : '';
    }

    public function is_custom_field( $field_id ) {
        return isset( $this->custom_fields[$field_id] );
    }

    public function get_field_settings( $field_id ) {
        if ( $this->is_custom_field( $field_id ) ) {
            return $this->custom_fields[$field_id];
        }
        return false;
    }
}
