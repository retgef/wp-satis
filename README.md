WP Satis Generator
========

This project was created due to the lack of support for Composer in WordPress. This interactive CLI tool generates a Packagist-style Composer repository of all WordPress tags and branches using Satis. This repository can then be hosted on your CDN or behind your firewall for reliable builds.

Usage
----
Run `./build` or `php build` from the command line and follow the prompts.

Composer Installers
----
Each WordPress package is marked with the type 'wordpress-core'. An installer dependency was left out purposely as some developers prefer to define their own installer plugin. [WordPress Core Installer](https://github.com/johnpbloch/wordpress-core-installer) is compatible with this package type.
