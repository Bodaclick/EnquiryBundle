Installation instructions
=========================

These are the steps you must follow to install BDKEnquiryBundle:

1. Download BDKEnquiryBundle using composer
-------------------------------------------

Add BDKEnquiryBundle in your composer.json:

```js
{
    "require": {
        "bodaclick/bdk-enquiry-bundle": "*"
    }
}
```

Tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update bodaclick/bdk-enquiry-bundle
```

2. Enable the bundle
--------------------

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Bodaclick\BDKEnquiryBundle\BDKEnquiryBundle(),
    );
}
```

3. Configure the BDKEnquiryBundle
---------------------------------

Add the following configuration to your `config.yml` file according to which type of datastore you are using.

``` yaml
# app/config/config.yml
bdk_enquiry:
    db_driver: orm # other valid value is 'mongodb'
    user_class: Acme\UserBundle\Entity\User
```

Only this two configuration values are required.
See [Configuration Reference](https://github.com/Bodaclick/BDKEnquiryBundle/blob/dev/Resources/doc/configuration.md)
for a list of all configuration parameters

If you're not using auto mapping in your doctrine configuration, you must add the following to the doctrine section in
your  `config.yml`:

``` yaml
# app/config/config.yml
doctrine:
    orm:
        entity_managers:
            default:
                mappings:
                    BDKEnquiryBundle: ~
```

Notes:
- If you are using the short syntax for the ORM configuration, the mappings key is directly under `orm:`
- If you are using several entity managers, take care to register the entities for the right ones.
- The mapping for MongoDB is similar, but under the `doctrine_mongodb` section


4. Import BDKEnquiryBundle routing file (optional)
---------------------------------------------------

Only if you plan to use the controller provided by the bundle, you must import the BDKEnquiryBundle routing file
in you application routing file.

Example:

``` yaml
# app/config/routing.yml
bdk_enquiry:
    resource: "@BDKEnquiryBundle/Resources/config/rounting.xml"
```

5. Update your database schema
------------------------------

As the bundle provide a few entities (for ORM) or documents (for MongoDB), and also because they can be related to your
own entity/document classes, you must update your database schema.

For ORM run the following command.

``` bash
$ php app/console doctrine:schema:update --force
```

For MongoDB you can run the following command to create the indexes.

``` bash
$ php app/console doctrine:mongodb:schema:create --index
```


