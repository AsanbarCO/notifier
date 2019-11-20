<?php

namespace Asanbar\Notifier\NotificationService\Strategies\NotificationProviders\SmsProviders\Sms0098;

use Asanbar\Notifier\NotificationService\Strategies\NotificationProviders\SmsProviders\SmsAbstract;
use Asanbar\Notifier\Traits\RestConnector;
use Carbon\Carbon;

class Sms0098Provider extends SmsAbstract
{
    use RestConnector;

    public $from;
    private $sendURI = "http://www.0098sms.com/sendsmslink.aspx";
    private $domain  = "0098";
    private $username;
    private $password;

    public function __construct()
    {
        $this->from     = config("notifier.sms.sms0098.from");
        $this->username = config("notifier.sms.sms0098.username");
        $this->password = config("notifier.sms.sms0098.password");
    }

    /**
     * @param string $message
     * @param array $numbers
     * @param Carbon|null $expireAt
     * @return array
     */
    public function send(string $message, array $numbers, ?Carbon $expireAt = null): array
    {
        $query = [
            "FROM"     => $this->from,
            "TEXT"     => $message,
            "USERNAME" => $this->username,
            "PASSWORD" => $this->password,
            "DOMAIN"   => $this->domain,
        ];

        $result = [
            'all_success'   => true,
            'success_count' => 0,
        ];

        foreach ($numbers as $number) {
            $query['TO'] = $number;

            $response = $this->get(
                $this->sendURI,
                $query
            );

            $status = $this->isSuccess($response);

            $result['detail'][$number] = [
                'success'  => $status,
                'response' => (method_exists($response, 'getContent') ? $response->getContent() : null),
                'provider' => '0098',
            ];

            if ($status) {
                $result['success_count']++;
            } else {
                $result['all_success'] = false;
            }
        }

        return $result;
    }

    /**
     * receive sms from provider function
     *
     * @return mixed[]
     */
    protected function receiveMessages(): array
    {
        //TODO: should implement
        return [
            'all'    => 0,
            'status' => false,
        ];
    }

    private function isSuccess($response): bool
    {
        $status = trim(explode('<!DOCTYPE html PUBLIC', $response->getBody()->getContents())[0]); //Becarefull never cast it

        return $response->getStatusCode() == 200
            && (
            (
                $status == 0
            )

        );

    }
}
