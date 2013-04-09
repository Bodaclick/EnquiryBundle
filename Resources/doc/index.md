BDKEnquiryBundle
================

The BDKEnquiryBundle is intended to be able to handle any form input and relate it to a class in your system.

Prerequisites
-------------

The bundle requires Symfony 2.1+

Installation
------------

See installation instructions [here](https://github.com/Bodaclick/BDKEnquiryBundle/blob/dev/Resources/doc/install.md)

Configuration Reference
-----------------------

See configuration reference [here](https://github.com/Bodaclick/BDKEnquiryBundle/blob/dev/Resources/doc/configuration.md)

Usage
-----

The bundles allow to create enquiries related to an entity/document and handle the responses to that enquiry.

The related entity/document must implement the interface `Bodaclick\BDKEnquiryBundle\Model\AboutInterface`.

The enquiry also can have a name associated to it, but this name must be unique.
So you'll have the posibility to access the enquiry by its id, its *about* entity/document associated, or by its name,
if specified.

To save the responses to an enquiry, there is a couple of *helper* classes, that belongs to the bundle's data model.
The most important one is the *Response* class. There is a default *Response* class defined in the bundle (depending
on the data store used it'll be an entity or a document), that extends the model abstract class
`Bodaclick\BDKEnquiryBundle\Model\Response`.

This default *Response* class has to fields to store a key and a value, and fits all generic uses. If you want to use
your own default *Response* class, you must extends `Bodaclick\BDKEnquiryBundle\Model\Response` abstract class and
redefine bdk.response_mapping.default parameter.

If you want to use a set of *Response* classes, all of them must extends the default one, and be configured in
configuration (see [configuration reference](https://github.com/Bodaclick/BDKEnquiryBundle/blob/dev/Resources/doc/configuration.md)).
If you're usin ORM, you can configure if all the responses are stored in the same table (single inheritance) or each
type in its own table (joined inheritance).

So, to save a response to an enquiry (or a list of responses), you have three choices:

- To use the `Answer` *helper* class, that it's an entity/document that admit a list of
`Bodaclick\BDKEnquiryBundle\Model\Response` objects
- To use an array of `Bodaclick\BDKEnquiryBundle\Model\Response` objects.
- To use a JSON representation of the responses (see below, in the controller section).

The last two options finally use the first one to save the data, so at the end we have a table or a collection which
stores the relationship between the enquiry, the responses, and the user that answer the enquiry. If we are using ORM,
the responses are stored in their own table, with a foreign key to the answer they belong. If we are using MongoDB,
there are only one collection where the answers are stored, and the responses are embedded documents of that answers.

###BDKEnquiryBundle Service

The bundle provides access to some of its features through actions in a controller, but if you want to use all the
capabilities you must use the service provided.

To get a reference to the service, you must use the service container provided by symfony.
For example in a controller's action you can have this:

``` php
$bdk_enquiry_service = $this->get('bdk.enquiry.service');
```

Once you have a reference to the service, you have a few methods you can use:

####getEnquiryFor and getEnquiriesFor

Both method admit an *about* object (an entity/document that implements `Bodaclick\BDKEnquiryBundle\Model\AboutInterface`
interface), and an optional format (JSON or XML), and returns the last enquiry associated to the object or the list of
all enquiries associated to that object (or null if none found). If format is not specified, it returns a *Enquiry*
object or an array of *Enquiry* objects. If format is specified, returns that object/array in a JSON or XML
representation (see below, in the controller section)

####getEnquiryByName

Returns an enquiry given its name (or its representation in JSON or XML format if the second parameter is used).


####getEnquiry

Returns an enquiry given its id (or its representation in JSON or XML format if the second parameter is used).

####saveEnquiry

Create an enquiry and associate the *about* object given in the first parameter. A *form name* and an *enquiry name*
can also be set with this method. The name must be unique, if used.

Returns the new enquiry created.

####deleteEnquiry

Delete an enquiry from the data store, given its name or its instance object.

####saveAnswer

Save responses given to an enquiry. The enquiry can be specified by name or its instance object. The responses must be
collected in a `Bodaclick\BDKEnquiryBundle\Model\Answer` object. An user instance can also be specified, and if so,
it will rewrite any user set in the *answer* object.

*Anonymous* responses are allowed, if the user parameter is set to null.

As `Bodaclick\BDKEnquiryBundle\Model\Answer` is an abstract class, the appropiated entity or document *answer* class in
the bundle model must be used. There is a helper method in the service, *createAnswer*, that returns an empty instance
of the appropiated class, based in the driver configured.

####saveResponses

As the previous method, save responses given to an enquiry, but this method allow more flexibility, as the responses
can be passed as an array of *Response* objects or as a JSON representation (see below, in the controller section).

At the end, an appropiated *answer* object is created and the *saveAnswer* method is used to save the responses.

A helper method exists to create an empty *Response* object depending on driver configured, *createResponse*.


###Controller

The bundle provide also a controller to handle two of the basic actions: get an enquiry and save the responses.

The creation of an enquiry only can be made through the service, as an *about* object must be specified.

To use the controller you must add the routing configuration, as stated in the [installation instructions](https://github.com/Bodaclick/BDKEnquiryBundle/blob/dev/Resources/doc/install.md).

There are trhee actions available:

####bdk_enquiry_get: /enquiry/{id}.{_format}

Used to get an enquiry by id.

If none found, the controller return a 404 HTTP code.

If found, returns a 200 HTTP code and a JSON or XML representation of the enquiry, depending on the format specified
(if none given, JSON is the default format). This JSON or XML format is the same returned by the service when format
is specified.

For example (indents added to better read):

In JSON format:

``` http
GET /enquiry/12344.JSON

HTTP/1.1 200 OK
Content-Type: application/json

{
    "enquiry":
        {
            "id":12344,
            "name":"test",
            "form":"testForm",
            "answers":[
                {
                  "answer":
                  {
                    "user":{"id":1,"username":"testUser"},
                    "responses":[
                        {"key":"question1","value":"response1"},
                        {"key":"question2","value":"response2"}
                    ]
                  }
                },
                {
                  "answer":
                  {
                    "user":{"id":2,"username":"testUser2"},
                    "responses":[
                        {"key":"question1","value":"response1"},
                        {"key":"question2","value":"response2"}
                    ]
                  }
                }
            ]
        }
    }
}

```

In XML format:

``` xml

<?xml version="1.0"?>
    <response>
        <enquiry>
            <id>1</id>
            <name>test</name>
            <form>testForm</form>
            <answers>
                <answer>
                    <responses>
                        <key>question1</key>
                        <value>response1</value>
                    </responses>
                </answer>
            </answers>
        </enquiry>
    </response>

```

This JSON response can be slightly diferent if there are no responses to the enquiry, or the responses are anonymous, etc.

####bdk_enquiry_get_by_name: /enquiry/by_name/{name}.{_format}

Same as previous but searching by name.

####bdk_enquiry_save_answer: /answer/save/{enquiryId}

Save the responses given to an enquiry, specified by the enquiryId parameter.

The connected user (if any) is associated to the responses.

If enquiryId not exists, a 404 HTTP code is returned.
If exists and the responses are saved, a 200 HTTP code and an empty response is returned.

Responses must be specified in JSON format in the request body, and the request mime type must
be `application/json`. This is the same format that must be used in the service, when using method *saveResponse*:

``` json
"answer":
      {
        "responses":[
            {"key":"question1","value":"response1"},
            {"key":"question2","value":"response2"}
        ]
      }
```

