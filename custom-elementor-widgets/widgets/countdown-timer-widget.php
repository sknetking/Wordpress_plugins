<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Countdown_Timer_Widget extends Widget_Base {

    public function get_name() {
        return 'countdown_timer';
    }

    public function get_title() {
        return __( 'Countdown Timer', 'text-domain' );
    }

    public function get_icon() {
        return 'eicon-countdown';
    }

    public function get_categories() {
        return [ 'basic' ];
    }
  
    public function get_script_depends(): array {
		return [ 'countdown-timer', 'widget-script-2' ];
	}

    private function get_templates_list() {
        $templates = [];
        $saved_templates = \Elementor\Plugin::instance()->templates_manager->get_source( 'local' )->get_items();
    
        if ( ! empty( $saved_templates ) ) {
            foreach ( $saved_templates as $template ) {
                $templates[ $template['template_id'] ] = $template['title'];
            }
        }
    
        return $templates;
    }
    
    protected function register_controls() {
        // Add date and time control
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Settings', 'text-domain' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'target_date',
            [
                'label' => __( 'Target Date & Time', 'text-domain' ),
                'type' => Controls_Manager::DATE_TIME,
                'picker_options' => [
                    'enableTime' => true,
                ],
                'default' => date('Y-m-d H:i:s', strtotime('+1 day')),
            ]
        );

        $this->add_control(
            'message_on_complete',
            [
                'label' => __( 'Message on Completion', 'text-domain' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( "Time's up!", 'text-domain' ),
            ]
        );

        $this->add_control(
            'after_countdown_template',
            [
                'label' => __( 'After Countdown Template', 'text-domain' ),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_templates_list(),
                'default' => '',
                'description' => __( 'Select a saved template to display after the countdown ends.', 'text-domain' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $target_date = $settings['target_date'];
        $message_on_complete = $settings['message_on_complete'];
        $template_id = $settings['after_countdown_template'];
        ?>
        <div class="countdown-timer-widget" data-target-date="<?php echo esc_attr( $target_date ); ?>" data-complete-message="<?php echo esc_attr( $message_on_complete ); ?>">
            <span id="countdown-display"></span>
        </div>
        <div id="countdown-template" style="display: none;">
        <?php
        if ( $template_id ) {
            echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
        }
        ?>
        </div>
        <?php
    }
}
