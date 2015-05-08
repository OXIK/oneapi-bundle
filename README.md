# OneApiBundle for Symfony2 0.0.1
## What is OneApiBundle?
It's a small symfony bundle that acts like a bridge between [infobip OneApi](https://github.com/infobip/oneapi-php) and your symfony project.

Simple wrap the OneApi objects into your services or controllers.

The next documentation is ported from OneApi and adapted to the use in your symfony 2 project.

## Installation

Add this to your `composer.json` file.

```json
    {
        "require": {
            ...
            "infobip/oneapi": "dev-master",
            "oxik/oneapibundle": "0.0.1"
        }
    }
```

And to `AppKernel.php`

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new Oxik\OneApiBundle\OxikOneApiBundle(),
        );
    }
}
```

Add your username and password to `config.yml' file.

```yml
# OneApiBundle Configuration
oxik_one_api:
    username: USERNAME
    password: PASSWORD
    baseUrl: ~
```

## Use of static::functions 

You still can use the static functions of OneApi, load `infobin/class` namespace manually.

## Basic messaging example

First include the OneApiBundle `Wrapper` service into your code and retrieve a new instance of `SmsClient`.

```php
$serviceWrapper = $this->get('oxik_one_api.wrapper');
$smsClient = $serviceWrapper->getService('smsClient', true);
```

The first argument is the class to initialize from OneApi, the second one are the arguments of the class (if true then username and password will be passed to the function, insert an array instead to set your own custom arguments).

An exception will be thrown if your *username* and/or *password* are incorrect.

Prepare the message:

```php
$smsMessage = $serviceWrapper->getModel('SMSRequest');
$smsMessage->senderAddress = SENDER_ADDRESS;
$smsMessage->address = DESTINATION_ADDRESS;
$smsMessage->message = 'Hello world';
```

Send the message:

```php
$smsMessageSendResult = $smsClient->sendSMS($smsMessage);
```

Later you can query for the delivery status of the message:

```php
// You can use $clientCorrelator or $smsMessageSendResult as an method call argument here:
$smsMessageStatus = $smsClient->queryDeliveryStatus($smsMessageSendResult);
$deliveryStatus = $smsMessageStatus->deliveryInfo[0]->deliveryStatus;

echo 'Success:', $smsMessageStatus->isSuccess(), "\n";
echo 'Status:', $deliveryStatus, "\n";
if( ! $smsMessageStatus->isSuccess()) {
    echo 'Message id:', $smsMessageStatus->exception->messageId, "\n";
    echo 'Text:', $smsMessageStatus->exception->text, "\n";
    echo 'Variables:', $smsMessageStatus->exception->variables, "\n";
}
```

Possible statuses are: **DeliveredToTerminal**, **DeliveryUncertain**, **DeliveryImpossible**, **MessageWaiting** and **DeliveredToNetwork**.

##Messaging with notification push example

Same as with the standard messaging example, but when preparing your message:

```php
$smsMessage = $serviceWrapper->getModel('SMSRequest');
$smsMessage->senderAddress = SENDER_ADDRESS;
$smsMessage->address = DESTINATION_ADDRESS;
$smsMessage->message = 'Hello world';
$smsMessage->notifyURL = NOTIFY_URL;
```

When the delivery notification is pushed to your server as a HTTP POST request, you must process the body of the message with the following code:

```php
$result = $smsClient->queryDeliveryStatus($send);
// Process $result here, e.g. just save it to a file:
$f = fopen(FILE_NAME, 'w');
fwrite($f, "\n-------------------------------------\n");
fwrite($f, 'status: ' . $result->deliveryInfo->deliveryStatus . "\n") ;
fwrite($f, 'address: ' . $result->deliveryInfo->address . "\n");
fwrite($f, 'messageId: ' . $result->deliveryInfo->messageId . "\n");
fwrite($f, 'clientCorrelator: '. $result->deliveryInfo->clientCorrelator . "\n");
fwrite($f, 'callback data: ' . $result->callbackData . "\n");
fwrite($f, "\n-------------------------------------\n");
fclose($f);
```

##Sending message with special characters example

If you want to send message with special characters, this is how you prepare your message:

```php
$smsMessage = $serviceWrapper->getModel('SMSRequest');
$smsMessage->senderAddress = SENDER_ADDRESS;
$smsMessage->address = DESTINATION_ADDRESS;
$smsMessage->message = MESSAGE_TEXT;

$language = $serviceWrapper->getModel('Language');

//specific language code
$language->languageCode = LANGUAGE_CODE;

//use locking shift table for specific language ('false' or 'true') 
$language->useLockingShift = USE_LOCKING_SHIFT;

//use single shift table for specific language ('false' or 'true')
$language->useSingleShift = USE_SINGLE_SHIFT;

$smsMessage->language = $language;
```

Currently supported languages (with their language codes) are: `Spanish - "SP"`, `Portuguese - "PT"`, `Turkish - "TR"`.

##Number Context example

Initialize and login the data connection client:

```php
$client = $serviceWrapper->getService('DataConnectionProfileClient', true);
``` 

Retrieve the roaming status (Number Context):

```php
$response = $client->retrieveRoamingStatus(DESTINATION_ADDRESS);
echo 'Number context result: \n<br>';
echo 'servingMccMnc: ', $response->servingMccMnc,'\n<br>';
echo 'address: ', $response->address,'\n<br>';
echo 'currentRoaming: ', $response->currentRoaming,'\n<br>';
echo 'resourceURL: ', $response->resourceURL,'\n<br>';
echo 'retrievalStatus: ', $response->retrievalStatus,'\n<br>';
echo 'callbackData: ', $response->callbackData,'\n<br>';
echo 'extendedData: ', $response->extendedData,'\n<br>';
echo 'IMSI: ', $response->extendedData->imsi,'\n<br>';
echo 'destinationAddres: ', $response->extendedData->destinationAddress,'\n<br>';
echo 'originalNetworkPrefix: ', $response->extendedData->originalNetworkPrefix,'\n<br>';
echo 'portedNetworkPrefix: ', $response->extendedData->portedNetworkPrefix,'\n<br>';
```

##Retrieve inbound messages example

With the existing sms client (see the basic messaging example to see how to start it):

```php
$inboundMessages = $smsClient->retrieveInboundMessages();

foreach($inboundMessages->inboundSMSMessage as $message) {
    echo $message->dateTime;
    echo $message->destinationAddress;
    echo $message->messageId;
    echo $message->message;
    echo $message->resourceURL;
    echo $message->senderAddress;
}
```

##Social invites sms example

If you have Social Invites application registered and configured ([tutorial](http://developer.infobip.com/getting-started/tutorials/social-invite)), you can send invitations.

First initialize the social invites client using your username and password:

```php
$socinv = $serviceWrapper->getService('SocialInviteClient', true);
```

Prepare the social invitation:

```php
$siReq = $serviceWrapper->getModel('SocialInviteRequest');
$siReq->senderAddress = SENDER_ADDRESS;
$siReq->recipients = DESTINATION_ADDRESS;
$siReq->messageKey = SOCIAL_INVITES_MESSAGE_KEY;
```

Send the message:

```php
$siResult = $socinv->sendInvite($siReq, SOCIAL_INVITES_APP_SECRET);
```

Later you can query for the delivery status of the social invite message:

```php
// You can use $siResult->sendSmsResponse->bulkId as an argument here:
$smsMessageStatus = $smsClient->queryDeliveryStatus($siResult->sendSmsResponse->bulkId);
$deliveryStatus = $smsMessageStatus->deliveryInfo[0]->deliveryStatus;

echo 'Success:', $smsMessageStatus->isSuccess(), "\n";
echo 'Status:', $deliveryStatus, "\n";
if( ! $smsMessageStatus->isSuccess()) {
    echo 'Message id:', $smsMessageStatus->exception->messageId, "\n";
    echo 'Text:', $smsMessageStatus->exception->text, "\n";
    echo 'Variables:', $smsMessageStatus->exception->variables, "\n";
}
```

##License

This library (and OneApi PHP) is licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0)