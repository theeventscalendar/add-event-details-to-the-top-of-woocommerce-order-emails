<?php
/**
 * Plugin Name: The Events Calendar Extension: Add Event Details to the Top of WooCommerce Order Emails
 * Description: Add event details to the top of WooCommerce order emails if the order includes event tickets.
 * Version: 1.0.0
 * Author: Modern Tribe, Inc.
 * Author URI: http://m.tri.be/1971
 * License: GPLv2 or later
 */

defined( 'WPINC' ) or die;

class Tribe__Extension__Add_Event_Details_to_the_Top_of_WooCommerce_Order_Emails {

    /**
     * The semantic version number of this extension; should always match the plugin header.
     */
    const VERSION = '1.0.0';

    /**
     * Each plugin required by this extension
     *
     * @var array Plugins are listed in 'main class' => 'minimum version #' format
     */
    public $plugins_required = array(
        'Tribe__Tickets__Main' => '4.2',
        'Tribe__Events__Main'  => '4.2'
    );

    /**
     * The constructor; delays initializing the extension until all other plugins are loaded.
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ), 100 );
    }

    /**
     * Extension hooks and initialization; exits if the extension is not authorized by Tribe Common to run.
     */
    public function init() {

        // Exit early if our framework is saying this extension should not run.
        if ( ! function_exists( 'tribe_register_plugin' ) || ! tribe_register_plugin( __FILE__, __CLASS__, self::VERSION, $this->plugins_required ) ) {
            return;
        }

        add_action( 'woocommerce_email_before_order_table', array( $this, 'add_attending_events_above_woo_order_info' ), 10, 2 );
    }

    /**
     * Adds ticketed event information to WooCommerce "order confirmation" emails.
     *
     * @param object $order
     * @param bool $sent_to_admin
     * @return void
     */
    public function add_attending_events_above_woo_order_info( $order, $sent_to_admin ) {
        
            $line_items    = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
            $product_ids   = array();
            $event_objects = array();
        
            if ( ! is_array( $line_items ) || empty( $line_items ) ) {
                return;
            }
        
            foreach ( $line_items as $line_item ) {
                $product_id = absint( $line_item['item_meta']['_product_id'][0] );
        
                if ( ! $product_id || in_array( $product_id, $product_ids ) ) {
                    continue;
                }
        
                $event_object = tribe_events_get_ticket_event( $product_id );
                
                if ( ! empty( $event_object ) ) {
                    $event_objects[] = $event_object;
                }
            }
        
            if ( empty( $event_objects ) ) {
                return;
            }
        ?>
            
            <h2 style="color: #557da1; display: block; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 16px 0 8px; text-align: left;">Events</h2>
        
            <?php foreach ( $event_objects as $event_object ) : ?>
        
            <p style="margin: 0 0 16px;">
                <strong><?php echo $event_object->post_title; ?></strong><br>
                <?php echo tribe_events_event_schedule_details( $event_object->ID, '<em>', '</em>' ); ?><br>
                <?php echo tribe_get_full_address( $event_object->ID ); ?>
            </p>
        
        <?php endforeach;
    }

}

new Tribe__Extension__Add_Event_Details_to_the_Top_of_WooCommerce_Order_Emails();
