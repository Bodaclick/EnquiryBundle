Configuration Reference
=======================

These are the configuration parameters that can be used to configure the bundle behavior.
These parameters must be under a `bdk_enquiry` entry in the config application file:
``` yaml
bdk_enquiry:
   db_driver: orm #The data store used. Valid values are orm and mongodb. Required.
   user_class: Acme\DemoBundle\Entity\User #The entity/document class used to represent users. Required.
   db_prefix: bdk_ #A prefix to add to bundle tables/collections names, 
                   #so they don't collide with existing ones. Optional.
   logger: logger #The logger used to log bundle process/events, given as a service id. Optional
   responses:  #Used to specify custom response classes. Optional
       mapping:
          - { type: 'test', class: 'Acme\DemoBundle\Entity\MyResponse' } #The type must be unique
       inheritance: single #Only used in ORM, the table inheritance type used. 
                           #Valid values are single and joined. Optional. Default to single
```

The *type* in the responses mapping is used as column type in ORM and discriminator field in MongoDB.
