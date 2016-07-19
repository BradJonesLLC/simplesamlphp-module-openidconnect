<?php

$config = array(
  'openid-connect' => array(
      'openidconnect:Connect',
      'client_id'           => '*****-*****-*****-*****-*****',
      'client_secret'       => '*****-*****-*****-*****-*****',
      'token_endpoint'      => 'https://auth.dataporten.no/oauth/token',          //Example url
      'user_info_endpoint'  => 'https://auth.dataporten.no/openid/userinfo',      //Example url
      'auth_endpoint'       => 'https://auth.dataporten.no/oauth/authorization',  //Example url
      'sslcapath'           => '/etc/ssl/certs',
  ),

);
