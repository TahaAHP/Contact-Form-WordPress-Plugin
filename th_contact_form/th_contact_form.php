<?php
/**
 * Plugin Name: Custom Contact Form
 * Description: A beautiful contact form with Elementor integration and admin dashboard.
 * Version: 1.0
 * Author: Taha Ahmadpour
 * Text Domain: custom-contact-form
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CustomContactForm {
    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('plugins_loaded', array($this, 'init'));
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_menu_item'));
        add_action('elementor/widgets/widgets_registered', array($this, 'register_elementor_widget'));
        add_action('wp_ajax_submit_contact_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_submit_contact_form', array($this, 'handle_form_submission'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_form_submissions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            message text NOT NULL,
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function add_menu_item() {
        add_menu_page(
            'Contact Form Submissions',
            'Contact Form',
            'manage_options',
            'contact-form-submissions',
            array($this, 'render_admin_page'),
            'dashicons-email',
            30
        );
    }

    public function register_elementor_widget() {
        require_once(plugin_dir_path(__FILE__) . 'widgets/contact-form-widget.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \ContactFormWidget());
    }

    public function handle_form_submission() {
        check_ajax_referer('custom_contact_form_nonce', 'nonce');

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $message = sanitize_textarea_field($_POST['message']);

        if (empty($name) || empty($email) || empty($message)) {
            wp_send_json_error('Please fill in all fields.');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_form_submissions';

        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'message' => $message,
            ),
            array('%s', '%s', '%s')
        );

        if ($result) {
            wp_send_json_success('Thank you for your message. We will get back to you soon!');
        } else {
            wp_send_json_error('There was an error saving your message. Please try again.');
        }
    }

    public function enqueue_scripts() {
        wp_enqueue_style('custom-contact-form', plugin_dir_url(__FILE__) . 'assets/css/style.css');
        wp_enqueue_script('custom-contact-form', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0', true);
        wp_localize_script('custom-contact-form', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('custom_contact_form_nonce')
        ));
    }

    public function render_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_form_submissions';
        $submissions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY submitted_at DESC");

        ?>
        <div class="wrap">
            <h1>Contact Form Submissions</h1>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?php echo esc_html($submission->id); ?></td>
                            <td><?php echo esc_html($submission->name); ?></td>
                            <td><?php echo esc_html($submission->email); ?></td>
                            <td><?php echo esc_html($submission->message); ?></td>
                            <td><?php echo esc_html($submission->submitted_at); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

new CustomContactForm();