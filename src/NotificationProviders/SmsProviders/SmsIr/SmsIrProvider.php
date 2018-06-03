<?php

namespace Asanbar\Notifier\NotificationProviders\SmsProviders\SmsIr;

use Asanbar\Notifier\NotificationProviders\SmsProviders\SmsAbstract;
use Asanbar\Notifier\Traits\RestConnector;
use Illuminate\Support\Facades\Log;

class SmsIrProvider extends SmsAbstract
{
    use RestConnector;

    public $send_uri = "http://restfulsms.com/api/MessageSend";
    public $token_uri = "http://restfulsms.com/api/Token";

    public function getToken()
    {
        $request = [
            "UserApiKey" => config("notifier.sms.smsir.api_key"),
            "SecretKey" => config("notifier.sms.smsir.secret_key"),
            "System" => "Notifier",
        ];

        $response = $this->post(
            $this->token_uri,
            ["json" => $request]
        );

        $response = json_decode($response->getBody(),true);

        if(array_key_exists("TokenKey", $response)) {
            return $response["TokenKey"];
        } else {
            Log::error("Notifier: Could not get token from SMS.ir");

            return false;
        }
    }

    public function send(string $message, array $numbers, string $datetime = null)
    {
        $body = [
            "Messages" => [$message],
            "MobileNumbers" => $numbers,
            "LineNumber" => config("notifier.sms.smsir.line_number"),
        ];

        if($datetime) {
            $body["SendDateTime"] = $datetime;
        }

        $headers = [
            "x-sms-ir-secure-token" => $this->getToken()
        ];

        $response = $this->post(
            $this->send_uri,
            [
                "json" => $body,
                "headers" => $headers
            ]
        );

        $response = json_decode($response->getBody()->getContents(),true);

        return $response["IsSuccessful"] ?? false;
    }
}