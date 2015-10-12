# Zend Service Paypal Adapters #

Currently, on the NVP Adapter is the only available adapter. However, design will allow possible integration of future adapters (such as for the currently existing SOAP interface).

We've decided not to develop a SOAP adapter for the simple reason that there is no real benefit of using the SOAP interface over the NVP interface.  The SOAP interface was developed for languages that work very will with SOAP natively, such as .NET and Java. For these languages, It is relatively simple and quick to integrate with the SOAP interface.  However, since you will not be interacting directly with the Paypal API, you are compeltely transparent to the transport format (as you should be!).

# API Method Invocation #

## Calling an API Method ##

API Methods are invocated by calling them as a function to your Zend\_Service\_Paypal\_Adapter instance.

```
   $service->DoDirectPayment( $data );
```

Behind the scenes, The adapter will attempt to load an API class matching the method you requested.  If found, this class will be offloaded with the work of performing some logic, calling the PayPal API, and returning the response.  If not found, the adapter will simply call the API with all of the field values that you specified.

## How ZSP handles method calls ##

The ZSP component will not attempt to emulate method invocation by "rewriting" each method into a PHP-like method call.  However, convenience data classes will be provided to simplify end user code for each API method call.

What this means is that the user can supply parameters to each method invocation such as:

```
$cc = new Zend_Service_Paypal_Data_CreditCard();
$billingInfo = new Zend_Service_Paypap_Data_Address();
$data = array(
   'PaymentAction' => 'Sale',
   'IPAddress'     => $_SERVER['REMOTE_ADDR'],
   'ReturnFMFDetails' => false,
   'Amt'           => 10.00
);

$service->DoDirectPayment( $data, $cc, $billingInfo );


```

**Each argument passed to an API method call MUST either be an associative array of named fields and values, or a Zend\_Service\_PayPal\_Data\_AbstractData instance!**  You can also pass them within an array:

```
$cc = new Zend_Service_Paypal_Data_CreditCard();
$billingInfo = new Zend_Service_Paypap_Data_Address();

$data = array(
   'PaymentAction' => 'Sale',
   'IPAddress'     => $_SERVER['REMOTE_ADDR'],
   'ReturnFMFDetails' => false,
   'Amt'           => 10.00,
   $cc,
   $billingInfo
);

$service->DoDirectPayment( $data );
```

However, on the backed, Zend\_Service\_Paypal will simply break these data classes out into individual fields, and pass them to the API method.  Because of this, you are not forced to use the Data classes.  The following is also possible:

```

$service->DoDirectPayment(array(
   'PaymentAction' => 'Sale',
   'IPAddress'     => $_SERVER['REMOTE_ADDR'],
   'ReturnFMFDetails' => false,
   'Amt'           => 25.00,
   'CreditCardType' => 'MasterCard',
   'Acct'          => 1234123412341234,
   //... And So On ...
));

```

## The ZSP Response Object ##

## When to use ZSP\_Api_Methods ##_

The Zend\_Service\_PayPal\_Api_Methods are used to perform additional validating and filtering on a per method basis.  A class will be created for a method when there is additional validation or logic that must take place before the field data is sent to Paypal.  For example, in some API methods, certain optional fields become required under certain conditions.  The generic code for validating and sending data to the API is not aware of conditional requirements, but each individual API Method Class can be._

As a further example, the Callback API Method does not require a shipping address, but some of the shipping address fields become required if any of them are present.