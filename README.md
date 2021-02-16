# Paguelo Facil - WHMCS Tokenisation Gateway Module

## Summary

This payment gateway allows you to integrate Paguelo Facil with the WHMCS platform. It allows you to set up production and testing keys. It also allows you to set up amount for authorizations when storing payment information as well as recurrent payments.
     
This is coded as a tokenised module. A tokenised module is a type of merchant gateway that accepts input of pay
method data locally and then exchanges it for a token that is stored locally
for future billing attempts.

Within WHMCS, sensitive payment data such as a card number is not stored
locally when a tokenisation module is used.

## Minimum Requirements

For the latest WHMCS minimum system requirements, please refer to
https://docs.whmcs.com/System_Requirements

## Installation

Just make sure the file `paguelofacilgateway.php` that is in modules/gateways is uploaded to modules/gateways folder of your WHMCS installation. You can do this via FTP or web interface if you have one.

## Implementation

THe code contains log calls before and after each actions. Visit WHMCS documentation pages to learn how to activate and review logs.

### Considerations

Addresses sent to Paguelo Facil are concatations of WHMCS Addres 1, City, State and Country fields. Due to 100 character limit by Paguelo Facil, the result of the concatenation is limited to the 100 chars.

Paguelo Facil naming for token id is codOper. It is this value the one we need to store in WHMCS for futures charges. Also note that you ened the codOper from the first transaction, do not update to use the one comming from following transactions. Lastly, we do need to store the codOper from all transactions as transaction ID, since is the information we need to process refunds.

Tax field is a required field for paguelo facil, but it is not always present in WHMCS, and it is not part of the use case for which this integration is firstly created. It is being left hardcoded to $0.

### Configuration

This integration has the following parameters:

* Código Web (CCLW) `apiCCLW`: CCLW Key found in your prod account.
* Access Token API `apiToken`: APi Token Key found in your prod account.
* Amount used to Authorizations `authAmount`: Amount used to authorizes credit cards without a payment.
* Test Mode `testMode`: Switch to enable the use of testing account.
* Código Web (CCLW) for Testing `apiCCLWTest`: CCLW Key found in your testing account.
* Access Token API for Testing `apiTokenTest`: APi Token Key found in your testing account.

### Functions

#### Store Remote

This functions implements CRUD for credit card information. It is only used when interacting with credit card information when there is no payment involved. Credit cards administration. 

There is no real deletion endpoint from Paguelo Facil son it is not implemented, it could just overwrite the codOper in WHMCS so we could not use that credit card anymore. 

The Update function could be just Adding a new one, storing that information, and applying proposed logic for deletion.

Creation is fully implemented as authorization endpoint in Paguelo Facil and using Authorization Amount setting.

#### Capture

This is the function that actually calls for captures or charges. It first if we have a token stored to be used for the capture.

If there is no token, we make an AUTH_CAPTURE call to Paguelo Facil. This allow us to first authorize the amount and prevent fraud. This call returns a codOper we can store as token for future transactions.

If there is a token, we make a RECURRRENT call to Paguelo Facitl. This will use the codOper code store as Token in WHMCS instead of credit card inforamtion. And will charge the amount to the card stored in Paguelo Facil.

#### Refund

As the name suggests, this function is in charge of refunds. This makes a REVERSE_CAPTURE call to Paguelo Facil using the codOper for the transaction in question.

## Useful Resources

* [Developer Resources](https://developers.whmcs.com/)
* [Hook Documentation](https://developers.whmcs.com/hooks/)
* [API Documentation](https://developers.whmcs.com/api/)
* [Paguelo Facil API](https://developers.paguelofacil.com/servicios-rest/autorizacion)

[WHMCS Limited](https://www.whmcs.com)

## Future Enhancements

* Support for tax
* Store Remote Update and Delete Triggers
* Client IP for security Checks