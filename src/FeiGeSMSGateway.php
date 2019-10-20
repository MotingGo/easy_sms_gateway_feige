<?php


namespace MotingGo\EasySms\Gateway;

use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

class FeiGeSMSGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_HOST = 'http://api.feige.ee/SmsService/Template';

    /**
     * Send a short message.
     *
     * @param \Overtrue\EasySms\Contracts\PhoneNumberInterface $to
     * @param \Overtrue\EasySms\Contracts\MessageInterface $message
     * @param \Overtrue\EasySms\Support\Config $config
     *
     * @return array
     * @throws GatewayErrorException
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $params = [
            'Account' => $config->get('account'),
            'Pwd' => $config->get('pwd'),
            'Content' => $message->getContent($this),
            'Mobile' => !\is_null($to->getIDDCode()) ? strval($to->getZeroPrefixedNumber()) : $to->getNumber(),
            'SignId' => $config->get('sign_id'),
            'TemplateId' => $config->get('verification_code'),
        ];

        $result = $this->post(self::ENDPOINT_HOST, $params, ['Content-Type' => 'application/json']);

        if (0 !== $result['Code']) {
            throw new GatewayErrorException($result['Message'], $result['Code'], $result);
        }

        return $result;
    }
}
