# Contact form endpoint

Do you want to have a simple, static website that the only dynamic thing is a contact form? You don't want to go serverless and/or use [formspree.io](formspree.io), AWS Lambda or similar? I got your back.

With this _Contact form endpoint_ all you need is a hosting with PHP (cheapest one will do) and a text editor to configure few things (or leave it with defaults).

What you also need is a basic knowledge of HTML to connect the form to the endpoint or some knowledge about XHR to take advantage of the JSON support to make your contact form work without a refresh!

## Features

- Working OOTB using `mail` and SQLite
- Simple YAML/INI configuration
  - SMTP credentials
  - HTML form fields names
  - Database creds (SQLite/MySQL; for antiflood)
  - JSON response messages
- Supporting both `text/html` and `application/json` requests
- Custom email templates

## Configuration

```yaml
strategy:
    smtp: # or just mail
        server: smtp.google.com
        port: 465
        username:
        password:
        authentication: ssl # no, ssl, tls

antiflood:
    quantity: 3
    period: 3600 # 1 hour

    db:
        dsn: sqlite:///antiflood.db
        table: antiflood

messages:
    # ...
```
