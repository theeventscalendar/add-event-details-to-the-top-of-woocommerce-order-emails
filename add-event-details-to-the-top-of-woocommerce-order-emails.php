<?php
/**
 * Plugin Name: The Events Calendar — Add Event Details to the Top of WooCommerce Order Emails
 * Description: Add event details to the top of WooCommerce order emails if the order includes event tickets.
 * Version: 1.0.0
 * Author: Modern Tribe, Inc.
 * Author URI: http://m.tri.be/1x
 * License: GPLv2 or later
 */
 
defined( 'WPINC' ) or die;

/**
 * Adds ticketed event information to WooCommerce "order confirmation" emails.
 *
 * @param object $order
 * @param bool $sent_to_admin
 * @return void
 */
function add_attending_events_above_woo_order_info( $order, $sent_to_admin ) {
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
	
	<h2 style="color: #557da1; display: block; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 16px 0 8px; text-align: left;">
		Events
	</h2>

	<?php foreach ( $event_objects as $event_object ) : ?>

	<p style="margin: 0 0 16px;">
		<strong><?php echo $event_object->post_title; ?></strong><br>
		<?php echo tribe_events_event_schedule_details( $event_object->ID, '<em>', '</em>' ); ?><br>
		<?php echo tribe_get_full_address( $event_object->ID ); ?>
	</p>

	<?php endforeach;
}

add_action( 'woocommerce_email_before_order_table', 'add_attending_events_above_woo_order_info', 10, 2 );
