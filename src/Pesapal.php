<?php

namespace NyanumbaCodes\Pesapal;

use Exception;
use Illuminate\Support\Facades\Http;


class Pesapal
{
    public $base_url;
    public $ipn_url;
    public $consumer_key;
    public $consumer_secret;
    public $callback_url;
    public $merchant;
    public function __construct()
    {
        $this->base_url = config('pesapal.env') == 'live'
            ? "https://pay.pesapal.com/v3"
            : "https://cybqa.pesapal.com/pesapalv3";
        $this->callback_url = config('pesapal.callback_url');
        $this->ipn_url = config('pesapal.ipn_url');
        $this->merchant = config('pesapal.merchant');
        $this->consumer_key = config('pesapal.consumer_key');
        $this->consumer_secret = config('pesapal.consumer_secret');
    }

    /**
     * Random Reference Generator used to generate unique IDs
     * @param mixed $prefix
     * @param mixed $length
     * @return string
     */
    public function random_reference($prefix = 'PESAPAL', $length = 10)
    {
        $keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        $prefix = $this->merchant;
        // Generate a random string of the desired length
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }

        // Append the current timestamp in milliseconds for uniqueness
        $timestamp = round(microtime(true) * 1000);

        return $prefix . '-' . $timestamp . '-' . $str;
    }

    /**
     * Get the Authentication Token from Pesapal
     * @return mixed
     */
    public function getAccessToken()
    {
        $response = Http::post("{$this->base_url}/api/Auth/RequestToken", [
            "consumer_key" => $this->consumer_key,
            "consumer_secret" => $this->consumer_secret
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Access Token Failed: ' . $response->body());
    }

    /**
     * Register Pesapal IPN URL for  Instant Payment Notification
     * @param mixed $type
     * @throws \Exception
     * @return mixed
     */
    public function registerIpnUrl($type = "GET")
    {

        $token = $this->getAccessToken();
        $data = [
            "url" => $this->ipn_url,
            "ipn_notification_type" => $type
        ];
        $response = Http::withToken($token['token'])
            ->post("{$this->base_url}/api/URLSetup/RegisterIPN", $data);


        return $response->json();
    }

    /**
     * Allows you to fetch all registered IPN URLs for a particular Pesapal merchant account.
     * @return mixed
     */
    public function getIpnList()
    {
        $token = $this->getAccessToken();
        $response = Http::withToken($token['token'])
            ->get("{$this->base_url}/api/URLSetup/GetIpnList");

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Get IPN List Failed: ' . $response->body());
    }


    /**
     * Summary of submitOrderRequest
     * @param mixed $amount
     * @param mixed $description
     * @param array $billing_address
     * @param mixed $notificationId
     * @param mixed $redirectMode
     * @return mixed
     */
    public function submitOrderRequest($amount, $description, array $billing_address, $notificationId, $redirectMode = "", $account_number = null, array $subscription_details = [])
    {

        $data = [
            "id" => $this->random_reference(),
            "currency" => config('pesapal.currency'),
            "amount" => $amount,
            'callback_url' => $this->callback_url,
            "description" => $description,
            "redirect_mode" => $redirectMode,
            "notification_id" => $notificationId,
            "branch" => "Store Name - HQ",
            "billing_address" => $billing_address
        ];

        if ($account_number != null) {
            array_merge($data, ['account_number' => $account_number]);
            if (empty($subscription_details)) {
                throw new Exception("Subscription Details need to accompany the Account Number");
            }
        }
        if (!empty($subscription_details)) {
            array_merge($data, ['subscription_details' => $subscription_details]);
        }

        $token = $this->getAccessToken();
        $response = Http::withToken($token['token'])
            ->post("{$this->base_url}/api/Transactions/SubmitOrderRequest", $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Order Request Failed: ' . $response->body());
    }

    /**
     * Getting the Transaction Status
     * @param mixed $transactionId
     * @return mixed
     */
    public function getTransactionStatus($transactionId)
    {
        $token = $this->getAccessToken();
        $response = Http::withToken($token['token'])->get("{$this->base_url}/api/Transactions/GetTransactionStatus?orderTrackingId={$transactionId}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Order Request Failed: ' . $response->body());
    }

    /**
     * Request Refund
     * @return mixed
     */
    public function refundRequest($confirmation_code, $amount, $username, $remarks)
    {
        $data = [
            "confirmation_code" => $confirmation_code,
            "amount" => $amount,
            "username" => $username,
            "remarks" => $remarks
        ];
        $token = $this->getAccessToken();
        $response = Http::withToken($token['token'])->post("{$this->base_url}/api/Transactions/RefundRequest", $data);

        if ($response->successful()) {
            return $response->json();
        }
        throw new Exception('Refund Request Failed: ' . $response->body());
    }

    public function orderCancellation($trackingId)
    {
        $data = [
            "order_tracking_id" => $trackingId
        ];
        $token = $this->getAccessToken();
        $response = Http::withToken($token['token'])->post("{$this->base_url}/api/Transactions/CancelOrder", $data);

        if ($response->successful()) {
            return $response->json();
        }
        throw new Exception('Order Cancellation Failed: ' . $response->body());
    }
}
