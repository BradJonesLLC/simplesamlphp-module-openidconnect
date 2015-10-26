# simplesamlphp-openid-connect
OpenID Connect module for SimpleSAMLphp

## Installation

1. Run the post-Install script, `Drupal\saml_idp\Installer::postInstall()`, by
  adding it to your project's `composer.json` file under `post-install-cmd`.
  If you don't do so, you will need to manually install and enable the "openidconnect"
  module in simplesamlphp.

## Developer notes/Known issues

- This library uses a fork of the OpenID Connect package by ivan-novakov, until
  [this pull request](https://github.com/ivan-novakov/php-openid-connect-client/pull/11)
  is merged upstream.
