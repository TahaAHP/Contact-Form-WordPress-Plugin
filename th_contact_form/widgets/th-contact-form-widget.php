<?php
class ContactFormWidget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'contact_form';
    }

    public function get_title() {
        return 'Contact Form';
    }

    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Content',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'form_title',
            [
                'label' => 'Form Title',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Contact Us',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="custom-contact-form">
            <h2><?php echo esc_html($settings['form_title']); ?></h2>
            <form id="custom-contact-form" method="post">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <button type="submit">Send</button>
            </form>
            <div id="form-response"></div>
        </div>
        <?php
    }
}