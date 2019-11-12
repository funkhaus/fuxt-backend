# stackhaus backend

A [WordPress](https://wordpress.org/) template that turns it into a headless CMS API powered by [WP Graph QL](https://www.wpgraphql.com/). We use it to power a Vue/Nuxt frontend. The boilerplate for that is called [Stackhaus](https://github.com/funkhaus/stackhaus). Built by [Funkhaus](http://funkhaus.us/).

## Install

1.  Install theme
1.  Install required plugins as prompted
1.  Re-save Permalinks
1.  If using ACF, it is strongly recommended to import the ACF fields in the `.json` file from `/acf/` directory.

## Wordpress config

### Permalinks

GrapghQL won't work with default Permalink setting. You must have some form of pretty permalinks enabled.

## Graph QL Extras

### Emailing

This theme includes a `sendEmail` mutation. Simply uncomment `gql_register_email_mutation` action in `/functions/gql-functions.php`.

The mutation maps to the [wp_mail() function](https://developer.wordpress.org/reference/functions/wp_mail/), so please read that for a description of required inputs.

The mutation comes with a basic form of anti-spam protection. The input `trap` must equal the same as the `clientMutationId`. With GraphQL, the `clientMutationId` is a unique identify and you can set it to whatever you like.

So for example:

```
mutation MyMutation {
  sendEmail(
      input: {
          clientMutationId: "12345",
          to: ["example@example.com"],
          message: "Email subject here",
          subject: "Message body here",
          trap: "12345",
          headers: ["From: site@example.com"]
       }
    ) {
        to
        message
        subject
        sent
  }
}
```

### Preview/Draft/Private

TODO Document how this works with WP-GraphQL-CORS plugin

### Search

Improved search coming soon!

### Next/Previous pages

TODO Document how these extra WP-GQL queries work.

## TODO

-   Document how to use shortcodes
