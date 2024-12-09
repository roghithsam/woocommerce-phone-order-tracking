<?php
/**
 * Plugin Name: WooCommerce Phone Order Tracking
 * Description: A plugin to track WooCommerce orders using the billing phone number.
 * Version: 1.0
 * Author: Roghithsam
 * License: GPL2
 * Text Domain: woocommerce-phone-order-tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function woocommerce_phone_order_tracking_shortcode( $atts ) {
    // Check if the form was submitted
    if ( isset( $_POST['orderid'], $_POST['order_phone'] ) ) {
        $order_id = wc_clean( $_POST['orderid'] );
        $phone_number = wc_clean( $_POST['order_phone'] );
        $nonce_value = isset( $_POST['woocommerce-order-phone-tracking-nonce'] ) ? sanitize_text_field( $_POST['woocommerce-order-phone-tracking-nonce'] ) : '';

        // Verify nonce
        if ( ! wp_verify_nonce( $nonce_value, 'woocommerce-order_phone_tracking' ) ) {
            wc_print_notice( __( 'Security check failed. Please try again.', 'woocommerce' ), 'error' );
            return;
        }

        // Validate order ID and phone number
        if ( empty( $order_id ) ) {
            wc_print_notice( __( 'Please enter a valid order ID.', 'woocommerce' ), 'error' );
        } elseif ( empty( $phone_number ) ) {
            wc_print_notice( __( 'Please enter a valid phone number.', 'woocommerce' ), 'error' );
        } else {
            $order = wc_get_order( apply_filters( 'woocommerce_shortcode_order_tracking_order_id', $order_id ) );

            if ( $order && is_a( $order, 'WC_Order' ) && strtolower( $order->get_billing_phone() ) === strtolower( $phone_number ) ) {
                do_action( 'woocommerce_track_order', $order->get_id() );
                wc_get_template(
                    'order/tracking.php',
                    array(
                        'order' => $order,
                    )
                );
                return;
            } else {
                wc_print_notice( __( 'Sorry, the order could not be found. Please contact us if you are having difficulty finding your order details.', 'woocommerce' ), 'error' );
            }
        }
    }

    // Output the tracking form
    ob_start();
    ?>
    <form action="<?php echo esc_url( get_permalink() ); ?>" method="post" class="woocommerce-form woocommerce-form-track-order track_order">
        <?php do_action( 'woocommerce_order_tracking_form_start' ); ?>

        <p><?php esc_html_e( 'To track your order, please enter your Order ID and Phone Number in the fields below and press the "Track" button.', 'woocommerce' ); ?></p>

        <p class="form-row form-row-first">
            <label for="orderid"><?php esc_html_e( 'Order ID', 'woocommerce' ); ?></label>
            <input class="input-text" type="text" name="orderid" id="orderid" value="<?php echo isset( $_REQUEST['orderid'] ) ? esc_attr( wp_unslash( $_REQUEST['orderid'] ) ) : ''; ?>" placeholder="<?php esc_attr_e( 'Found in your order confirmation email.', 'woocommerce' ); ?>" />
        </p>

        <p class="form-row form-row-last">
            <label for="order_phone"><?php esc_html_e( 'Billing Phone', 'woocommerce' ); ?></label>
            <input class="input-text" type="text" name="order_phone" id="order_phone" value="<?php echo isset( $_REQUEST['order_phone'] ) ? esc_attr( wp_unslash( $_REQUEST['order_phone'] ) ) : ''; ?>" placeholder="<?php esc_attr_e( 'Phone number you used during checkout.', 'woocommerce' ); ?>" />
        </p>

        <div class="clear"></div>

        <?php do_action( 'woocommerce_order_tracking_form' ); ?>

        <p class="form-row">
            <button type="submit" class="button" name="track" value="<?php esc_attr_e( 'Track', 'woocommerce' ); ?>"><?php esc_html_e( 'Track', 'woocommerce' ); ?></button>
        </p>
        <?php wp_nonce_field( 'woocommerce-order_phone_tracking', 'woocommerce-order-phone-tracking-nonce' ); ?>

        <?php do_action( 'woocommerce_order_tracking_form_end' ); ?>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode( 'woocommerce_phone_order_tracking', 'woocommerce_phone_order_tracking_shortcode' );
