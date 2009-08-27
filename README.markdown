Google Voice Dialer
===================

This is purely a stop-gap measure until they release a proper API.

Basic Usage
-----------

Dialing a number is simple

    $gv = new GoogleVoice('<username>', '<password>');
    $gv->call($your_phone_number, $their_phone_number);

Sending a text is easy

    $gv->sms($sometext, $their_phone_number);

License
-------

This code is released under the MIT Open Source License. Feel free to do whatever you want with it.
