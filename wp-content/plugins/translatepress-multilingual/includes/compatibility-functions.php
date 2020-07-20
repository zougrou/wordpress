<?php

/** Compatibility functions with WP core and various themes and plugins*/

/**
 * Remove '?fl_builder' query param from edit translation url (when clicking the admin bar button to enter the translation Editor)
 *
 * Otherwise after publishing out of BB and clicking TP admin bar button, it’s still showing the BB interface
 *
 * @param $url
 *
 * @return bool
 */
function trp_beaver_builder_compatibility( $url ){

    $url = remove_query_arg('fl_builder', $url );

    return esc_url ($url);

}
add_filter( 'trp_edit_translation_url', 'trp_beaver_builder_compatibility' );


/**
 * Mb Strings missing PHP library error notice
 */
function trp_mbstrings_notification(){
    echo '<div class="notice notice-error"><p>' . wp_kses( __( '<strong>TranslatePress</strong> requires <strong><a href="http://php.net/manual/en/book.mbstring.php">Multibyte String PHP library</a></strong>. Please contact your server administrator to install it on your server.','translatepress-multilingual' ), [ 'a' => [ 'href' => [] ], 'strong' => [] ] ) . '</p></div>';
}

function trp_missing_mbstrings_library( $allow_to_run ){
    if ( ! extension_loaded('mbstring') ) {
        add_action( 'admin_menu', 'trp_mbstrings_notification' );
        return false;
    }
    return $allow_to_run;
}
add_filter( 'trp_allow_tp_to_run', 'trp_missing_mbstrings_library' );

/**
 * Don't have html inside menu title tags. Some themes just put in the title the content of the link without striping HTML
 */
add_filter( 'nav_menu_link_attributes', 'trp_remove_html_from_menu_title', 10, 3);
function trp_remove_html_from_menu_title( $atts, $item, $args ){
    $atts['title'] = wp_strip_all_tags($atts['title']);
    return $atts;
}

/**
 * Rework wp_trim_words so we can trim Chinese, Japanese and Thai words since they are based on characters as words.
 *
 * @since 1.3.0
 *
 * @param string $text      Text to trim.
 * @param int    $num_words Number of words. Default 55.
 * @param string $more      Optional. What to append if $text needs to be trimmed. Default '&hellip;'.
 * @return string Trimmed text.
 */
function trp_wp_trim_words( $text, $num_words = 55, $more = null, $original_text ) {
    if ( null === $more ) {
        $more = __( '&hellip;' );
    }
    // what we receive is the short text in the filter
    $text = $original_text;
    $text = wp_strip_all_tags( $text );

    $trp = TRP_Translate_Press::get_trp_instance();
    $trp_settings = $trp->get_component( 'settings' );
    $settings = $trp_settings->get_settings();

    $default_language= $settings["default-language"];

    $char_is_word = false;
    foreach (array('ch', 'ja', 'tw') as $lang){
        if (strpos($default_language, $lang) !== false){
            $char_is_word = true;
        }
    }

    if ( $char_is_word && preg_match( '/^utf\-?8$/i', get_option( 'blog_charset' ) ) ) {
        $text = trim( preg_replace( "/[\n\r\t ]+/", ' ', $text ), ' ' );
        preg_match_all( '/./u', $text, $words_array );
        $words_array = array_slice( $words_array[0], 0, $num_words + 1 );
        $sep = '';
    } else {
        $words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY );
        $sep = ' ';
    }

    if ( count( $words_array ) > $num_words ) {
        array_pop( $words_array );
        $text = implode( $sep, $words_array );
        $text = $text . $more;
    } else {
        $text = implode( $sep, $words_array );
    }

    return $text;
}
add_filter('wp_trim_words', 'trp_wp_trim_words', 100, 4);


/**
 * Use home_url in the https://www.peepso.com/ ajax front-end url so strings come back translated.
 *
 * @since 1.3.1
 *
 * @param array $data   Peepso data
 * @return array
 */
add_filter( 'peepso_data', 'trp_use_home_url_in_peepso_ajax' );
function trp_use_home_url_in_peepso_ajax( $data ){
    if ( is_array( $data ) && isset( $data['ajaxurl_legacy'] ) ){
        $data['ajaxurl_legacy'] = home_url( '/peepsoajax/' );
    }
    return $data;
}

/**
 * Compatibility with Peepso urls having extra / due their link builder not considering home urls having trailing slashes
 */
add_filter('peepso_get_page', 'trp_remove_peepso_double_slash', 10, 2);
function trp_remove_peepso_double_slash( $page, $name){

    // avoid accidentally replacing // from http://
    $page = str_replace('http://', 'http:/', $page );
    $page = str_replace('https://', 'https:/', $page );

    $page = str_replace('//', '/', $page );

    // place it back
    $page = str_replace('https:/', 'https://', $page );
    $page = str_replace('http:/', 'http://', $page );

    return $page;
};

/**
 * Filter ginger_iframe_banner and ginger_text_banner to use shortcodes so our conditional lang shortcode works.
 *
 * @since 1.3.1
 *
 * @param string $content
 * @return string
 */

add_filter('ginger_iframe_banner', 'trp_do_shortcode', 999 );
add_filter('ginger_text_banner', 'trp_do_shortcode', 999 );
function trp_do_shortcode($content){
    return do_shortcode(stripcslashes($content));
}


/**
 * Compatibility with WooCommerce PDF Invoices & Packing Slips
 * https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/
 *
 * @since 1.4.3
 *
 */
// fix attachment name in email
add_filter( 'wpo_wcpdf_filename', 'trp_woo_pdf_invoices_and_packing_slips_compatibility' );

// fix #trpgettext inside invoice pdf
add_filter( 'wpo_wcpdf_get_html', 'trp_woo_pdf_invoices_and_packing_slips_compatibility');
function trp_woo_pdf_invoices_and_packing_slips_compatibility($title){
    if ( class_exists( 'TRP_Translation_Manager' ) ) {
        return 	TRP_Translation_Manager::strip_gettext_tags($title);
    }
}

// fix font of pdf breaking because of str_get_html() call inside translate_page()
add_filter( 'trp_stop_translating_page', 'trp_woo_pdf_invoices_and_packing_slips_compatibility_dont_translate_pdf', 10, 2 );
function trp_woo_pdf_invoices_and_packing_slips_compatibility_dont_translate_pdf( $bool, $output ){
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'generate_wpo_wcpdf' ) {
        return true;
    }
    return $bool;
}


/**
 * Compatibility with WooCommerce order notes
 *
 * When a new order is placed in secondary languages, in admin area WooCommerce->Orders->Edit Order, the right sidebar contains Order notes which can contain #trpst tags.
 *
 * @since 1.4.3
 */

// old orders
add_filter( 'woocommerce_get_order_note', 'trp_woo_notes_strip_trpst' );
// new orders
add_filter( 'woocommerce_new_order_note_data', 'trp_woo_notes_strip_trpst' );
function trp_woo_notes_strip_trpst( $note_array ){
    foreach ( $note_array as $item => $value ){
        $note_array[$item] = TRP_Translation_Manager::strip_gettext_tags( $value );
    }
    return $note_array;
}

/*
 * Compatibility with WooCommerce back-end display order shipping taxes
 */
add_filter('woocommerce_order_item_display_meta_key','trp_woo_data_strip_trpst');
add_filter('woocommerce_order_item_get_method_title','trp_woo_data_strip_trpst');
function trp_woo_data_strip_trpst( $data ){
    return TRP_Translation_Manager::strip_gettext_tags( $data );
}

/**
 * Compatibility with WooCommerce country list on checkout.
 *
 * Skip detection by translate-dom-changes of the list of countries
 *
 */
add_filter( 'trp_skip_selectors_from_dynamic_translation', 'trp_woo_skip_dynamic_translation' );
function trp_woo_skip_dynamic_translation( $skip_selectors ){
    $add_skip_selectors = array( '#select2-billing_country-results', '#select2-shipping_country-results' );
    return array_merge( $skip_selectors, $add_skip_selectors );
}


/**
 * Compatibility with WooCommerce product variation.
 *
 * Add span tag to woocommerce product variation name.
 *
 * Product variation name keep changes, but the prefix is the same. Wrap the prefix to allow translating that part separately.
 */
add_filter( 'woocommerce_product_variation_title', 'trp_woo_wrap_variation', 8, 4);
function trp_woo_wrap_variation($name, $product, $title_base, $title_suffix){
    $separator  = '<span> - </span>';
    return $title_suffix ? $title_base . $separator . $title_suffix : $title_base;
}


/**
 * Compatibility with Query Monitor
 *
 * Remove their HTML and reappend it after translate_page function finishes
 */
add_filter('trp_before_translate_content', 'trp_qm_strip_query_monitor_html', 10, 1 );
function trp_qm_strip_query_monitor_html( $output ) {

    $query_monitor = apply_filters( 'trp_query_monitor_begining_string', '<!-- Begin Query Monitor output -->' );
    $pos = strpos( $output, $query_monitor );

    if ( $pos !== false ){
        global $trp_query_monitor_string;
        $trp_query_monitor_string = substr( $output, $pos );
        $output = substr( $output, 0, $pos );

    }

    return $output;
}

add_filter( 'trp_translated_html', 'trp_qm_reappend_query_monitor_html', 10, 1 );
function trp_qm_reappend_query_monitor_html( $final_html ){
    global $trp_query_monitor_string;

    if ( isset( $trp_query_monitor_string ) && !empty( $trp_query_monitor_string ) ){
        $final_html .= $trp_query_monitor_string;
    }

    return $final_html;
}

// trpgettext tags don't get escaped because they add <small> tags through a regex.
add_filter( 'qm/output/title', 'trp_qm_strip_gettext', 100);
function trp_qm_strip_gettext( $data ){
    if ( is_array( $data ) ) {
        foreach( $data as $key => $value ){
            $data[$key] = trp_qm_strip_gettext($value);
        }
    }else {
        // remove small tags
        $data = preg_replace('(<(\/)?small>)', '', $data);
        // strip gettext (not needed, they are just numbers shown in admin bar anyway)
        $data = TRP_Translation_Manager::strip_gettext_tags( $data );
        // add small tags back the same way they do it in the filter 'qm/output/title'
        $data = preg_replace( '#\s?([^0-9,\.]+)#', '<small>$1</small>', $data );
    }
    return $data;
}

/**
 * Compatibility with SeedProd Coming Soon
 *
 * Manually include the scripts and styles if do_action('enqueue_scripts') is not called
 */
add_filter( 'trp_translated_html', 'trp_force_include_scripts', 10, 4 );
function trp_force_include_scripts( $final_html, $TRP_LANGUAGE, $language_code, $preview_mode ){
    if ( $preview_mode ){
        $trp = TRP_Translate_Press::get_trp_instance();
        $translation_render = $trp->get_component( 'translation_render' );
        $trp_data = $translation_render->get_trp_data();

        $scripts_and_styles = apply_filters('trp_editor_missing_scripts_and_styles', array(
            'jquery'                        => "<script type='text/javascript' src='" . includes_url( '/js/jquery/jquery.js' ) . "'></script>",
            'trp-iframe-preview-script.js'  => "<script type='text/javascript' src='" . TRP_PLUGIN_URL . "assets/js/trp-iframe-preview-script.js'></script>",
            'trp-translate-dom-changes.js'  => "<script>trp_data = '" . addslashes(json_encode($trp_data) ) . "'; trp_data = JSON.parse(trp_data);</script><script type='text/javascript' src='" . TRP_PLUGIN_URL . "assets/js/trp-translate-dom-changes.js'></script>",
            'trp-preview-iframe-style-css'  => "<link rel='stylesheet' id='trp-preview-iframe-style-css'  href='" . TRP_PLUGIN_URL . "assets/css/trp-preview-iframe-style.css' type='text/css' media='all' />",
            'dashicons'                     => "<link rel='stylesheet' id='dashicons-css'  href='" . includes_url( '/css/dashicons.min.css' ) . "' type='text/css' media='all' />"
        ));

        $missing_script = '';
        foreach($scripts_and_styles as $key => $value ){
            if ( strpos( $final_html, $key ) === false ){
                $missing_script .= $value;
            }
        }

        if ( $missing_script !== '' ){
            $html = TranslatePress\str_get_html( $final_html, true, true, TRP_DEFAULT_TARGET_CHARSET, false, TRP_DEFAULT_BR_TEXT, TRP_DEFAULT_SPAN_TEXT );
            if ( $html === false ) {
                return $final_html;
            }

            $body = $html->find( 'body', 0 );
            if ( $body ) {
                $body->innertext = $body->innertext . $missing_script;
            }

            $final_html = $html->save();
        }
    }
    return $final_html;
}

/*
 * Compatibility with plugins sending Gettext strings in requests such as Cartflows
 *
 * Strip gettext wrappings from the requests made from http->post()
 */
// Strip of gettext wrappings all the values of the body request array
add_filter( 'http_request_args', 'trp_strip_trpst_from_requests', 10, 2 );
function trp_strip_trpst_from_requests($args, $url){
    if( is_array( $args['body'] ) ) {
        array_walk_recursive( $args['body'], 'trp_array_walk_recursive_strip_gettext_tags' );
    }else{
        $args['body'] = TRP_Translation_Manager::strip_gettext_tags( $args['body'] );
    }
    return $args;
}
function trp_array_walk_recursive_strip_gettext_tags( &$value ){
    $value = TRP_Translation_Manager::strip_gettext_tags( $value );
}

// Strip of gettext wrappings the customer_name and customer_email keys. Found in WC Stripe and Cartflows
add_filter( 'wc_stripe_payment_metadata', 'trp_strip_request_metadata_keys' );
function trp_strip_request_metadata_keys( $metadata ){
    foreach( $metadata as $key => $value ) {
        $stripped_key = TRP_Translation_Manager::strip_gettext_tags( $key );
        if ( $stripped_key != $key ) {
            $metadata[ $stripped_key ] = $value;
            unset( $metadata[ $key ] );
        }
    }
    return $metadata;
}

/**
 * Compatibility with NextGEN Gallery
 *
 * They start an output buffer at init -1 (before ours at init 0). They print footer scripts after we run translate_page,
 * resulting in outputting scripts that won't be stripped of trpst trp-gettext wrappings.
 * This includes WooCommerce Checkout scripts, resulting in trpst wrappings around form fields like Street Address.
 * Another issue is that translation editor is a blank page.
 *
 * We cannot move their hook to priority 1 because we do not have access to the object that gets hooked is not retrievable so we can't call remove_filter()
 * Also we cannot simply disable ngg using run_ngg_resource_manager hook because we would be disabling features of their plugin.
 *
 * So the only solution that works is to move our hook to -2.
 */
add_filter( 'trp_start_output_buffer_priority', 'trp_nextgen_compatibility' );
function trp_nextgen_compatibility( $priority ){
    if ( class_exists( 'C_Photocrati_Resource_Manager' ) ) {
        return '-2';
    }
    return $priority;
}

/**
 * Compatibility with NextGEN Gallery
 *
 * This plugin is adding wp_footer forcefully in a shutdown hook and appends it to "</body>" which bring up admin bar in translation editor.
 *
 * This filter prevents ngg from hooking the filters to alter the html.
 */
add_filter( 'run_ngg_resource_manager', 'trp_nextgen_disable_nextgen_in_translation_editor');
function trp_nextgen_disable_nextgen_in_translation_editor( $bool ){
    if ( isset( $_REQUEST['trp-edit-translation'] ) && esc_attr( $_REQUEST['trp-edit-translation'] ) === 'true' ) {
        return false;
    }
    return $bool;
}

/**
 * Compatibility with WooCommerce added to cart message
 *
 * Makes sure title of product is translated.
 *
 * The title of product is added through sprintf %s of a Gettext.
 *
 */
add_filter( 'the_title', 'trp_woo_translate_product_title_added_to_cart', 10, 2 );
function trp_woo_translate_product_title_added_to_cart( ...$args ){
    // fix themes that don't implement the_title filter correctly. Works on PHP 5.6 >.
    // Implemented this because users we getting this error frequently.
    if( isset($args[0])){
        $title = $args[0];
    } else {
        $title = '';
    }


    if( class_exists( 'WooCommerce' ) ){
        if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
            $callstack_functions = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);//set a limit if it is supported to improve performance
        }
        else{
            $callstack_functions = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        $list_of_functions = apply_filters( 'trp_woo_translate_title_before_translate_page', array( 'wc_add_to_cart_message' ) );
        if( !empty( $callstack_functions ) ) {
            foreach ( $callstack_functions as $callstack_function ) {
                if ( in_array( $callstack_function['function'], $list_of_functions ) ) {
                    $trp = TRP_Translate_Press::get_trp_instance();
                    $translation_render = $trp->get_component( 'translation_render' );
                    $title = $translation_render->translate_page($title);
                    break;
                }
            }
        }
    }
    return $title;
}
/**
 * Compatibility with WooTour plugin
 *
 * They replace spaces (" ") with \u0020, after we apply #trpst and because we don't strip them it breaks html
 */
add_action('init', 'trp_wootour_add_gettext_filter');
function trp_wootour_add_gettext_filter(){
    if ( class_exists( 'WooTour_Booking' ) ){
        add_filter('gettext', 'trp_wootour_exclude_gettext_strings', 1000, 3 );
    }
}
function trp_wootour_exclude_gettext_strings($translation, $text, $domain){
    if ( $domain == 'woo-tour' ){
        if ( in_array( $text, array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ) ) ){
            return TRP_Translation_Manager::strip_gettext_tags( $translation );
        }
    }
    return $translation;
}

/**
 * Compatibility with WooCommerce cart product name translation
 * For products with the character - in the product name.
 *
 * There is a difference between the rendered – and –. Two different characters.
 * Somehow in the cart is the minus one, in the shop listing is the longer separator.
 * Make the cart contain the same type of character which is obtained using get_the_title.
 */
add_filter( 'woocommerce_cart_item_name', 'trp_woo_cart_item_name', 8, 3 );
function trp_woo_cart_item_name( $product_name, $cart_item, $cart_item_key ){
    if ( isset( $cart_item['product_id'] ) ){
        $title = get_the_title( $cart_item['product_id'] );
        if ( !empty( $title )){
            if ( strpos( $product_name, '</a>' ) ) {
                preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $product_name, $matches);
                $product_name = sprintf( '<a href="%s">%s</a>', esc_url( $matches[2][0] ), $title );
            }
        }
    }
    return $product_name;
}

/**
 * Compatibility with WooCommerce PDF Invoices & Packing Slips
 *
 * Translate product name and variation (meta) in pdf invoices.
 */
add_filter( 'wpo_wcpdf_order_item_data', 'trp_woo_wcpdf_translate_product_name', 10, 3 );
function trp_woo_wcpdf_translate_product_name( $data, $order, $type ){
    if ( isset( $data['name'] ) ) {
        $trp = TRP_Translate_Press::get_trp_instance();
        $translation_render = $trp->get_component('translation_render');
        remove_filter( 'trp_stop_translating_page', 'trp_woo_pdf_invoices_and_packing_slips_compatibility_dont_translate_pdf', 10 );
        $data['name'] = $translation_render->translate_page($data['name']);
        if ( isset( $data['meta'] ) ) {
            $data['meta'] = $translation_render->translate_page($data['meta']);
        }
        add_filter( 'trp_stop_translating_page', 'trp_woo_pdf_invoices_and_packing_slips_compatibility_dont_translate_pdf', 10, 2 );
    }
    return $data;
}

/**
 * Compatibility with WooCommerce Checkout Add-Ons plugin
 *
 * Exclude name of "paid add-on" item from being run through gettext.
 *
 * No other filters were found. Advanced settings strip meta did not work.
 * It's being added through WC->add_fee and inserted directly in db in custom table.
 */
add_action( 'woocommerce_cart_calculate_fees', 'trp_woo_checkout_add_ons_filter_trpstr', 10, 2);
function trp_woo_checkout_add_ons_filter_trpstr(){
    if ( class_exists('WC_Checkout_Add_Ons_Frontend') ) {
        add_filter('trp_skip_gettext_processing', 'trp_woo_checkout_exclude_strings', 1000, 4);
    }
}

function trp_woo_checkout_exclude_strings( $return, $translation, $text, $domain) {
    if ( $domain === 'woocommerce-checkout-add-ons' ) {
        $add_ons = wc_checkout_add_ons()->get_add_ons();
        foreach ($add_ons as $add_on) {
            if ( $add_on->name === $text)
                return true;
        }
    }
    return $return;
}

/**
 * Compatibility with WooCommerce Fondy Payment gateway
 */
add_action('init', 'trp_woo_fondy_payment_gateway_add_gettext_filter');
function trp_woo_fondy_payment_gateway_add_gettext_filter(){
    if ( class_exists( 'WC_fondy' ) ){
        add_filter('gettext', 'trp_woo_fondy_payment_gateway_exclude_gettext_strings', 1000, 3 );
    }
}

function trp_woo_fondy_payment_gateway_exclude_gettext_strings($translation, $text, $domain){
    if ( $domain == 'fondy-woocommerce-payment-gateway' && $text == 'Order: ' ){
        return TRP_Translation_Manager::strip_gettext_tags( $translation );
    }
    return $translation;
}


/**
 * Compatibility with Elementor Popups Links
 *
 * The url is urlencoded so we add the language to it but we shouldn't.
 *
 */
add_filter('trp_skip_url_for_language', 'trp_skip_elementor_popup_action_from_url_converter', 10, 2);
function trp_skip_elementor_popup_action_from_url_converter($value, $url){
	if(strpos($url, '%23elementor-action') !== false){
		return true;
	}
	return $value;
}

/**
 * Strip gettext wrapping from get_the_date function parameter $d
 */
add_filter('get_the_date','trp_strip_gettext_from_get_the_date', 1, 3);
function trp_strip_gettext_from_get_the_date($the_date, $d = NULL, $post = NULL){
	if ( $d === NULL || $post === NULL ){
		return $the_date;
	}

    $d = TRP_Translation_Manager::strip_gettext_tags( $d );
    $post = get_post( $post );

    if ( ! $post ) {
        return false;
    }

    if ( '' == $d ) {
        $the_date = get_post_time( get_option( 'date_format' ), false, $post, true );
    } else {
        $the_date = get_post_time( $d, false, $post, true );
    }

    return $the_date;
}


/**
 * Compatibility with Affiliate Theme
 * It's adding parameters found in the filter forms automatically, braking the query.
 * TranslatePress adds the trp-form-language for other reasons. So we need to remove it in this case.
 * https://affiliatetheme.io
 *
 */
add_filter('at_set_product_filter_query', 'trp_remove_lang_param_from_query');
function trp_remove_lang_param_from_query($args){

	if ( isset( $args['meta_query'] ) && is_array( $args['meta_query']) ){
		foreach($args['meta_query'] as $key => $value){
			if ($value['key'] == 'trp-form-language'){
				unset( $args['meta_query'][$key] );
			}
		}
		$args['meta_query'] = array_values($args['meta_query']);
	}

	return $args;
}


/**
 * Set user prefered language to the language he was present on new user creation.
 * Only set it if an existing locale isn't set already, in case the registration comes from a form that sets the locale manually.
 *
 */
add_action( 'user_register', 'trp_add_user_prefered_language', 10 );
function trp_add_user_prefered_language($user_id) {
	global $TRP_LANGUAGE;
	if ( ! empty( $TRP_LANGUAGE ) ) {
		$user_locale = get_user_meta( $user_id, 'locale', true );
		if ( empty( $user_locale ) ) {
			update_user_meta( $user_id, 'locale', $TRP_LANGUAGE );
		}
	}
}

/*
 * Dflip Compatibility
 * With Secondary Language First, it deferes jquery and scripts don't load on the Elementor Editor.
 * Not sure exactly what's causing. I assume it's because Elementor loads with Ajax certain elements and that comes back broken somehow.
 */
add_action('wp_enqueue_scripts', 'trp_remove_dflip_defer_script', 9999);
function trp_remove_dflip_defer_script(){
	if(class_exists('DFlip')){
		$dflip_instance = DFlip::get_instance();
		remove_filter( 'script_loader_tag', array( $dflip_instance, 'add_defer_attribute' ), 10, 2 );
	}
}

/**
 * Ignore WooCommerce display_name gettext
 * _x( '%1$s %2$s', 'display name', 'woocommerce' ) || wordpress\wp-content\plugins\woocommerce\includes\class-wc-customer.php
 * _x( '%1$s %2$s', 'Display name based on first name and last name')   || wordpress\wp-includes\user.php
 * This will insert trpstr strings in the database. So just ignore it.
 *
 */
add_filter('trp_skip_gettext_processing', 'trp_exclude_woo_display_name_gettext', 2000, 4 );
function trp_exclude_woo_display_name_gettext ( $return, $translation, $text, $domain ){
	if($text == '%1$s %2$s' && $domain == 'woocommerce'){
		return true;
	}

	if($text == '%1$s %2$s' && $domain == 'default'){
		return true;
	}

	return $return;
}

/** Compatibility with superfly menu plugin.
 *
 *  Moving their script later so that dynamic translation detects their strings.
 */
add_action('wp_head','trp_superfly_change_menu_loading_hook', 5);
function trp_superfly_change_menu_loading_hook(){
    if ( remove_action ('wp_head', 'sf_dynamic') ){
        add_action ('wp_print_footer_scripts', 'sf_dynamic', 20);
    }
}

/**
 * Compatibility with Yoast SEO Canonical URL and Opengraph URL
 * Yoast places the canonical wrongly and it's not processed correctly.
 */
add_filter( 'wpseo_canonical', 'trp_wpseo_canonical_compat', 99999, 2);
function trp_wpseo_canonical_compat( $canonical, $presentation_class = null ){
	global $TRP_LANGUAGE;
	$trp = TRP_Translate_Press::get_trp_instance();
	$url_converter = $trp->get_component( 'url_converter' );
	$canonical = $url_converter->get_url_for_language($TRP_LANGUAGE, $canonical, '');

	return $canonical;
};

add_filter( 'wpseo_opengraph_url', 'trp_opengraph_url', 99999 );
function trp_opengraph_url( $url ) {
	global $TRP_LANGUAGE;
	$trp = TRP_Translate_Press::get_trp_instance();
	$url_converter = $trp->get_component( 'url_converter' );
	$url = $url_converter->get_url_for_language($TRP_LANGUAGE, $url, '');

	return $url;
}