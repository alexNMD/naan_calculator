<?php
/**
 * Plugin Name: Calculateur de Tarif
 * Description: Un plugin pour calculer le tarif en fonction de différents paramètres.
 * Version: 6.6.6
 * Author: NAAN Fromage
 */

// Sécurité : Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

// Ajouter le shortcode
function calculateur_tarif_shortcode() {
    $fields = get_option('calculateur_tarif_fields', array());

    ob_start();
    ?>
    <div id="calculateur-tarif">
        <h1>Calculateur de Tarif d'Installation</h1>
        <?php foreach ($fields as $field) : ?>
        <div class="form-group">
            <label for="<?php echo esc_attr($field['id']); ?>">
                <?php echo esc_html($field['name']); ?>: 
                <output id="output<?php echo esc_attr($field['id']); ?>">0</output>
            </label>
            <?php if ($field['type'] == 'number') : ?>
                <input type="range" id="<?php echo esc_attr($field['id']); ?>" class="range-input" value="0" min="0" max="10">
            <?php elseif ($field['type'] == 'text') : ?>
                <input type="number" id="<?php echo esc_attr($field['id']); ?>" class="number-input" value="0" min="0">
            <?php elseif ($field['type'] == 'select') : ?>
                <select id="<?php echo esc_attr($field['id']); ?>" class="select-input">
                    <?php for ($i = 0; $i <= 10; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <div class="result" id="result">Le tarif total de l'installation est de <span id="totalPrice">0</span> €</div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('calculateur_tarif', 'calculateur_tarif_shortcode');

// Enqueue les scripts et styles
function calculateur_tarif_assets() {
    wp_enqueue_style('calculateur-tarif-css', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('calculateur-tarif-js', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), null, true);

    // Passer les options PHP aux scripts JavaScript
    $fields = get_option('calculateur_tarif_fields', array());
    if (!is_array($fields)) {
        $fields = array();
    }
    wp_localize_script('calculateur-tarif-js', 'tarifsOptions', array(
        'fields' => $fields
    ));
}
add_action('wp_enqueue_scripts', 'calculateur_tarif_assets');

// Créer la page de menu dans l'administration
function calculateur_tarif_menu() {
    add_menu_page(
        'Calculateur de Tarif',
        'Calculateur de Tarif',
        'manage_options',
        'calculateur-tarif',
        'calculateur_tarif_options_page',
        'dashicons-admin-generic',
        20
    );
}
add_action('admin_menu', 'calculateur_tarif_menu');

// Enregistrer les paramètres
function calculateur_tarif_settings() {
    register_setting('calculateurTarifSettingsGroup', 'calculateur_tarif_fields');
}
add_action('admin_init', 'calculateur_tarif_settings');

// Afficher la page d'options
function calculateur_tarif_options_page() {
    ?>
    <div class="wrap">
        <h1>Options du Calculateur de Tarif d'Installation</h1>
        <form method="post" action="options.php">
            <?php settings_fields('calculateurTarifSettingsGroup'); ?>
            <?php do_settings_sections('calculateurTarifSettingsGroup'); ?>
            <table class="form-table" id="fields-table">
                <?php
                $fields = get_option('calculateur_tarif_fields', array());
                if (!empty($fields)) {
                    foreach ($fields as $index => $field) {
                        ?>
                        <tr>
                            <th scope="row">Champ <?php echo $index ? $index > 0 : $index + 1; ?></th>
                            <td>
                                <input type="text" name="calculateur_tarif_fields[<?php echo $index; ?>][name]" value="<?php echo esc_attr($field['name']); ?>" placeholder="Nom du champ" />
                                <input type="number" name="calculateur_tarif_fields[<?php echo $index; ?>][price]" value="<?php echo esc_attr($field['price']); ?>" placeholder="Tarif unitaire" />
                                <select name="calculateur_tarif_fields[<?php echo $index; ?>][type]">
                                    <option value="number" <?php selected($field['type'], 'number'); ?>>Nombre</option>
                                    <option value="text" <?php selected($field['type'], 'text'); ?>>Texte</option>
                                    <option value="select" <?php selected($field['type'], 'select'); ?>>Sélection</option>
                                </select>
                                <input type="hidden" name="calculateur_tarif_fields[<?php echo $index; ?>][id]" value="<?php echo esc_attr($field['id']); ?>" />
                                <button type="button" class="button remove-field">Supprimer</button>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
            <button type="button" class="button add-field">Ajouter un champ</button>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let fieldIndex = <?php echo count($fields); ?>;
            const fieldsTable = document.getElementById('fields-table');
            const addFieldButton = document.querySelector('.add-field');

            addFieldButton.addEventListener('click', function() {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <th scope="row">Champ ${fieldIndex + 1}</th>
                    <td>
                        <input type="text" name="calculateur_tarif_fields[${fieldIndex}][name]" placeholder="Nom du champ" />
                        <input type="number" name="calculateur_tarif_fields[${fieldIndex}][price]" placeholder="Tarif unitaire" />
                        <select name="calculateur_tarif_fields[${fieldIndex}][type]">
                            <option value="number">Nombre</option>
                            <option value="text">Texte</option>
                            <option value="select">Sélection</option>
                        </select>
                        <input type="hidden" name="calculateur_tarif_fields[${fieldIndex}][id]" value="field_${fieldIndex}" />
                        <button type="button" class="button remove-field">Supprimer</button>
                    </td>
                `;
                fieldsTable.appendChild(row);
                fieldIndex++;
            });

            fieldsTable.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-field')) {
                    e.target.closest('tr').remove();
                }
            });
        });
    </script>
    <?php
}
