<?php
/*
Plugin Name: VISS-FORM
Plugin URI: https://viss-tunisie.com/VISS-FORM
Description: Un plugin personnalisé pour WordPress qui facilite la génération de contenu de votre site web.
Version: 1.0
Author: Manel Mbarek & Farah Abbes
Author URI: https://viss-tunisie.com
License: GPL2
*/

// Fonction pour inclure les styles et les scripts
function viss_form_enqueue_scripts() {
    wp_enqueue_style('viss-form-style', plugin_dir_url(__FILE__) . 'css/styles.css');
    wp_enqueue_style('viss-form-create-template', plugin_dir_url(__FILE__) . 'css/style_create_template.css');
    wp_enqueue_script('viss-form-script', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), null, true);
    wp_enqueue_script('viss-form-handler', plugin_dir_url(__FILE__) . 'js/formhandler.js', array('jquery'), null, true);
    wp_enqueue_script('viss-form-create-template', plugin_dir_url(__FILE__) . 'js/script_create_template.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'viss_form_enqueue_scripts');

// Inclure les fichiers PHP supplémentaires
$includes_files = array(
    'delete_template.php',
    'edit_template.php',
    'generated_content.php',
    'get_template_content.php',
    'get_templates.php',
    'prompt.php',
    'promptgenere.php',
    'save_template.php',
    'submit_form.php',
    'template_fields.php'
);

foreach ($includes_files as $file) {
    include_once(plugin_dir_path(__FILE__) . 'includes/' . $file);
}

// Fonction pour afficher le contenu avec un shortcode
function viss_form_display_content($atts) {
    $template = isset($atts['template']) ? $atts['template'] : 'index';
    ob_start();
    include(plugin_dir_path(__FILE__) . "templates/{$template}.html");
    return ob_get_clean();
}
add_shortcode('viss_form', 'viss_form_display_content');

// Fonction pour ajouter du contenu à la fin de chaque article
function viss_form_add_content_to_post($content) {
    if (is_single()) {
        ob_start();
        include(plugin_dir_path(__FILE__) . 'templates/index.html');
        $content .= ob_get_clean();
    }
    return $content;
}
add_filter('the_content', 'viss_form_add_content_to_post');