Junos NETCONF XML Management Protocol
-------------------------------------
This is a Junos-specific NETCONF implementation.  It attempts to adhere to Juniper's proprietary NETCONF extension
(http://www.juniper.net/techpubs/en_US/junos14.2/information-products/pathway-pages/netconf-guide/netconf.html)

Dependencies
-------------
 - PHP >= 5.4
 - phpseclib/phpseclib (https://github.com/phpseclib/phpseclib)
 - lamoni/netconf (https://github.com/lamoni/netconf)

Considerations
--------------
 - Implement force-synchronize for <commit-configuration> calls?
 - Implement the more obscure capabilities of Junos XML <get-configuration>?

Examples
--------

Initializing JunosNetConf and then executing an operational command
------------------------------------------------------------------
```php
$junos = new JunosNetConf(
    "192.168.0.100",
    new NetConfAuthPassword(
        [
            "username" => "lamoni",
            "password" => "phpsux"
        ]
    )
);

echo $junos->operationalCommandText('show interfaces terse');
```

Committing set-format configuration changes
------------------------------------------------------------------
```
$config = Array('set interfaces ge-0/0/0 unit 0 description "test"');
$junos->loadConfigurationSet($config);
$junos->commitConfiguration();
```
