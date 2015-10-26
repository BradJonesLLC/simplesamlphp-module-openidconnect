# simplesamlphp-openid-connect
OpenID Connect module for SimpleSAMLphp

## Installation

1. Run the post-Install script, `Drupal\saml_idp\Installer::postInstall()`, by
  adding it to your project's `composer.json` file under `post-install-cmd`.
  If you don't do so, you will need to manually install and enable the "openidconnect"
  module in simplesamlphp.