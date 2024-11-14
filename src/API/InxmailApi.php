<?php

/**
 * Inxmail Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL-3.0-or-later
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\InxmailBundle\API;

use Contao\System;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class InxmailApi {

    CONST BASE_URI = 'https://api.inxmail.com/%s/rest/v1';

    CONST STANDARD_CONTACT_FIELDS = ['EMAIL'];


    /**
     * @var string
     */
    private $customer;

    /**
     * @var string
     */
    private $clientID;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var Symfony\Contracts\HttpClient\HttpClientInterface
     */
    protected $client;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;


    public function __construct( HttpClientInterface $client, LoggerInterface $logger ) {

        $this->client = $client;
        $this->logger = $logger;

        $this->customer = null;
        $this->clientID = null;
        $this->secret = null;
    }


    /**
     * Set the API credentials that will be used
     *
     * @param string $customer
     * @param string $clientID
     * @param string $secret
     *
     * @return self
     */
    public function setApiCredentials( string $customer, string $clientID, string $secret ): self {

        $this->customer = $customer;
        $this->clientID = $clientID;
        $this->secret = $secret;

        return $this;
    }


    /**
     * Get configured contacts custom fields
     *
     * @return string
     *
     * @throws RuntimeException If no API credentails were set
     */
    public function getBaseUri(): string {

        if( $this->customer === null ) {
            $this->logger->error('Inxmail base uri cannot be generated without customer.');
            throw new RuntimeException('Inxmail base uri cannot be generated without customer');
        }

        return sprintf(self::BASE_URI, $this->customer);
    }


    /**
     * Create an event subscription
     *
     * @param array $data
     * @param array $settings
     *
     * @return null|array
     */
    public function createEventSubscription( array $data, array $settings ): ?array {

        $email = null;
        $attributes = [];

        foreach( $data as $key => $value ) {

            if( $key === 'EMAIL' ) {
                $email = $value;
            } else {
                $attributes[$key] = $value;
            }
        }

        if( empty($email) ) {
            $this->logger->error('Inxmail API cannot create event subscription as of missing email.');
            return null;
        }

        if( !empty($attributes) ) {

            $attributeConfig = $this->getRecipientAttributes();

            $attributeConfig = array_combine(array_column($attributeConfig, 'name'), $attributeConfig);

            if( !empty($attributes) ) {
                foreach( $attributes as $field => $value ) {

                    if( !array_key_exists($field, $attributeConfig) ) {
                        continue;
                    }

                    $attributes[$field] = $this->formatAttributeValue($value, $attributeConfig[$field]);
                }
            }
        }

        $inxmailData = $settings;
        $inxmailData['email'] = $email;
        $inxmailData['attributes'] = $attributes;

        $createResult = $this->send('POST', '/events/subscriptions', $inxmailData);

        if( $createResult['status'] === 200 ) {
            return $createResult['body'];
        }

        return null;
    }


    /**
     * Get the recipeient attributes
     *
     * @return array
     */
    public function getRecipientAttributes(): array {

        $attrResult = $this->send('GET', '/attributes');

        if( $attrResult['status'] !== 200 ) {
            return [];
        }

        $attributes = [];

        if( !empty($attrResult['body']['_embedded']['inx:attributes']) ) {
            foreach( $attrResult['body']['_embedded']['inx:attributes'] as $attr ) {
                $attributes[] = [
                    'id' => $attr['id'],
                    'name' => $attr['name'],
                    'type' => $attr['type'],
                    'maxLength' => $attr['maxLength'] ?? null,
                ];
            }
        }

        return $attributes;
    }


    /**
     * Format the given value for the given attribute config from Inxmail
     *
     * @param mixed $value
     * @param array $config
     *
     * @return mixed
     */
    protected function formatAttributeValue( $value, array $config ) {

        $type = strtoupper($config['type']);

        if( $type === "TEXT" ) {
            return strval($value);
        } else if( $type === "DATE_AND_TIME" ) {
            return date('Y-m-d\TH:i:s\Z', $value);
        } else if( $type === "DATE_ONLY" ) {
            return date('Y-m-d', $value);
        } else if( $type === "TIME_ONLY" ) {
            return date('H:i:s\Z', $value);
        } else if( $type === "INTEGER" ) {
            return intval($value);
        } else if( $type === "FLOATING_POINT_NUMBER" ) {
            return floatval($value);
        } else if( $type === "BOOLEAN" ) {
            return !empty($value);
        }

        return $value;
    }


    /**
     * Send request to Inxmail
     *
     * @param string $method
     * @param string $url
     * @param array  $data
     *
     * @return array
     *
     * @throws RuntimeException If no API credentails were set
     */
    private function send( string $method, string $url, array $data=[] ): array {

        if( empty($this->customer) || $this->clientID === null || $this->secret === null ) {
            $this->logger->error('Inxmail API call cannot be performed without credentials.');
            throw new RuntimeException('Inxmail credentials not set');
        }

        $oOptions = new HttpOptions();
        $oOptions->setHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        $oOptions->setAuthBasic($this->clientID, $this->secret);

        if( !empty($data) ) {
            $content = json_encode($data);
            $oOptions->setBody($content);
        }

        $aOptions = [];
        $aOptions = $oOptions->toArray();

        $base = $this->getBaseUri();

        $response = null;
        $response = $this->client->request($method, $base.$url, $aOptions);

        $return = [
            'url' => $base.$url
        ,   'status' => $response->getStatusCode()
        ];

        // log error status in system log
        $headers = $response->getHeaders(false);
        if( $return['status'] >= 400 || in_array('application/problem+json', $headers['content-type']) ) {
            $this->logger->error('Inxmail API return status '. $return['status'] .' with body: '.$response->getContent(false));
        }

        $return['body'] = json_decode($response->getContent(false), true);

        return $return;
    }
}
