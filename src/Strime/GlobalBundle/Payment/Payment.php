<?php

namespace Strime\GlobalBundle\Payment;

class Payment {

    public $stripe_secret_key;
    public $stripe_customer_id;
    public $stripe_subscription_id;
    public $strime_api_url;
    public $headers;
    public $user_id;
    public $offer_id;
    public $update_offer_id = FALSE;
    public $plan;
    public $coupon_id;
    public $amount_off;
    public $percent_off;
    public $currency;
    public $duration;
    public $duration_in_months;
    public $max_redemptions;
    public $redeem_by;
    public $offers;

    public function __construct() {
        $this->headers = array(
            'Content-type' => 'application/json'
        );
    }



    /**
     * @param string $stripe_token The token returned by Stripe JS
     * @param string $email The user email
     * @return array The result of the customer creation
     */
    public function createCustomer($stripe_token, $email)
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        try {
            // Create a Customer
            $customer = \Stripe\Customer::create(array(
              "source" => $stripe_token,
              "description" => "Email : ".$email." - ID : ".$this->user_id)
            );

            // Update the database
            // Set the endpoint
            $endpoint = $this->strime_api_url."user/".$this->user_id."/edit";

            // Prepare the parameters
            $params = array(
                'stripe_id' => (string)$customer->{'id'}
            );

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('PUT', $endpoint, [
                'headers' => $this->headers,
                'http_errors' => false,
                'json' => $params
            ]);
            $edit_user_curl_status = $json_response->getStatusCode();
            $edit_user_json_response = json_decode( $json_response->getBody() );

            if($edit_user_curl_status != 200) {
                $stripe_error = "id_not_saved";
                $result = array(
                    "status" => "error",
                    "status_boolean" => FALSE,
                    "error_code" => $stripe_error
                );
            }
            else {
                $result = array(
                    "status" => "success",
                    "status_boolean" => TRUE,
                    "customer" => $customer
                );
            }
        }
        catch(\Stripe\Error\Card $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            // Invalid parameters were supplied to Stripe's API
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\Authentication $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\Base $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (Exception $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }

        return $result;
    }



    /**
     * @param string $stripe_token The token returned by Stripe JS
     * @param string $email The user email
     * @return array The result of the customer creation
     */
    public function updateCustomer($stripe_token = NULL, $email = NULL)
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        // Set the result variable
        $result = NULL;

        try {
            $customer = \Stripe\Customer::retrieve( $this->stripe_customer_id ); // stored in your application
            
            if($stripe_token != NULL)
                $customer->source = $stripe_token; // obtained with Checkout
            if($email != NULL)
                $customer->description = "Email : ".$email." - ID : ".$this->user_id;
            
            $customer->save();

            $result = array(
                "status" => "success",
                "status_boolean" => TRUE,
                "customer" => $customer
            );
        }
        catch(\Stripe\Error\Card $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            // Invalid parameters were supplied to Stripe's API
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\Authentication $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\Base $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (Exception $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }

        return $result;
    }

    /**
     * @param string $stripe_token The token returned by Stripe JS
     * @param string $email The user email
     * @return array The result of the payment
     */
    public function executePayment($stripe_token, $email)
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        // Set the parameters
        $params = array(
            "source" => $stripe_token,
            "plan" => trim( $this->plan ),
            "email" => $email
        );

        if(isset($this->coupon))
            $params["coupon"] = $this->coupon;

        try {
            // Create the customer
            $customer = \Stripe\Customer::create( $params );

            $result = NULL;

            // If the result is a valid customer
            // then save his ID
            if(isset($customer->{'id'})) {

                // Set the endpoint
                $endpoint = $this->strime_api_url."user/".$this->user_id."/edit";

                // Prepare the parameters
                $params = array(
                    'stripe_id' => (string)$customer->{'id'},
                    'stripe_sub_id' => (string)$customer->{'subscriptions'}->{'data'}[0]->{'id'}
                );

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('PUT', $endpoint, [
                    'headers' => $this->headers,
                    'http_errors' => false,
                    'json' => $params
                ]);
                $edit_user_curl_status = $json_response->getStatusCode();
                $edit_user_json_response = json_decode( $json_response->getBody() );

                if($edit_user_curl_status != 200) {
                    $stripe_error = "id_not_saved";
                    $result = array(
                        "status" => "error",
                        "status_boolean" => FALSE,
                        "error_code" => $stripe_error
                    );
                }
                else {
                    $result = array(
                        "status" => "success",
                        "status_boolean" => TRUE,
                        "customer" => $customer,
                        "current_period_start" => $customer->{'subscriptions'}->{'data'}[0]->{'current_period_start'},
                        "current_period_end" => $customer->{'subscriptions'}->{'data'}[0]->{'current_period_end'}
                    );
                }
            }

            // Else, throw an error
            else {
                $stripe_error = $customer;
                $result = array(
                    "status" => "error",
                    "status_boolean" => FALSE,
                    "error_code" => $stripe_error,
                    "stripe_id" => (string)$customer->{'id'}
                );
            }
        }
        catch(\Stripe\Error\Card $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            // Invalid parameters were supplied to Stripe's API
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\Authentication $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (\Stripe\Error\Base $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }
        catch (Exception $e) {
            
            // Something else happened, completely unrelated to Stripe
            $result = array(
                "status" => "error",
                "status_boolean" => FALSE,
                "customer" => NULL
            );
        }

        return $result;
    }



    /**
     * @return array The result of the creation
     */
    public function createSubscription()
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        try {
            $customer = \Stripe\Customer::retrieve( $this->stripe_customer_id );
            $subscription = \Stripe\Subscription::create(
                array(
                    "customer" => $this->stripe_customer_id,
                    "plan" => $this->plan
                )
            );

            // If the customer has been properly subscribed to the plan
            if(isset($subscription->id)) {

                // Change the offer in the profile of the user in the API
                $params = array(
                    "offer_id" => $this->offer_id,
                    "stripe_sub_id" => $subscription->id
                );

                // Set the endpoint
                $endpoint = $this->strime_api_url."user/".$this->user_id."/edit";

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('PUT', $endpoint, [
                    'headers' => $this->headers,
                    'http_errors' => false,
                    'json' => $params
                ]);
                $curl_status = $json_response->getStatusCode();
                $response = json_decode( $json_response->getBody() );

                if($curl_status == 200)
                    return array(
                        "current_period_start" => $subscription->current_period_start,
                        "current_period_end" => $subscription->current_period_end
                    );
                else
                    return FALSE;
            }

            // If an error occured while subscribing the user to the plan
            else {
                return FALSE;
            }
        }
        catch(\Stripe\Error\Subscription $e) {

            return FALSE;
        }
        catch(\Stripe\Error\Card $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }
    }



    /**
     * @return boolean The result of the change
     */
    public function changeSubscription()
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        try {
            $customer = \Stripe\Customer::retrieve( $this->stripe_customer_id );
            $subscription = \Stripe\Subscription::retrieve( $this->stripe_subscription_id );
            $subscription->plan = $this->plan;
            $subscription->save();

            // Change the offer in the profile of the user in the API
            $params = array(
                "offer_id" => $this->offer_id,
                "stripe_sub_id" => $subscription->id
            );

            // Set the endpoint
            $endpoint = $this->strime_api_url."user/".$this->user_id."/edit";

            // Send the request
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('PUT', $endpoint, [
                'headers' => $this->headers,
                'http_errors' => false,
                'json' => $params
            ]);
            $curl_status = $json_response->getStatusCode();
            $response = json_decode( $json_response->getBody() );

            if($curl_status == 200)
                return TRUE;
            else
                return FALSE;
        }
        catch(\Stripe\Error\Subscription $e) {

            return FALSE;
        }
        catch(\Stripe\Error\Card $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }
    }



    /**
     * @return boolean The result of the cancelation
     */
    public function cancelSubscription()
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        try {
            $customer = \Stripe\Customer::retrieve( $this->stripe_customer_id );
            $subscription = \Stripe\Subscription::retrieve( $this->stripe_subscription_id );
            $subscription->cancel();

            // If we have to update user details
            if($this->update_offer_id == TRUE) {

                // Change the offer in the profile of the user in the API
                $params = array(
                    "offer_id" => $this->offer_id,
                    "empty_stripe_sub_id" => 1
                );

                // Set the endpoint
                $endpoint = $this->strime_api_url."user/".$this->user_id."/edit";

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('PUT', $endpoint, [
                    'headers' => $this->headers,
                    'http_errors' => false,
                    'json' => $params
                ]);
                $curl_status = $json_response->getStatusCode();
                $response = json_decode( $json_response->getBody() );

                if($curl_status == 200)
                    return TRUE;
                else
                    return FALSE;
            }
            else {
                return TRUE;
            }
        }
        catch(\Stripe\Error\Subscription $e) {

            return FALSE;
        }
        catch(\Stripe\Error\Card $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }
    }



    /**
     * @return string The subscription details
     */
    public function getSubscription()
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        try {
            $subscription = \Stripe\Subscription::retrieve( $this->stripe_subscription_id );
            return $subscription;
        }
        catch(\Stripe\Error\Subscription $e) {

            return FALSE;
        }
        catch(\Stripe\Error\Card $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }
    }



    /**
     * @return string The last digits of the credit card of the user
     */
    public function getLastDigits()
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        try {
            $customer = \Stripe\Customer::retrieve( array( "id" => $this->stripe_customer_id, "expand" => array("default_source") ) );
            return $customer->default_source->last4;
        }
        catch(\Stripe\Error\Card $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }
    }



    /**
     * @return string The brand of the card of the user
     */
    public function getCardBrand()
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        try {
            $customer = \Stripe\Customer::retrieve( array( "id" => $this->stripe_customer_id, "expand" => array("default_source") ) );
            return $customer->default_source->brand;
        }
        catch(\Stripe\Error\Card $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }
    }



    /**
     * @return object The details of the coupon
     */
    public function supportTLS()
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        \Stripe\Stripe::$apiBase = "https://api-tls12.stripe.com";
        try {
            \Stripe\Charge::all();
            return TRUE;
        } 
        catch(\Stripe\Error\Card $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }
    }



    /**
     * @return boolean The result of the creation
     */
    public function createCoupon()
    {
        // Create a coupon in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        // Set Stripe parameters
        $stripe_params = array(
            "id" => $this->coupon_id,
            "currency" => $this->currency
        );

        if($this->amount_off != 0) {
            $this->amount_off = (int)round($this->amount_off, 0);
            $stripe_params["amount_off"] = $this->amount_off;
        }
        if($this->percent_off != 0) {
            $this->percent_off = (int)round($this->percent_off, 0);
            $stripe_params["percent_off"] = $this->percent_off;
        }
        if(isset($this->duration))
            $stripe_params["duration"] = $this->duration;
        if(($this->duration_in_months != 0) && (strcmp($this->duration, "repeating") == 0))
            $stripe_params["duration_in_months"] = $this->duration_in_months;
        if($this->max_redemptions != 0)
            $stripe_params["max_redemptions"] = $this->max_redemptions;
        if(isset($this->redeem_by))
            $stripe_params["redeem_by"] = $this->redeem_by;

        if(isset($this->offers))
            $stripe_params["metadata"] = array('offers' => implode(",", $this->offers));

        try {
            $coupon = \Stripe\Coupon::create($stripe_params);

            // Create the coupon in the API
            if(isset($coupon->created) && $coupon->valid) {

                // Prepare the parameters
                $params = array(
                    'stripe_id' => $this->coupon_id,
                    'offers' => $this->offers
                );

                // Create the coupon in Stripe
                $endpoint = $this->strime_api_url."coupon/add";

                // Send the request
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('POST', $endpoint, [
                    'headers' => $this->headers,
                    'http_errors' => false,
                    'json' => $params
                ]);
                $curl_status = $json_response->getStatusCode();
                $response = json_decode( $json_response->getBody() );

                if($curl_status == 201)
                    return TRUE;
                else
                    return FALSE;
            }
            else {
                return TRUE;
            }
        }
        catch(\Stripe\Error\Coupon $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }
    }



    /**
     * @return boolean The result of the deletion
     */
    public function deleteCoupon()
    {
        // Delete the coupon
        try {
            \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

            $coupon_to_delete = \Stripe\Coupon::retrieve($this->coupon_id);
            $coupon_to_delete->delete();
        }
        catch(\Stripe\Error\Coupon $e) {

            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }

        // Delete the coupon in the API
        $endpoint = $this->strime_api_url."coupon/".$this->coupon_id."/delete";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('DELETE', $endpoint, [
            'headers' => $this->headers,
            'http_errors' => false
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        if($curl_status == 204)
            return TRUE;
        else
            return FALSE;
    }



    /**
     * @return array The list of coupon objects
     */
    public function getCoupons()
    {
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        // Get the list of coupons
        try {
            $coupons = \Stripe\Coupon::all(array("limit" => 100));
            return $coupons;
        }
        catch(\Stripe\Error\Coupon $e) {

            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }
    }



    /**
     * @return object The details of the coupon
     */
    public function getCoupon()
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        try {
            $coupon = \Stripe\Coupon::retrieve( $this->coupon );
            return $coupon;
        }
        catch(\Stripe\Error\Coupon $e) {

            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }
    }



    /**
     * @return boolean The result of the change
     */
    public function applyCoupon()
    {
        // Create a subscription in Stripe
        \Stripe\Stripe::setApiKey( $this->stripe_secret_key );

        try {
            $customer = \Stripe\Customer::retrieve( $this->stripe_customer_id );
            $subscription = \Stripe\Subscription::retrieve( $this->stripe_subscription_id );
            $subscription->coupon = $this->coupon;
            $subscription->save();
        }
        catch(\Stripe\Error\Subscription $e) {

            return FALSE;
        }
        catch(\Stripe\Error\Coupon $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\RateLimit $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\InvalidRequest $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Authentication $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            
            return FALSE;
        }
        catch (\Stripe\Error\Base $e) {
            
            return FALSE;
        }
        catch (Exception $e) {
            
            return FALSE;
        }

        // Add the coupon in the profile of the user in the API
        $params = array(
            "coupon" => $this->coupon
        );

        // Set the endpoint
        $endpoint = $this->strime_api_url."user/".$this->user_id."/edit";

        // Send the request
        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('PUT', $endpoint, [
            'headers' => $this->headers,
            'http_errors' => false,
            'json' => $params
        ]);
        $curl_status = $json_response->getStatusCode();
        $response = json_decode( $json_response->getBody() );

        if($curl_status == 200)
            return TRUE;
        else
            return FALSE;
    }
}