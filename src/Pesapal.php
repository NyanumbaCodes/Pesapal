<?php

use GuzzleHttp\Client as Http;


class Pesapal
{
    public $base_url;
    public $callback_url;
    public $http;

    public function __construct()
    {
        $this->base_url = config('pesapal.env')=='live'
            ? "https://pay.pesapal.com/v3"
            : "https://cybqa.pesapal.com/pesapalv3";
        $this->http = new Http(['base_uri' => $this->base_url]);
        $this->callback_url = config('pesapal.callback_url');
    }

    public function getAccessToken()
    {
        $response = $this->http->post('Auth/RequestToken', [
            'auth' => [config('pesapal.consumer_key'), config('pesapal.consumer_secret')]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function createPaymentRequest(array $data)
    {
        $token = $this->getAccessToken();

        $data = [
            "id" => "AA1122-3344ZZ",
            "currency" => config('pesapal.currency'),
            "amount" => 100.00,
            "description" => "Payment description goes here",
            "redirect_mode" => "",
            "notification_id" => "fe078e53-78da-4a83-aa89-e7ded5c456e6",
            "branch" => "Store Name - HQ",
            "billing_address" => [
                "email_address" => "john.doe@example.com",
                "phone_number" => "0723xxxxxx",
                "country_code" => "KE",
                "first_name" => "John",
                "middle_name" => "",
                "last_name" => "Doe",
                "line_1" => "Pesapal Limited",
                "line_2" => "",
                "city" => "",
                "state" => "",
                "postal_code" => "",
                "zip_code" => ""
            ]
        ];

        $response = $this->http->post('api/Transactions/SubmitOrderRequest', [
            'headers' => ['Authorization' => 'Bearer ' . $token['token']],
            'json' => array_merge($data, ['callback_url' => $this->callback_url])
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function getPaymentStatus($transactionId)
    {
        $token = $this->getAccessToken();

        $response = $this->http->get("api/Transactions/GetTransactionStatus?orderTrackingId={$transactionId}", [
            'headers' => ['Authorization' => 'Bearer ' . $token['token']]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
