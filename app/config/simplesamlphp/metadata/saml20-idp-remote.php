<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote
 */

$metadata['http://idp.surfuni.org/simplesaml/saml2/idp/metadata.php'] = array(
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'http://idp.surfuni.org/simplesaml/saml2/idp/metadata.php',
    'SingleSignOnService' =>
        array (
            0 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'Location' => 'http://idp.surfuni.org/simplesaml/saml2/idp/SSOService.php',
                ),
        ),
    'SingleLogoutService' =>
        array (
            0 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'Location' => 'http://idp.surfuni.org/simplesaml/saml2/idp/SingleLogoutService.php',
                ),
        ),
    'certData' => 'MIIDhTCCAm2gAwIBAgIJANTpviRF7cZtMA0GCSqGSIb3DQEBCwUAMFkxCzAJBgNVBAYTAk5MMRMwEQYDVQQIDApTb21lLVN0YXRlMRAwDgYDVQQHDAdVdHJlY2h0MQ0wCwYDVQQKDARTVVJGMRQwEgYDVQQDDAtzdXJmdW5pLm9yZzAeFw0xNTA2MTYxMzQzMjJaFw0yNTA2MTUxMzQzMjJaMFkxCzAJBgNVBAYTAk5MMRMwEQYDVQQIDApTb21lLVN0YXRlMRAwDgYDVQQHDAdVdHJlY2h0MQ0wCwYDVQQKDARTVVJGMRQwEgYDVQQDDAtzdXJmdW5pLm9yZzCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBANlxBVi4qBfSeSIU2DRlund0wfmbvYvY7YWM1ASEnFlly1pj0PX1nmI0LksWFLhmuWqqX7919WR5G4s5KIGjzOFUnZERqyVWshAfmsAbPGqxeEctaTBGFKG4I3CV2gAqpTvO2L/gzJrkpk9VR84mfxadIfQnyPV0yNcHA0AkBR+1U13qo5+hyPWDWB63MzoCc4/VoWPYdKbyznkVshMsDGD7jevW80CtqJnThtVB2/lJyK2ZYsFOE4ZC9m9zQWsCaZcxJSRbTVlJsn7SGeCDwJIXeQqTDw7ZydRhJnhNBiILewc7qL0IMXToRHn1yVLCjXRLJN8R2QYXzPpD2XrYYRUCAwEAAaNQME4wHQYDVR0OBBYEFAEAdgpyNGTsc8gqZ64WBtBYRQdyMB8GA1UdIwQYMBaAFAEAdgpyNGTsc8gqZ64WBtBYRQdyMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBAC/Raw+ztMwfbcwVE1iMiShr0YTqYNGc/ypJo4CboWOB8bmuVkxyDUC5UqQqyNm86tY7ivk7ekJtWb/FAE89eT1YaKtTlDLavG3+YYv1iIBjXqS4KvRKSNlnIpWD0wNCBa/Iys5y6TUowHsbVJOsleKSJ8ET4p0+9FZCTwawjXNCc4p6PlBw4ujCPLiJIpbSvlPbBXpLT6ihf51TCECuLRQgOiF4bMqNDmI9e+7x17/lULY4PZejSu0RWe1ce3FjhbaE8iQQh9pSlM3UnCPTSYOnAfNEioZAi7alGOqY2rmxMewozfFOOOiL3OOiSk5wI4PZV9cL3u2W85BGJ6vRfLI=',
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
);

$metadata['https://engine.surfconext.nl/authentication/idp/metadata'] = array(
    'SingleSignOnService'  => 'https://engine.surfconext.nl/authentication/idp/single-sign-on',
    'certFingerprint'      => array('A0:D8:C8:5A:BC:64:DC:A3:71:92:77:08:99:AF:72:50:8D:B3:89:77'),
);
