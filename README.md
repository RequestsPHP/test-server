Requests for PHP Test Server
============================

This repository is a simplistic test server that is being used by the [Requests for PHP library](https://github.com/WordPress/Requests/) to run its automated testing suite.

Installation
------------

This package is not meant to be used as a stand-alone server. Rather, it is being used by the CI testing workflows in the [Requests for PHP library](https://github.com/WordPress/Requests/).

To install it in your local environment anyway, you can use the following command:

```bash
composer install
```

Note: This package requires **Composer v2.2+** to work correctly.

Contribute
----------

1. Check for open issues or open a new issue for a feature request or a bug.
2. Fork [the repository][] on Github to start making your changes to the
    `develop` branch (or branch off of it).
3. Write one or more tests which show that the bug was fixed or that the feature works as expected.
4. Send in a pull request.

If you have questions while working on your contribution and you use Slack, there is
a [#core-http-api] channel available in the [WordPress Slack] in which contributions can be discussed.

[the repository]: https://github.com/RequestsPHP/test-server/
[#core-http-api]: https://wordpress.slack.com/archives/C02BBE29V42
[WordPress Slack]: https://make.wordpress.org/chat/
