<?php

class Autoposting_Admin_Page {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_menu() {
        add_menu_page(
            'Autoposting',
            'Autoposting',
            'manage_options',
            'autoposting-settings',
            [$this, 'settings_page']
        );
    }

    public function register_settings() {
        register_setting('autoposting_options', 'autoposting_topics');
        register_setting('autoposting_options', 'autoposting_api_key');
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Autoposting Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('autoposting_options'); ?>
                <?php do_settings_sections('autoposting_options'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Google trands topics</th>
                        <td>
                            <textarea name="autoposting_topics" rows="10" cols="50"><?php echo esc_textarea(get_option('autoposting_topics')); ?></textarea>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">API key for AI tool</th>
                        <td>
                            <input type="text" name="autoposting_api_key" value="<?php echo esc_attr(get_option('autoposting_api_key')); ?>" size="50" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}