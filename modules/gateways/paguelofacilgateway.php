<?php
/**
 * WHMCS Sample Tokenisation Gateway Module
 *
 * This sample module demonstrates how to create a merchant gateway module
 * that accepts input of pay method data locally and then exchanges it for
 * a token that is stored locally for future billing attempts.
 *
 * As with all modules, within the module itself, all functions must be
 * prefixed with the module filename, followed by an underscore, and then
 * the function name. For this example file, the filename is "paguelofacilgateway"
 * and therefore all functions begin "paguelofacilgateway_".
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) WHMCS Limited 2019
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function paguelofacilgateway_MetaData()
{
    return [
        'DisplayName' => 'Paguelo Facil',
        'APIVersion' => '1.0', // Use API Version 1.1
    ];
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/configuration/
 *
 * @return array
 */
function paguelofacilgateway_config()
{
    return [
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'Paguelo Facil Gateway Module',
        ],
        'apiCCLW' => [
            'FriendlyName' => 'Código Web (CCLW)',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Ingresa to Código Web',
        ],
        'apiToken' => [
            'FriendlyName' => 'Access Token API',
            'Type' => 'password',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Ingresa tu Access Token API',
        ],
        'authAmount' => [
            'FriendlyName' => 'Amount used to Authorizations',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Ingresa tu Monto',
        ],
        'testMode' => [
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
        ],
        'apiCCLWTest' => [
            'FriendlyName' => 'Código Web (CCLW) for Testing',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Ingresa to Código Web for Testing',
        ],
        'apiTokenTest' => [
            'FriendlyName' => 'Access Token API for Testing',
            'Type' => 'password',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Ingresa tu Access Token API for Testing',
        ],
    ];
}

/**
 * Store payment details.
 *
 * Called when a new pay method is added or an existing pay method is
 * requested to be updated or deleted.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/tokenised-remote-storage/
 *
 * @return array
 */
function paguelofacilgateway_storeremote($params)
{
    logModuleCall('Paguelo Facil Gateway', 'Store Remote Start', $params, array(), array(), array());
    // Gateway Configuration Parameters
    $testMode = $params['testMode'];

    if ( $testMode ) {
        $apiCCLW = $params['apiCCLWTest'];
        $apiToken = $params['apiTokenTest'];
        $apiUrl = 'https://sandbox.paguelofacil.com/';
    } else {
        $apiCCLW = $params['apiCCLW'];
        $apiToken = $params['apiToken'];
        $apiUrl = 'https://secure.paguelofacil.com/';
    }

    
    // Store Remote Parameters
    $action = $params['action']; // One of either 'create', 'update' or 'delete'
    $remoteGatewayToken = $params['gatewayid'];
    $cardType = $params['cardtype']; // Card Type
    $cardNumber = $params['cardnum']; // Credit Card Number
    $cardExpiry = $params['cardexp']; // Card Expiry Date (format: mmyy)
    $cardStart = $params['cardstart']; // Card Start Date (format: mmyy)
    $cardIssueNum = $params['cardissuenum']; // Card Issue Number
    $cardCvv = $params['cardcvv']; // Card Verification Value
    $amount = (float)$params['authAmount'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];
    $address = substr($address1.' '.$city.' '.$state.' '.$country,0,100);

    switch ($action) {
        case 'create':
            // Invoked when a new card is added.
            $urlConfig = $apiUrl."rest/processTx/AUTH";
            $tax=0.0;
            $description='Tokenization';

            $data = array(
                "cclw" =>  $apiCCLW ,
                "amount" => $amount,
                "taxAmount" => $tax,
                "email" => $email,
                "phone" => $phone,
                "address" => $address,
                "concept" => $description,
                "description" => $description,
                // "ipCheck" => $ip,
                // "lang" => 'ES', //EN
                // "additionalData"=> ["sessionKount": "123as777dfsdf898"],
                // "customFieldValues"  => [["id"=>"idOrder","nameOrLabel"=>"Nro de Orden","value"=>"OD-234567"],
                //                     ["id"=>"idUser","nameOrLabel"=>"User","value"=>"24"],
                //                     ["id"=>"idTx","nameOrLabel"=>"Txtx","value"=>"678643"],
                //                         ["id"=>"reference","nameOrLabel"=>"Referencia","value"=>"6754"],
                //                     ["id"=>"activo","nameOrLabel"=>"estado","value"=>"true"]],
                "cardInformation" => array(
                    "cardNumber" => $cardNumber,
                    "expMonth" => substr($cardExpiry, 0, 2),
                    "expYear" => substr($cardExpiry, 2, 2),
                    "cvv" => $cardCvv,
                    "firstName" => $firstname,
                    "lastName" => $lastname,
                    "cardType" => $cardType,
                ),
            );

            logModuleCall('Paguelo Facil Gateway', 'Store Remote Create', $urlConfig, array(), $data, array());
            

            $json=json_encode($data);

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, $urlConfig);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','authorization:'.$apiToken));
            curl_setopt($ch,CURLOPT_POSTFIELDS,$json);

            $result = curl_exec($ch);
            $response = json_decode($result, true);

            logModuleCall('Paguelo Facil Gateway', 'Store Remote Create End', $data, array(), $response, array());
            

            // Perform API call to store the provided card details and generate a token.
            // Sample response data:

            if ($response['success']) {
                return [
                    // 'success' if successful, otherwise 'error' for failure
                    'status' => 'success',
                    // Data to be recorded in the gateway log - can be a string or array
                    'rawdata' => $response,
                    // The token that should be stored in WHMCS for recurring payments
                    'gatewayid' => $response['data']['codOper'],
                ];
            }

            return [
                // 'success' if successful, otherwise 'error' for failure
                'status' => 'error',
                // Data to be recorded in the gateway log - can be a string or array
                'rawdata' => $response,
            ];

            break;
        case 'update':
//             // Invoked when an existing card is updated.
//             $postfields = [
//                 'token' => $remoteGatewayToken,
//                 'card_type' => $cardType,
//                 'card_number' => $cardNumber,
//                 'card_expiry_month' => substr($cardExpiry, 0, 2),
//                 'card_expiry_year' => substr($cardExpiry, 2, 2),
//                 'card_cvv' => $cardCvv,
//                 'card_holder_name' => $firstname . ' ' . $lastname,
//                 'card_holder_address1' => $address1,
//                 'card_holder_address2' => $address2,
//                 'card_holder_city' => $city,
//                 'card_holder_state' => $state,
//                 'card_holder_zip' => $postcode,
//                 'card_holder_country' => $country,
//             ];
                return [
                        // 'success' if successful, otherwise 'error' for failure
                        'status' => 'success',
                        // Data to be recorded in the gateway log - can be a string or array
                        'rawdata' => 'Not implemented yet',
                        // The token to be stored if it has changed
                        // 'gatewayid' => $response['token'],
                    ];
                break;
        case 'delete':
//             // Invoked when an existing card is requested to be deleted.
//             $postfields = [
//                 'token' => $remoteGatewayToken,
//             ];
                return [
                    // 'success' if successful, otherwise 'error' for failure
                    'status' => 'success',
                    // Data to be recorded in the gateway log - can be a string or array
                    'rawdata' => 'Not implemented yet',
                    // The token to be stored if it has changed
                    // 'gatewayid' => $response['token'],
                ];
                break;
    }
}

/**
 * Capture payment.
 *
 * Called when a payment is requested to be processed and captured.
 *
 * This function may receive pay method data instead of a token when a
 * payment is attempted using a pay method that was originally created and
 * stored locally within WHMCS using something other than this token
 * module, and therefore it should be able to accomodate captures based
 * both on a token as well as a pay method data.
 *
 * The CVV number parameter will only be present for card holder present
 * transactions. Automated recurring capture attempts will not provide it.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/merchant-gateway/
 *
 * @return array
 */
function paguelofacilgateway_capture($params)
{
    logModuleCall('Paguelo Facil Gateway', 'Capture Start', $params, array(), array(), array());
    // Gateway Configuration Parameters
    $testMode = $params['testMode'];

    if ( $testMode ) {
        $apiCCLW = $params['apiCCLWTest'];
        $apiToken = $params['apiTokenTest'];
        $apiUrl = 'https://sandbox.paguelofacil.com/';
    } else {
        $apiCCLW = $params['apiCCLW'];
        $apiToken = $params['apiToken'];
        $apiUrl = 'https://secure.paguelofacil.com/';
    }

    // Capture Parameters
    $remoteGatewayToken = $params['gatewayid'];
    $cardType = $params['cardtype']; // Card Type
    $cardNumber = $params['cardnum']; // Credit Card Number
    $cardExpiry = $params['cardexp']; // Card Expiry Date (format: mmyy)
    $cardStart = $params['cardstart']; // Card Start Date (format: mmyy)
    $cardIssueNum = $params['cardissuenum']; // Card Issue Number
    $cardCvv = $params['cccvv']; // Card Verification Value

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params['description'];
    $amount = $params['amount'];
    $tax = 0.00; // Required for paguelo facil but not given by whmcs
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];
    $address = substr($address1.' '.$city.' '.$state.' '.$country,0,100);

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    if (!$remoteGatewayToken) {
        // If there is no token yet, it indicates this capture is being
        // attempted using an existing locally stored card. Create a new
        // token and then attempt capture.
        $urlConfig = $apiUrl.'rest/processTx/AUTH_CAPTURE';

        $data = array(
            "cclw" =>  $apiCCLW ,
            "amount" => $amount,
            "taxAmount" => $tax,
            "email" => $email,
            "phone" => $phone,
            "address" => $address,
            "concept" => $description,
            "description" => $description,
            // "ipCheck" => '100.23.45.51',
            // "lang" => 'ES', //EN
            "customFieldValues"  => [["id"=>"idInvoiceId","nameOrLabel"=>"Invoice Id","value"=>$invoiceId],
                                ["id"=>"idFirstName","nameOrLabel"=>"First Name","value"=>$firstname],
                                  ["id"=>"idLastName","nameOrLabel"=>"Last Name","value"=>$lastname],
                                    ["id"=>"idAddress2","nameOrLabel"=>"Adress Line 2","value"=>$address2],
                                ["id"=>"idPostCode","nameOrLabel"=>"Postal Code","value"=>$postcode],
                                ["id"=>"idCompanyName","nameOrLabel"=>"Company Name","value"=>$companyName],
                                ["id"=>"idWhmcsVersion","nameOrLabel"=>"WHMCS Version","value"=>$whmcsVersion]],
            "cardInformation" => array(
                "cardNumber" => $cardNumber,
                "expMonth" => substr($cardExpiry, 0, 2),
                "expYear" => substr($cardExpiry, 2, 2),
                "cvv" => $cardCvv,
                "firstName" => $firstname,
                "lastName" => $lastname,
                "cardType" => $cardType,
            )
        );

        logModuleCall('Paguelo Facil Gateway', 'Capture Requesting Token', $urlConfig, array(), $data, array());

        $json=json_encode($data);

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $urlConfig);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','authorization:'.$apiToken));
        curl_setopt($ch,CURLOPT_POSTFIELDS,$json);

        $result = curl_exec($ch);
        $response = json_decode($result, true);

        logModuleCall('Paguelo Facil Gateway', 'Capture Requesting Token End', $data, array(), $result, array());

        // Perform API call to store the provided card details and generate a token.
        if ($response['success']) {
            return [
                // 'success' if successful, otherwise 'declined', 'error' for failure
                'status' => 'success',
                // The unique transaction id for the payment
                'transid' => $response['data']['codOper'],
                // Optional fee amount for the transaction
                // 'fee' => $response['fee'],
                // Return only if the token has updated or changed
                'gatewayid' => $response['data']['codOper'],
                // Data to be recorded in the gateway log - can be a string or array
                'rawdata' => $response,
            ];
        } else {
            return [
                // 'success' if successful, otherwise 'error' for failure
                'status' => 'error',
                // Data to be recorded in the gateway log - can be a string or array
                'rawdata' => $response,
            ];
        }
    }

    logModuleCall('Paguelo Facil Gateway', 'Capture Using Stored Token', array(), array(), array(), array());
    
    $urlConfig = $apiUrl.'rest/processTx/RECURRENT';
    $data = array(
        "cclw" =>  $apiCCLW ,
        "amount" => $amount,
        "taxAmount" => $tax,
        "email" => $email,
        "phone" => $phone,
        "address" => $address,
        "concept" => $description,
        "description" => $description,
        "codOper" => $remoteGatewayToken,
        // "ipCheck" => $ip,
        // "lang" => 'ES', //EN
        // "additionalData"=> ["sessionKount": "123as777dfsdf898"],
        "customFieldValues"  => [["id"=>"idInvoiceId","nameOrLabel"=>"Invoice Id","value"=>$invoiceId],
                                ["id"=>"idFirstName","nameOrLabel"=>"First Name","value"=>$firstname],
                                  ["id"=>"idLastName","nameOrLabel"=>"Last Name","value"=>$lastname],
                                    ["id"=>"idAddress2","nameOrLabel"=>"Adress Line 2","value"=>$address2],
                                ["id"=>"idPostCode","nameOrLabel"=>"Postal Code","value"=>$postcode],
                                ["id"=>"idCompanyName","nameOrLabel"=>"Company Name","value"=>$companyName],
                                ["id"=>"idWhmcsVersion","nameOrLabel"=>"WHMCS Version","value"=>$whmcsVersion]],
    );

    logModuleCall('Paguelo Facil Gateway', 'Capture Using Stored Token', $urlConfig, array(), $data, array());
    
    $json=json_encode($data);

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $urlConfig);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','authorization:'.$apiToken));
    curl_setopt($ch,CURLOPT_POSTFIELDS,$json);

    $result = curl_exec($ch);
    $response = json_decode($result, true);

    // Perform API call to initiate capture.
    // Sample response data:
    logModuleCall('Paguelo Facil Gateway', 'Capture Using Stored Token End', $data, array(), $result, array());
    
    if ($response['success']) {
        return [
            // 'success' if successful, otherwise 'declined', 'error' for failure
            'status' => 'success',
            // The unique transaction id for the payment
            'transid' => $response['data']['codOper'],
            // Optional fee amount for the transaction
            // 'fee' => $response['fee'],
            // Return only if the token has updated or changed
            // 'gatewayid' => $response['data']['codOper'],
            // Data to be recorded in the gateway log - can be a string or array
            'rawdata' => $response,
        ];
    }

    return [
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status' => 'declined',
        // For declines, a decline reason can optionally be returned
        // 'declinereason' => $response['decline_reason'],
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $response,
    ];
}

/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array
 */
function paguelofacilgateway_refund($params)
{
    logModuleCall('Paguelo Facil Gateway', 'Refund Start', $params, array(), array(), array());
    // Gateway Configuration Parameters
    $testMode = $params['testMode'];

    if ( $testMode ) {
        $apiCCLW = $params['apiCCLWTest'];
        $apiToken = $params['apiTokenTest'];
        $apiUrl = 'https://sandbox.paguelofacil.com/';
    } else {
        $apiCCLW = $params['apiCCLW'];
        $apiToken = $params['apiToken'];
        $apiUrl = 'https://secure.paguelofacil.com/';
    }

    // Refund Parameters
    $transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'];
    $currencyCode = $params['currency'];
    $remoteGatewayToken = $params['gatewayid'];
    $description = 'Refund: '.$transactionIdToRefund.' amount: $'.$refundAmount;

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    $urlConfig = $apiUrl.'rest/processTx/REVERSE_CAPTURE';

    $data = array(
        "cclw" =>  $apiCCLW ,
        "amount" => $refundAmount,
        "description" => $description,
        "codOper" => $transactionIdToRefund,
        // "lang" => 'ES', //EN
        "customFieldValues"  => [["id"=>"idInvoiceId","nameOrLabel"=>"Invoice Id","value"=>$invoiceId],
            ["id"=>"idFirstName","nameOrLabel"=>"First Name","value"=>$firstname],
            ["id"=>"idLastName","nameOrLabel"=>"Last Name","value"=>$lastname],
                ["id"=>"idAddress2","nameOrLabel"=>"Adress Line 2","value"=>$address2],
            ["id"=>"idPostCode","nameOrLabel"=>"Postal Code","value"=>$postcode],
            ["id"=>"idCompanyName","nameOrLabel"=>"Company Name","value"=>$companyName],
            ["id"=>"idWhmcsVersion","nameOrLabel"=>"WHMCS Version","value"=>$whmcsVersion]],
    );

    logModuleCall('Paguelo Facil Gateway', 'Refund', $urlConfig, array(), $data, array());

    $json=json_encode($data);

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $urlConfig);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','authorization:'.$apiToken));
    curl_setopt($ch,CURLOPT_POSTFIELDS,$json);

    $result = curl_exec($ch);
    $response = json_decode($result, true);

    logModuleCall('Paguelo Facil Gateway', 'Refund End', $data, array(), $response, array());


    // Perform API call to initiate a refund.
    // Sample response data:
    if ($response['success']) {
        return [
            // 'success' if successful, otherwise 'declined', 'error' for failure
            'status' => 'success',
            // Data to be recorded in the gateway log - can be a string or array
            'rawdata' => $response,
            // Unique Transaction ID for the refund transaction
            'transid' => $response['data']['codOper'],
            // Optional fee amount for the fee value refunded
            // 'fee' => $response['fee'],
        ];
    }

    return [
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status' => 'error',
        // For declines, a decline reason can optionally be returned
        // 'declinereason' => $response['decline_reason'],
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $response,
    ];
    
}
