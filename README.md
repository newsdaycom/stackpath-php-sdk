# StackPath PHP SDK

This package was written to make connections to the StackPath API easier in PHP. We will maintain it for as long as makes sense for us and had the primary goal of adding purge request logic to it, which we've done.

Other abstraction methods will be forthcoming as we require it.

This package is open source and we are open to pull requests for new features.

## Install

You can easily add this to your project by running

    composer require newsdaycom/stackpath-php-sdk

## Configuration

For simplicity sake, we encourage you to set two environment variables, `STACKPATH_ID` and `STACKPATH_SECRET`. If you cannot do this you can also provide the client_id and client_secret as parameters during instantiation. The OAuth handshake happens on instantiation and the bearer token is provided in all requests by default.

## Use

To use Stackpath you must first instantiate the class and include your client_id, client_secret and stack_id;

    $sp = new \StackPath\StackPath("9ad4bdfc-77ec-41bb-ac95-da137d837742", $CLIENT_ID, $CLIENT_SECRET);

If you don't provide the $CLIENT_ID or $CLIENT_SECRET the class will fallback on your environment variables.

There are request methods set up by default, `GET`, `POST`, `DELETE`, and `PUT`. These are all shorthand methods for the `request` method which handles all of the logic for Authorization, payload and returning the response as an object. The requests in this application are powered by Guzzle.

The URLs you provide should begin with what comes after `https://gateway.stackpath.com` in your API calls. The gateway is filled in automatically. Absolute URLs can also be used.

For example, requesting a token can be done as follows:

    $this->post("identity/v1/oauth2/token", ["json" => [
              "client_id" => $this->creds["client_id"],
              "client_secret" => $this->creds["client_secret"],
              "grant_type" => "client_credentials"
            ]])->access_token

Note, the response will come back as an object so you can treat the method like one. Most of the payloads you send to stackpath can begin with

    ["json" => [ARRAY OF DATA]]

## Docs

At the time this README was written, documentation can be found in PHPDoc comment notation in src/StackPath/StackPath.php. If you have questions not answered here, reach out via GitHub ticket.

Enjoy!
