<?php

/*
Plugin Name: Display Message
Description: A simple plugin that displays a message in the footer.
Version: 1.0
Author:
*/

global $wpdb;

// Name of tables
$table_user = $wpdb->prefix . 'user';
$table_didactic_program = $wpdb->prefix . 'didactic_program';
$table_didactic_unit = $wpdb->prefix . 'didactic_unit';
$table_learning_outcome = $wpdb->prefix . 'learning_outcome';
$table_activity = $wpdb->prefix . 'activity';
$table_sessions = $wpdb->prefix . 'sessions';
$table_evaluation_criterion = $wpdb->prefix . 'evaluation_criterion';
$table_content = $wpdb->prefix . 'content';

// Function to validate user data
function validate_user_data($name, $email)
{
    // Validate name
    if (empty($name) || strlen($name) > 100) {
        return 'El nombre es obligatorio y no puede exceder 100 caracteres.';
    }

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'El correo electrónico es obligatorio y debe ser válido.';
    }

    return true; // Return true if validation passes
}

// Function that runs when you activate the plugin
function display_message_activate()
{
    global $wpdb;

    // Define tables
    $charset_collate = $wpdb->get_charset_collate();

    // Create tables
    // Create a user table
    $sql_user = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}user (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE
    ) $charset_collate; 
    ";

    // Create a didactic program table
    $sql_didactic_program = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}didactic_program (
        dp_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        start_date DATE,
        end_date DATE,
        user_id INT,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}user(user_id) ON DELETE CASCADE
    ) $charset_collate;
    ";

    // Create a didactic unit table
    $sql_didactic_unit = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}didactic_unit (
        du_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        dp_id INT,
        hours INT NOT NULL,
        numer_unit INT NOT NULL,
        FOREIGN KEY (dp_id) REFERENCES {$wpdb->prefix}didactic_program(dp_id) ON DELETE CASCADE
    ) $charset_collate;
    ";

    // Create a learning outcome table
    $sql_learning_outcome = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}learning_outcome (
        lo_id INT AUTO_INCREMENT PRIMARY KEY,
        description TEXT NOT NULL,
        du_id INT,
        FOREIGN KEY (du_id) REFERENCES {$wpdb->prefix}didactic_unit(du_id) ON DELETE CASCADE
    ) $charset_collate;
    ";

    // Create an activity table
    $sql_activity = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}activity (
        ac_id INT AUTO_INCREMENT PRIMARY KEY,
        du_id INT,
        description TEXT NOT NULL,
        FOREIGN KEY (du_id) REFERENCES {$wpdb->prefix}didactic_unit(du_id) ON DELETE CASCADE
    ) $charset_collate;
    ";

    // Create a sessions table
    $sql_sessions = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}sessions (
        session_id INT AUTO_INCREMENT PRIMARY KEY,
        du_id INT,
        session_number INT NOT NULL,
        duration INT NOT NULL,
        description TEXT,
        FOREIGN KEY (du_id) REFERENCES {$wpdb->prefix}didactic_unit(du_id) ON DELETE CASCADE
    ) $charset_collate;
    ";

    // Create an evaluation criterion table
    $sql_evaluation_criterion = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}evaluation_criterion (
        ec_id INT AUTO_INCREMENT PRIMARY KEY,
        lo_id INT,
        description TEXT NOT NULL,
        FOREIGN KEY (lo_id) REFERENCES {$wpdb->prefix}learning_outcome(lo_id) ON DELETE CASCADE
    ) $charset_collate;
    ";

    // Create a content table
    $sql_content = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}content (
        co_id INT AUTO_INCREMENT PRIMARY KEY,
        ec_id INT,
        description TEXT NOT NULL,
        FOREIGN KEY (ec_id) REFERENCES {$wpdb->prefix}evaluation_criterion(ec_id) ON DELETE CASCADE
    ) $charset_collate;
    ";

    // Include the WordPress upgrade functions to enable database management functionalities
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Use dbDelta to create or update the database tables as defined in the SQL statement.
    dbDelta($sql_user);
    dbDelta($sql_didactic_program);
    dbDelta($sql_didactic_unit);
    dbDelta($sql_learning_outcome);
    dbDelta($sql_activity);
    dbDelta($sql_sessions);
    dbDelta($sql_evaluation_criterion);
    dbDelta($sql_content);

    // Validate user data
    $validation_result = validate_user_data($name, $email);
    if ($validation_result === true) {
        // Insert user if validation passes
        $wpdb->insert(
            $wpdb->prefix . 'user',
            [
                'name' => $name,
                'email' => $email,
            ]
        );
    } else {
        // Error handling: log the error
        error_log($validation_result);
    }

    // Create a default option in the database
    add_option('display_message_text', '¡Este es un mensaje de mi plugin!');
}

// Function that runs when you disable the plugin
function display_message_deactivate()
{
    global $wpdb;
    // Name of the tables you want to delete in the order of dependencies
    $tablesToDelete = [
        $wpdb->prefix . 'content',
        $wpdb->prefix . 'evaluation_criterion',
        $wpdb->prefix . 'learning_outcome',
        $wpdb->prefix . 'activity',
        $wpdb->prefix . 'sessions',
        $wpdb->prefix . 'didactic_unit',
        $wpdb->prefix . 'didactic_program',
        $wpdb->prefix . 'user'
    ];
    // Loop through each table and drop it
    foreach ($tablesToDelete as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
    // Clean up the database option
    delete_option('display_message_text');
}

// Register the activation and deactivation functions
register_activation_hook(__FILE__, 'display_message_activate');
register_deactivation_hook(__FILE__, 'display_message_deactivate');

// Register the menu in the admin panel
add_action('admin_menu', 'display_message_menu');

function display_message_menu()
{
    add_menu_page(
        'Display Message Settings', // Page title
        'Display Message', // Menu title
        'manage_options', // Required capability
        'display-message-settings', // Slug
        'display_message_settings_page' // Function to display the settings page
    );
}

// Create the settings page
function display_message_settings_page()
{
    ?>
    <div class="wrap">
        <h1><?php _e('Display Message Plugin Settings', 'text-domain'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('display_message_options_group');
            do_settings_sections('display-message-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register the options
add_action('admin_init', 'display_message_register_settings');

function display_message_register_settings()
{
    // Register a new setting
    register_setting('display_message_options_group', 'display_message_text');

    // Add the main section for settings
    add_settings_section(
        'display_message_main_section',
        'Main Settings',
        null,
        'display-message-settings'
    );

    // Add the input field for the message
    add_settings_field(
        'display_message_text',
        'Enter your message',
        'display_message_input_callback',
        'display-message-settings',
        'display_message_main_section'
    );
}

// Callback function for the input field
function display_message_input_callback()
{
    $value = get_option('display_message_text', '');
    echo '<input type="text" name="display_message_text" value="' . esc_attr($value) . '" />';
}

// Function to display the message in the footer
function display_message_footer()
{
    // Get the message from the database
    $message = get_option('display_message_text', '¡Este es un mensaje de mi plugin!');
    echo '<p style="text-align: center; color: gray;">' . esc_html($message) . '</p>';
}

// Add the function to the footer
add_action('wp_footer', 'display_message_footer');
?>