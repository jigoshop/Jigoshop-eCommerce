# [Jigoshop](http://www.jigoshop.com)

Jigoshop eCommerce is a feature-packed eCommerce plugin built upon WordPress core functionality and modern coding standards ensuring excellent performance.

## Quick start (standard plugin package)

1. Install [Composer](http://getcomposer.org).
2. Clone the git repository: `git clone https://github.com/jigoshop/Jigoshop-eCommerce.git .`
3. Install required dependencies: `composer update --no-dev`
4. Install required Node packages: `npm install`
5. Run `node_modules/bower/bin/bower update` to update frontend libraries.
6. Run `node_modules/gulp/bin/gulp.js dist` to prepare the final package.
7. Jigoshop eCommerce plugin is ready, copy contents of the `dist` directory to Wordpress `wp-content/plugins/jigoshop-ecommerce` directory and activate the plugin.

## Build the dev package

1. Install [Composer](http://getcomposer.org).
2. Clone the git repository: `git clone https://github.com/jigoshop/Jigoshop-eCommerce.git .`
3. Install required dependencies: `composer update`
4. Install required Node packages: `npm install`
5. Run `node_modules/bower/bin/bower update` to update frontend libraries.
6. Run `node_modules/gulp/bin/gulp.js dev` to prepare the final package.

## Node version conflict

If you encounter any issues during Gulp phase, make sure your Node version is at least 10.6.0. You can install the required Node version using `n` utility: `sudo node_modules/n/bin/n 10.6.0`. Re-run Gulp after updating.

## Bug tracker

Have a bug? Please create an issue here on GitHub!

[https://github.com/jigoshop/jigoshop-ecommerce/issues](https://github.com/jigoshop/jigoshop-ecommerce/issues)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md)

Anyone and everyone is welcome to contribute. Jigoshop wouldn't be what it is today without the GitHub community.

There are several ways you can help out:

* Raising [issues](https://github.com/jigoshop/jigoshop-ecommerce/issues) on GitHub.
* Submitting bug fixes or offering new features / improvements by sending [pull requests](https://github.com/jigoshop/Jigoshop-eCommerce/pulls).
* Offering [your own translations](https://www.jigoshop.com/development/post/translations/).

## Changelog

See [CHANGELOG.md](CHANGELOG.md)

## Project information

* Web: http://www.jigoshop.com
* Docs: http://www.jigoshop.com/documentation/
* Twitter: http://twitter.com/jigoshop
* Source: http://github.com/jigoshop/jigoshop-ecommerce