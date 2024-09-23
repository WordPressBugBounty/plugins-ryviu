<?php
/**
 * (c) copyright:  https://www.ryviu.com
 * Author: Ryviu
**/

class RyviuSettings{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_style' ) );
    }


    public function load_admin_style() {
        wp_enqueue_style( 'ryviu-admin-style', RYVIU_URL_ASSETS . 'css/ryviu-admin.css', false, time() );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Ryviu',
            'Ryviu',
            'manage_options',
            'ryviu-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }

    public static function get_option( $name ){
        $options = get_option( 'ryviu_settings_reviews' );

        if(is_array($options) && isset($options[$name])){
            return $options[$name];
        }else{
            return '';
        }
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'ryviu_settings_reviews' );
        $image_url = RYVIU_URL_ASSETS.'images/logo-ryviu-v8.svg';
        ?>
        <div class="ryviu-page--header">
            <img class="ryviu-page--image" src="<?php echo $image_url; ?>" alt="Ryviu Plugin"/>
            <div class="ryviu-page--intro">
                <h1 class="ryviu-page--title">Welcome to Ryviu!</h1>
                <p class="ryviu-page--intro">Ryviu is a product review app that helps you generate eCommerce social proof, increase trust & sales. You can easy to customize reviews with Ryviu and show them in your store.</p>
                <a class="ryviu-page--btn" href="https://app.ryviu.io" rel="noopener" target="_blank">Ryviu Dashboard</a>
                <a class="ryviu-page--btn ryviu-page--btnnd" rel="noopener" href="https://docs.ryviu.com/en/collections/1861213-woocommerce" target="_blank">Document</a>
                <a class="ryviu-page--btn ryviu-page--btnnd r-rate--us" rel="noopener" href="https://wordpress.org/support/plugin/ryviu/reviews/#new-post" target="_blank">&#9733; Rate Us</a>
            </div>
        </div>
        <div class="wrap">
            
            <form method="post" action="options.php">
                <?php
                    // This prints out all hidden setting fields
                    settings_fields( 'ryviu_option_group' );
                    do_settings_sections( 'ryviu-setting-admin' );

                    echo '<div class="info">
                    <p><strong>Priority</strong>: Used to specify the order in which the functions associated with a particular action are executed. Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.</p>
                </div>';

                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'ryviu_option_group', // Option group
            'ryviu_settings_reviews', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );


        add_settings_section(
            'general_id', // ID
            'Ryviu settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'ryviu-setting-admin' // Page
        );
        foreach ($this->general_settings() as $field) {
            add_settings_field(
                $field['name'], // name
                $field['title'], // Title
                array( $this, $field['name'].'_callback' ), // Callback
                'ryviu-setting-admin', // Page
                'general_id' // Section
            );
        }
    }
   
    public function general_settings(){
        $fields = [
            'ryviu_frontend_version' => 'Reviews widget interface',
            'position_display_widget' => 'Star rating on product page',
            'show_average_rating' => 'Show average rating',
            'position_display_widget_in_loop' => 'Star rating on collection page',
            'position_display' => 'Review box on product page',
            'custom_tab_title' => 'Title reviews tab',
            'active_reviews_tab' => 'Default active review tab',
            'one_column_mobile' => '1 column on mobile',
            'remove_write_review' => 'Write a Review button',
            'element_trigger_click' => 'Title reviews tab HTML',
            'question_and_answer' => 'Question and Answer',
            'custom_question_tab_title' => 'Title Question & Answer tab',
            'wordpress_theme' => 'Wordpress Theme',
            'data_rocket_status' => 'Cloudfare CDN & Data Rocket',
        ];
    
        return array_map(function($name, $title) {
            return ['name' => $name, 'title' => $title];
        }, array_keys($fields), array_values($fields));
    }

    public function check_selected($val1 = '1', $val2 = '2'){
        if($val1 == $val2){
            return 'selected="selected"';
        }
        return '';
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();

        $fields_setting = $this->general_settings();

        $fields_setting[] = array(
            'name' => 'priority_position_display_widget',
            'title' => 'Priority position'
        );

        foreach($fields_setting as $field){
            if( isset( $input[$field['name']] ) ){
                if(is_numeric($input[$field['name']])){
                    $new_input[$field['name']] = absint( $input[$field['name']] );
                }else{
                    $new_input[$field['name']] = sanitize_text_field( $input[$field['name']] );
                }
            }
        }

        flush_rewrite_rules();

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info(){
        print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
  
    public function custom_tab_title_callback(){
        $trace_fn = debug_backtrace();
        $name = str_replace('_callback', '', $trace_fn[0]["function"] );

        $this->input_field($name);
    }
    public function element_trigger_click_callback(){
        $trace_fn = debug_backtrace();
        $name = str_replace('_callback', '', $trace_fn[0]["function"] );

        $this->input_element_trigger_click($name);
    }
    public function custom_question_tab_title_callback(){
        $trace_fn = debug_backtrace();
        $name = str_replace('_callback', '', $trace_fn[0]["function"] );
        $this->input_question_field($name);
    }

    public function position_display_callback(){
        $trace_fn = debug_backtrace();
        $name = str_replace('_callback', '', $trace_fn[0]["function"] );

        $this->select_field($name);
    }

    public function position_display_widget_callback(){
        $trace_fn = debug_backtrace();
        $name = str_replace('_callback', '', $trace_fn[0]["function"] );

        $this->select_field($name);
        
    }

    public function ryviu_frontend_version_callback(){

        $select = isset($this->options['ryviu_frontend_version']) ? $this->options['ryviu_frontend_version'] : '1';

        echo '<select id="ryviu_frontend_version" class="ryviu_settings_reviews" name="ryviu_settings_reviews[ryviu_frontend_version]">';

        $themes = array('1' => 'Version 1', '2' => 'Version 2');

        foreach ($themes as $key => $data) {
            echo '<option value="'. $key .'" '. $this->check_selected($select, $key) .'>'. $data .'</option>';
        }
        echo '</select>';
    }

    public function position_display_widget_in_loop_callback(){
        $trace_fn = debug_backtrace();
        $name = str_replace('_callback', '', $trace_fn[0]["function"] );

        $this->select_field($name);
    }

    public function show_average_rating_callback(){
        $name = 'show_average_rating';
        $status = isset($this->options[$name])? $this->options[$name]: 0;

        echo '<div class="nice_fields rpl-one-col">
                <ul class="tg-list">
                    <li class="tg-list-item">
                        <input class="tgl tgl-light" id="r-average" type="checkbox" '.checked( $status, 'on', false ).' name="ryviu_settings_reviews['.$name.']"/>
                        <label class="tgl-btn" for="r-average"></label>
                    </li>
                </ul> <span>&#9733;&#9733;&#9733;&#9733;&#9733; 5.0 (20 reviews)</span>
            </div>';
    }

    public function input_element_trigger_click($name){
        $val = isset($this->options[$name])? $this->options[$name] : '';
        echo '<input type="text" name="ryviu_settings_reviews['.$name.'] aria-describedby="custom-tab-title" placeholder=".ryviu_reviews_tab_tab > a" value="'.$val.'" class="regular-text">';
        echo '<p class="description" id="custom-tab-title">If you can not open the reviews tab when clicking on star ratings, please enter the title reviews tab HTML (Jquery selector) to fix it. Please contact Ryviu or your technical staff to get help.</p>';
    }

    public function input_question_field($name){
        $val = isset($this->options[$name])? $this->options[$name] : 'Question and Answer';
        echo '<input type="text" name="ryviu_settings_reviews['.$name.'] aria-describedby="custom-tab-title" value="'.$val.'" class="regular-text">';
        echo '<p class="description" id="custom-tab-title">This text will show in Question and Answer the tab on the product page.</p></div>';
    }

    public function input_field($name){
        $val = isset($this->options[$name])? $this->options[$name] : 'Reviews (%total_number%)';
        echo '<input type="text" name="ryviu_settings_reviews['.$name.'] aria-describedby="custom-tab-title" value="'.$val.'" class="regular-text">';
        echo '<p class="description" id="custom-tab-title">This text will show in reviews the tab on the product page. Use (%total_number%) to display the total number of reviews.</p>';
    }

    public function select_field($name){
        $select = isset($this->options[$name])? $this->options[$name] : 1;
        echo '<select id="position_display" class="ryviu_settings_reviews" name="ryviu_settings_reviews['.$name.']">';
        foreach (ryviu_display_position_hook($name) as $key => $data) {
            echo '<option value="'. $key .'" '. $this->check_selected($select, $key) .'>'. $data['title'] .'</option>';
        }
        echo '</select>';
        if($name == 'position_display_widget'){
            $priority_select = isset($this->options['priority_'.$name]) ? $this->options['priority_'.$name] : 10;
            echo '<span class="priority-label">Priority</span><select name="ryviu_settings_reviews[priority_'.$name.']">';
            foreach([6, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80] as $priority){
                echo '<option value="'. $priority .'" '. $this->check_selected($priority_select, $priority) .'>'. $priority .'</option>';
            }
            echo '</select>';
        }
        $p_display = 'none';
        if($select == 10){
            $p_display = 'block';
        }
        echo '<p class="custom_position_display_reviews" style="display:'.$p_display.';">'. $this->__($name) .'</p>';
    }

    public function one_column_mobile_callback(){
        $name = 'one_column_mobile';
        $status = isset($this->options[$name])? $this->options[$name]: 0;

        echo '<div class="nice_fields rpl-one-col">
            <ul class="tg-list">
            <li class="tg-list-item">
                <input class="tgl tgl-light" id="cb3" type="checkbox" '.checked( $status, 'on', false ).' name="ryviu_settings_reviews['.$name.']"/>
                <label class="tgl-btn" for="cb3"></label>
            </li></ul> <span>Support Masonry theme</span>
            </div>';
    }
    public function remove_write_review_callback(){
        $name = 'remove_write_review';
        $status = isset($this->options[$name])? $this->options[$name]: 0;

        echo '<div class="nice_fields rpl-one-col">
            <ul class="tg-list">
            <li class="tg-list-item">
                <input class="tgl tgl-light" id="cb4" type="checkbox" '.checked( $status, 'on', false ).' name="ryviu_settings_reviews['.$name.']"/>
                <label class="tgl-btn" for="cb4"></label>
            </li></ul> <span>Hide the <strong>Write a review</strong> button when customers are not logged in</span>
            </div>';
    }

    public function data_rocket_status_callback(){
        $trace_fn = debug_backtrace();
        $name = str_replace('_callback', '', $trace_fn[0]["function"] );
        $status = isset($this->options[$name])? $this->options[$name]: 0;

        echo '<div class="nice_fields"><ul class="tg-list">
            <li class="tg-list-item">
                <input class="tgl tgl-light" id="cb1" type="checkbox" '.checked( $status, 'on', false ).' name="ryviu_settings_reviews['.$name.']"/>
                <label class="tgl-btn" for="cb1"></label>
            </li></ul> <span>Enable this option when your site using Cloudfare CDN and Data Rocket</span></div>';
    }

    public static function __($name){
        $__ = array(
            'position_display' => __('Add our PHP code or Shortcode anywhere in the single product page: <code><input class="medium-text" type="text" readonly="readonly" value="<?php do_action( \'ryviu_display_review\' ); ?>" style="min-width: 315px;color: #000;" /><input class="medium-text" type="text" readonly="readonly" value="[ryviu_widget]" style="min-width: 315px;color: #000;" /></code>', 'ryviu' ),
            'position_display_widget' => __('Add our PHP code or Shortcode anywhere in the single product page: <code><input class="medium-text" type="text" readonly="readonly" value="<?php do_action( \'ryviu_display_total_review\' ); ?>" style="min-width: 360px;color: #000;" /><input class="medium-text" type="text" readonly="readonly" value="[ryviu_widget_total]" style="min-width: 360px;color: #000;" /></code>', 'ryviu'),
            'position_display_widget_in_loop' => __('Add this code to anywhere in loop category product page: <code><input class="medium-text" type="text" readonly="readonly" value="<?php do_action( \'ryviu_display_review_total_in_loop\' ); ?>" style="min-width: 400px;color: #000;" /><input class="medium-text" type="text" readonly="readonly" value="[ryviu_widget_colection]" style="min-width: 360px;color: #000;" /></code>', 'ryviu')
        );

        return $__[$name];
    }
    public function wordpress_theme_callback(){

        $select = isset($this->options['wordpress_theme']) ? $this->options['wordpress_theme'] : 'default';

        echo '<select id="wordpress_theme" class="ryviu_settings_reviews" name="ryviu_settings_reviews[wordpress_theme]">';

        $themes = array('default' => 'Default', 'ocean' => 'Ocean');

        foreach ($themes as $key => $data) {
            echo '<option value="'. $key .'" '. $this->check_selected($select, $key) .'>'. $data .'</option>';
        }
        echo '</select>';

        echo '<p class="description">Fix conflicts with some themes. List of themes: Ocean</p>';
    }
    /**
     * Get the settings option array and print one of its values
     */
    public function active_reviews_tab_callback(){
        $select = isset($this->options['active_reviews_tab']) ? $this->options['active_reviews_tab'] : 0;

        echo '<select id="active_reviews_tab" class="ryviu_settings_reviews" name="ryviu_settings_reviews[active_reviews_tab]">';
        foreach (array('1' => 'Yes', '0' => 'No') as $key => $data) {
            echo '<option value="'. $key .'" '. $this->check_selected($select, $key) .'>'. $data .'</option>';
        }
        echo '</select>';

        echo '<p class="description">Active reviews tab when view product (Apply for Review tab)</p>';
    }

    public function question_and_answer_callback(){
        $select = isset($this->options['question_and_answer']) ? $this->options['question_and_answer'] : 0;

        echo '<select id="question_and_answer" class="ryviu_settings_reviews" name="ryviu_settings_reviews[question_and_answer]">';
        foreach (array('0' => 'Disable', '1' => 'After single product summary','2' => 'Question and Answer tab' ) as $key => $data) {
            echo '<option value="'. $key .'" '. $this->check_selected($select, $key) .'>'. $data .'</option>';
        }
        echo '</select>';

        echo '<p class="description">Show Question and Answer on the default position (Only support Ryviu\'s premium accounts)</p>';
        echo '<p class="custom_position_display_reviews r--alshow">Add our PHP code or Shortcode anywhere in the single product page: <code><input class="medium-text" type="text" readonly="readonly" value="<?php do_action( \'ryviu_question_and_answer\' ); ?>" style="min-width: 315px;color: #000;" /><input class="medium-text" type="text" readonly="readonly" value="[ryviu_question_and_answer]" style="min-width: 315px;color: #000;" /></code></p>';
    }

    public function enable_ajax_add_to_cart_callback(){
        $select = 0;

        if($this->options['enable_ajax_add_to_cart']){
            $select = $this->options['enable_ajax_add_to_cart'];
        }
        
        echo '<select id="position_display" class="ryviu_settings_reviews" name="ryviu_settings_reviews[enable_ajax_add_to_cart]">';
        foreach (array('1' => 'Yes', '0' => 'No') as $key => $data) {
            echo '<option value="'. $key .'" '. $this->check_selected($select, $key) .'>'. $data .'</option>';
        }
        echo '</select>';

        echo '<p>This option will replace default add to cart by ajax';
    }
}

if( is_admin() ){
   new RyviuSettings();
}
