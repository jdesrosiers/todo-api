[![Heroku CI Status](https://ci-badges.herokuapp.com/pipelines/f9c4543a-2d69-4cef-bae9-f86cff384d41/master.svg)](https://dashboard.heroku.com/pipelines/f9c4543a-2d69-4cef-bae9-f86cff384d41/tests)

TODO API
========

The TODO API was created to showcase a Hypermedia API. This API is built using
the Resourceful framework to declaratively define the API with JSON Hyper-Schema
(draft-04). The TODO API targets the Jsonary browser. Included with Jsonary is a
tool that allows you to browse any JSON Hyper-Schema (draft-04) API visually
without writing any front-end code.

While [browsing the TODO API](http://json-browser.s3-website-us-west-1.amazonaws.com/?url=http%3A//hypermedia-todo.herokuapp.com/)
you'll notice that it feels more like browsing a web page than using an API.
That's the point. At any point, click the `describedby` link to view the JSON
Hyper-Schema that describes the resource you are viewing.

Also check out the code to see how simple developing an API with JSON
Hyper-Schema and Jsonary can be.

Development
------------

```bash
docker-compose up
```

### Tests

```bash
docker-compose run web vendor/bin/phpunit
```

Deployment
----------

The master branch is automatically deployed to: https://hypermedia-todo.herokuapp.com/

Commentary
----------

### JSON Hyper-Schema

JSON Hyper-Schema (draft-04) can do some pretty impressive things, but it's not
perfect. Newer drafts make some improvements, but also add complexity. One
change that was introduced since draft-04 makes a fundamental change to how
a Link Description Object (LDO) is interpreted (more details below). That
change is the main reason I'm sticking with draft-04 for now. The other *huge*
reason is the lack of Jsonary-like tools available for anthing other than
draft-04.

### Jsonary

Jsonary can do some pretty impressive things, but it's not perfect. It's pretty
old and has been abandoned for years. It's long overdue for a replacement, but
for now, even with all it's warts and bugs, it's all we have.

Jsonary was created by the original author of JSON Hyper-Schema and thus can be
considered a reference implementation for the draft-04 specification.

### The LDO Shift

In JSON Hyper-Schema, we define links using Link Description Objects (LDO).
In the beginning, an LDO represented one action a user can take. That action
could be to retrieve a related resource, update a resource, delete a resource,
or whatever else the API allows.

In draft-05, the original authors had all moved on and a new group picked up
where they left off. By draft-06 the concept of what an LDO represents had
shifted. LDOs no longer represented an action. Now they represent a relationship.
The actions you can take with an LDO became a little fuzzier. In order to choose
what action to take, the user needs to choose which HTTP method they want to
envoke on the link and can use whatever hints the LDO provides to construct their
request.

### Supporting PATCH with `encType`

Neither Jsonary (client) nor Resourceful (server) support PATCH. But, I wanted
to show in this API how it could be supported. Here's an example of the `edit`
link.

```json
{
    "rel": "edit",
    "href": "/task/{id}",
    "encType": [
        "application/json-patch+json",
        "application/merge-patch+json",
        "application/json"
    ]
}
```

Note that this array form of `encType` is not part of the draft-04 spec or any
other. It's a hypothetical solution to address this use case.

The `edit` relation indicates a resource that can be used to modify the resource.
How that edit is accomplished is the domain of the client, not the user. I, as a
user, don't choose to use PATCH or PUT. The client has certain capabilities and
the LDO describes the capabilities of the server. With those two pieces of
information the client chooses what to do.

The array form of `encType` works like the `Accept` HTTP header. It lists media
types the server supports in priority order. A generic client like Jsonary can
use that information to choose how to make the request. The client presents the
edit functionality to the user with a consistent and uniform interface. The user
just has to be concerned about providing the data, the client is concerned with
what HTTP method and media type is used to make the request.

The Jsonary client understands the `edit` relation to mean the resource is
editable. In Jsonary, the default for editing a resource is to use the `PUT`
method and the "application/json" `encType`. You could specify these, but it
would be superfluous. If Jsonary supported PATCH, it would know that an
`encType` of "application/json-patch+json" means that the edit should be encoded
as a JSON Patch and sent to the server using the `PATCH` method. In the example
above, Jsonary, which doesn't know how to encode a request as a JSON Patch, can
skip that option and move to the next supported `encType` until it finds one it
supports. When it gets to "application/json", it finds one it understands and
makes the standard `PUT` request.

If the server has some constraint that restricts it from using the usual PUT or
PATCH, you can always override the method with the `method` keyword to make a
`POST` request instead. The client can hide this detail from the user and
present them with the same uniform interface for editing a resource. The user
doesn't know or care that under the hood `POST` was used instead of `PUT`.

### Supporting Operations

It's not well defined how JSON Hyper-Schema can describe operations that can be
performed on a resource. For example, it might be nice for this API to define an
operation that marks a task completed. This operation could be defined with POST,
but POSTing is overkill when simply editing the field and modifying the
"completed" flag is sufficient. Jsonary doesn't support operations, but I think
with small changes, it could.

```json
{
    "rel": ["edit", "https://hypermedia-todo.herokuapp.com/rel/complete-task"],
    "href": "/task/{id}",
    "encType": [
        "application/json-patch+json",
        "application/merge-patch+json",
        "application/json"
    ],
    "schema": {
        "type": "object",
        "properties": {
            "completed": { "enum": [true] },
            "text": { "readOnly": true }
        }
    }
}
```

The first thing to note in this example is the array form of `rel`. This is
a proposed addition to JSON Hyper-Schema. In the example, the `rel`s mean the
same thing, but one is more specific that the other. We are editing a resource,
but more specifically, we are completing a task. The client can be generic
because it knows how to handle "edit" requests while also giving the user (which
is often a program) a consistent and unambiguous handle to work with.

The other interesting part of this example is the use of `rel` "edit" and
`schema` together. When using "edit", the target schema that our request must
conform to is the schema of the document being edited. But, including the
`schema` keyword also means that the request must conform to that schema.
Jsonary gives the `schema` keyword priority and overrides the behavior defined
for the "edit" relation. However, if requests had to validate against both the
current schema *and* the `schema` schema, we have a solution for supporting
operations! The `schema` keyword in the example would then serve to constrain
the general "edit" operation into a more specific "complete-task" operation.
