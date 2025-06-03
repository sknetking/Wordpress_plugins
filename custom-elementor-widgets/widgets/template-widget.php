<?php 
// elementor-custom-addon.php
class Custom_Button_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'custom_button';
    }

    public function get_title() {
        return __('Custom Button', 'elementor-custom-addon');
    }

    public function get_icon() {
        return 'eicon-button';
    }

    public function get_categories() {
        return ['basic'];
    }

    protected function _register_controls() {
        // Button Controls
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Button Settings', 'elementor-custom-addon'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'elementor-custom-addon'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Click Me', 'elementor-custom-addon'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <button class="custom-elementor-button">
            <?php echo esc_html($settings['button_text']); ?>
        </button>
        <?php
    }

    protected function _content_template() {
        ?>
        <button class="custom-elementor-button">
            {{{ settings.button_text }}}
        </button>
        <?php
    }
}