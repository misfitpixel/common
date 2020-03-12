<?php


namespace MisfitPixel\Service;


use MisfitPixel\Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OauthService
 * @package MisfitPixel\Service
 */
class OauthService
{
    /**
     * @param string $token
     * @return array
     */
    public function getTokenDetails(string $token): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            sprintf('Authorization: Bearer %s', $token)
        ]);

        curl_setopt($ch, CURLOPT_URL, 'http://accounts.mtgbracket.com/oauth/validate');

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $result = (curl_exec($ch));
        $errorCode = curl_errno($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        switch($errorCode) {
            case CURLE_OK:
                break;

            case CURLE_OPERATION_TIMEOUTED:
                throw new Exception\TimeoutException('Could not connect to accounts service');

                break;

            default:
                throw new Exception\UnknownErrorException('Error encountered during api request');

                break;
        }

        switch($info['http_code']){
            case Response::HTTP_OK:
            case Response::HTTP_ACCEPTED:
            case Response::HTTP_NO_CONTENT:
                break;

            case Response::HTTP_FORBIDDEN:
                throw new Exception\ForbiddenException();

            default:
                throw new Exception\UnknownErrorException();
        }

        return ($result != null) ? json_decode($result, true) : null;
    }
}