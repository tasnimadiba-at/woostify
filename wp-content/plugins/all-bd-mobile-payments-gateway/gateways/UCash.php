<?php
    
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    
    /**
     * UCash Payment Gateway
     *
     * Provides a UCash Payment Gateway, mainly for BD Shops who are accepting UCash.
     *
     * @class 		WC_Gateway_UCash
     * @extends		WC_Payment_Gateway
     * @version		3.0.0
     * @package		WooCommerce/Classes/Payment
     * @author 		Emran Hossen
     */
    class WC_Gateway_ucash extends WC_Payment_Gateway {
        
        /**
         * Constructor for the gateway.
         */
        public function __construct() {
            $this->id                 = 'ucash';
			$this->method_description = __( 'Pay via UCash payment', 'woocommerce' );
            $this->icon               = apply_filters('woocommerce_UCash_icon', plugins_url( '/images/UCash.png', __FILE__ ));
            $this->has_fields         = false;
            $this->method_title       = __( 'ucash', 'woocommerce' );
           
		   
            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();
            
            // Define user set variables
            $this->title        = $this->get_option( 'title' );
            $this->description  = $this->get_option( 'description' );
            $this->instructions = $this->get_option( 'instructions', $this->description );
            $this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
            $this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes' ? true : false;
            
            // UCash account fields shown on the thanks page and in emails
            $this->account_details = get_option( 'woocommerce_ucash_accounts',
                                                array(
                                                      array(
                                                            'account_type'   => $this->get_option( 'account_type' ),
                                                            'account_number' => $this->get_option( 'account_number' )
                                                            )
                                                      )
                                                );
            
            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_account_details' ) );
            add_action( 'woocommerce_thankyou_ucash', array( $this, 'thankyou_page' ) );
            
            // Customer Emails
            add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
        }
        
        /**
         * Initialise Gateway Settings Form Fields
         */
        public function init_form_fields() {
            $shipping_methods = array();
            
            if ( is_admin() )
                foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
                    $shipping_methods[ $method->id ] = $method->get_title();
                }
            
            $this->form_fields = array(
                                       'enabled' => array(
                                                          'title'   => __( 'Enable/Disable', 'woocommerce' ),
                                                          'type'    => 'checkbox',
                                                          'label'   => __( 'UCash', 'woocommerce' ),
                                                          'default' => 'yes'
                                                          ),
                                       'title' => array(
                                                        'title'       => __( 'Title', 'woocommerce' ),
                                                        'type'        => 'text',
                                                        'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                                                        'default'     => __( 'UCash', 'woocommerce' ),
                                                        'desc_tip'    => true,
                                                        ),
                                       'description' => array(
                                                              'title'       => __( 'Description', 'woocommerce' ),
                                                              'type'        => 'textarea',
                                                              'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
                                                              'default'     => __( 'Description', 'woocommerce' ),
                                                              'desc_tip'    => true,
                                                              ),
                                       'instructions' => array(
                                                               'title'       => __( 'Instructions', 'woocommerce' ),
                                                               'type'        => 'textarea',
                                                               'description' => __( 'Instructions', 'woocommerce' ),
                                                               'default'     => 'Instructions',
                                                               'desc_tip'    => true,
                                                               ),
                                       'enable_for_methods' => array(
                                                                     'title'             => __( 'Enable for shipping methods', 'woocommerce' ),
                                                                     'type'              => 'multiselect',
                                                                     'class'             => 'wc-enhanced-select',
                                                                     'css'               => 'width: 450px;',
                                                                     'default'           => '',
                                                                     'description'       => __( 'If UCash is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'woocommerce' ),
                                                                     'options'           => $shipping_methods,
                                                                     'desc_tip'          => true,
                                                                     'custom_attributes' => array(
                                                                                                  'data-placeholder' => __( 'Select shipping methods', 'woocommerce' )
                                                                                                  )
                                                                     ),
                                       'enable_for_virtual' => array(
                                                                     'title'             => __( 'Enable for virtual orders', 'woocommerce' ),
                                                                     'label'             => __( 'Enable UCash if the order is virtual', 'woocommerce' ),
                                                                     'type'              => 'checkbox',
                                                                     'default'           => 'yes'
                                                                     ),
                                       'account_details' => array(
                                                                  'type'        => 'account_details'
                                                                  )
                                       );
        }
        
        /**
         * Check If The Gateway Is Available For Use
         *
         * @return bool
         */
        public function is_available() {
            $order = null;
            
            if ( ! $this->enable_for_virtual ) {
                if ( WC()->cart && ! WC()->cart->needs_shipping() ) {
                    return false;
                }
                
                if ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
                    $order_id = absint( get_query_var( 'order-pay' ) );
                    $order    = wc_get_order( $order_id );
                    
                    // Test if order needs shipping.
                    $needs_shipping = false;
                    
                    if ( 0 < sizeof( $order->get_items() ) ) {
                        foreach ( $order->get_items() as $item ) {
                            $_product = $order->get_product_from_item( $item );
                            
                            if ( $_product->needs_shipping() ) {
                                $needs_shipping = true;
                                break;
                            }
                        }
                    }
                    
                    $needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );
                    
                    if ( $needs_shipping ) {
                        return false;
                    }
                }
            }
            
            if ( ! empty( $this->enable_for_methods ) ) {
                
                // Only apply if all packages are being shipped via local pickup
                $chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );
                
                if ( isset( $chosen_shipping_methods_session ) ) {
                    $chosen_shipping_methods = array_unique( $chosen_shipping_methods_session );
                } else {
                    $chosen_shipping_methods = array();
                }
                
                $check_method = false;
                
                if ( is_object( $order ) ) {
                    if ( $order->shipping_method ) {
                        $check_method = $order->shipping_method;
                    }
                    
                } elseif ( empty( $chosen_shipping_methods ) || sizeof( $chosen_shipping_methods ) > 1 ) {
                    $check_method = false;
                } elseif ( sizeof( $chosen_shipping_methods ) == 1 ) {
                    $check_method = $chosen_shipping_methods[0];
                }
                
                if ( ! $check_method ) {
                    return false;
                }
                
                $found = false;
                
                foreach ( $this->enable_for_methods as $method_id ) {
                    if ( strpos( $check_method, $method_id ) === 0 ) {
                        $found = true;
                        break;
                    }
                }
                
                if ( ! $found ) {
                    return false;
                }
            }
            
            return parent::is_available();
        }
        
        
        /**
         * generate_account_details_html function.
         */
        public function generate_account_details_html() {
            ob_start();
            ?>
<tr valign="top">
<th scope="row" class="titledesc"><?php _e( 'Account Details', 'woocommerce' ); ?>:</th>
<td class="forminp" id="ucash_accounts">
<table class="widefat wc_input_table sortable" cellspacing="0">
<thead>
<tr>
<th class="sort">&nbsp;</th>
<th><?php _e( 'Account Type', 'woocommerce' ); ?></th>
<th><?php _e( 'Account Number', 'woocommerce' ); ?></th>
</tr>
</thead>
<tfoot>
<tr>
<th colspan="7"><a href="#" class="add button"><?php _e( '+ Add Account', 'woocommerce' ); ?></a> <a href="#" class="remove_rows button"><?php _e( 'Remove selected account(s)', 'woocommerce' ); ?></a></th>
</tr>
</tfoot>
<tbody class="accounts">
<?php
    $i = -1;
    if ( $this->account_details ) {
        foreach ( $this->account_details as $account ) {
            $i++;
            
            echo '<tr class="account">
            <td class="sort"></td>
            <td><input type="text" value="' . esc_attr( $account['account_type'] ) . '" name="ucash_account_type[' . $i . ']" /></td>
            <td><input type="text" value="' . esc_attr( $account['account_number'] ) . '" name="ucash_account_number[' . $i . ']" /></td>
            </tr>';
        }
    }
    ?>
</tbody>
</table>
<script type="text/javascript">
jQuery(function() {
       jQuery('#ucash_accounts').on( 'click', 'a.add', function(){
                                    
                                    var size = jQuery('#ucash_accounts tbody .account').size();
                                    
                                    jQuery('<tr class="account">\
                                           <td class="sort"></td>\
                                           <td><input type="text" name="ucash_account_type[' + size + ']" /></td>\
                                           <td><input type="text" name="ucash_account_number[' + size + ']" /></td>\
                                           </tr>').appendTo('#ucash_accounts table tbody');
                                    
                                    return false;
                                    });
       });
</script>
</td>
</tr>
<?php
    return ob_get_clean();
    }
    
    /**
     * Save account details table
     */
    public function save_account_details() {
        $accounts = array();
        
        if ( isset( $_POST['ucash_account_type'] ) ) {
            
            $account_types   = array_map( 'wc_clean', $_POST['ucash_account_type'] );
            $account_numbers = array_map( 'wc_clean', $_POST['ucash_account_number'] );
            
            foreach ( $account_types as $i => $type ) {
                if ( ! isset( $account_types[ $i ] ) ) {
                    continue;
                }
                
                $accounts[] = array(
                                    'account_type'   => $account_types[ $i ],
                                    'account_number' => $account_numbers[ $i ]
                                    );
            }
        }
        
        update_option( 'woocommerce_ucash_accounts', $accounts );
    }
    
    /**
     * Output for the order received page.
     */
    public function thankyou_page( $order_id ) {
        if ( $this->instructions ) {
            echo wpautop( wptexturize( wp_kses_post( $this->instructions ) ) );
        }
        $this->ucash_details( $order_id );
    }
    
    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     * @return void
     */
    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        
        if ( $sent_to_admin || $order->status !== 'on-hold' || $order->payment_method !== 'ucash' ) {
            return;
        }
        
        if ( $this->instructions ) {
            echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
        }
        
        $this->ucash_details( $order->id );
    }
    
    /**
     * Get UCash details and place into a list format
     */
    private function ucash_details( $order_id = '' ) {
        if ( empty( $this->account_details ) ) {
            return;
        }
        
        echo '<h2>' . __( 'Our UCash Details', 'woocommerce' ) . '</h2>' . PHP_EOL;
        
        $ucash_accounts = apply_filters( 'woocommerce_ucash_accounts', $this->account_details );
        
        if ( ! empty( $ucash_accounts ) ) {
            foreach ( $ucash_accounts as $ucash_account ) {
                echo '<ul class="order_details ucash_details">' . PHP_EOL;
                
                $ucash_account = (object) $ucash_account;
                
                // ucash account fields shown on the thanks page and in emails
                $account_fields = apply_filters( 'woocommerce_ucash_account_fields', array(
                                                                                           'account_type'=> array(
                                                                                                                  'label' => __( 'Account Type', 'woocommerce' ),
                                                                                                                  'value' => $ucash_account->account_type
                                                                                                                  ),
                                                                                           'account_number'=> array(
                                                                                                                    'label' => __( 'Account Number', 'woocommerce' ),
                                                                                                                    'value' => $ucash_account->account_number
                                                                                                                    )
                                                                                           ), $order_id );
                
                if ( $ucash_account->account_type || $ucash_account->account_number ) {
                    echo '<h3>' . implode( ' - ', array_filter( array( $ucash_account->account_type, $ucash_account->account_number ) ) ) . '</h3>' . PHP_EOL;
                }
                
                foreach ( $account_fields as $field_key => $field ) {
                    if ( ! empty( $field['value'] ) ) {
                        echo '<li class="' . esc_attr( $field_key ) . '">' . esc_attr( $field['label'] ) . ': <strong>' . wptexturize( $field['value'] ) . '</strong></li>' . PHP_EOL;
                    }
                }
                
                echo '</ul>';
            }
        }
    }
    
    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {
        
        $order = new WC_Order( $order_id );
        
        // Mark as on-hold (we're awaiting the payment)
        $order->update_status( 'on-hold', __( 'Awaiting UCash payment', 'woocommerce' ) );
        
        // Reduce stock levels
        $order->reduce_order_stock();
        
        // Remove cart
        WC()->cart->empty_cart();
        
        // Return thankyou redirect
        return array(
                     'result' 	=> 'success',
                     'redirect'	=> $this->get_return_url( $order )
                     );
    }
    }
