Networq PHP Library
================

This is a PHP library for [Networq](https://github.com/networq).

It allows you to load Networq graphs, including it's dependencies, types and nodes.

Once the graph is loaded, you can query it and iterate through it's data.

## Terms:

### Node:

All data in Networq is a "Node". A node has a name (i.e. `mario`). Every node is part of a package (i.e. `acme:example`). The node name and the package name combined give a Fully Qualified Node Name (FQNN) (i.e. `acme:example:mario`).

Other than the name/fqnn, a node doesn't have any properties of it's own. A Node can be tagged with one or more Types, which give the node it's available properties. For example, tagging the `acme:example:mario` node with the `acme:example:character` type allows you to specify the `debut` field for Mario, linking it to the game that Mario debuted in.

### Package

A "Package" is a reusable set of Types and Nodes. Packages can be shared and live in their own Git Repository.

A Package defines Types (in the package's `types/` directory) using YAML files.

A Package can optionally define a set of `Nodes`. Nodes in packages are therefor also reusable and lets you share reference data or standardized lists (like countries, operating systems, etc).

A Package can optionally define `Widgets`, which are simple HTML templates (in the `templates/` directory) which get injected into the User Interface when a user is viewing a Node of a given Type.

A Package always has a `package.yaml` file in the root of it's repository.
The `package.yaml` file defines the Package's name, description, license etc. And lists an optional set
of dependencies: other packages that this package depends on.

### Types

A "Type" defines what properties a Node can have. A type has a name (i.e. `vegetable`) and is part of a package (i.e. `acme:food`). Combining the Type name and Package Name gives you a Fully Qualified Type Name (FQTN) (i.e. `acme:food:vegetable`).

Types are defined by creating `.yaml` files in the `types/` directory of a package. They are automatically loaded when the package is loaded.

Types optionally define a list of Fields for the type. Only nodes tagged with this type will have the listed fields.

A Field can be either a `string` (simple text), or a FQTN. When you specify a FQTN for a Field, you
can only assign nodes that are tagged with this type.

A Field can be a single value (default), or a list of values (by appending [] to the end of the Field type definition).

### Tags

A "Tag" is simply the link between a Node and a Type. One Node can contain multiple Tags.

## Inspiration / related technology

* https://en.wikipedia.org/wiki/Multiple_inheritance
* https://en.wikipedia.org/wiki/Trait_(computer_programming)
* https://en.wikipedia.org/wiki/Entity-component-system
* https://en.wikipedia.org/wiki/Lightweight_Directory_Access_Protocol

## Getting started

### Installation

    $ git clone https://github.com/networq/networq-php
    $ cd networq-php
    $ composer install

### Examples

After installation you should be able to run the simple examples in `examples/`

    $ php example/list-packages.php
    Packages: 1
    - example:games

    $ php example/list-types.php
    Types: 5
    * example:games:platform
        - manufacturer (string)
    * example:games:character
        - debut (example:games:game)
        - games (example:games:game[])
    * example:games:game
        - publisher (string)
        - platform (example:games:platform)
        - characters (example:games:character[])
        - urls (example:games:url[])
    * example:games:url
        - target (string)
    * example:games:base
        - name (string)
        - image (string)
        - description (string)

    $ php example/mario.php
    example:games:mario
      example:games:base
          name=Mario
          image=https://mario.nintendo.com/assets/img/home/intro/mario-pose2.png
          description=
      example:games:character
          debut=*example:games:smb
          games=[*example:games:smb] [*example:games:dk]
    Plays in games: 2
    * example:games:smb (Super Mario Bros) Nintendo Entertainment System
    * example:games:dk (Donkey Kong) Nintendo Entertainment System

    $ php example/nintendo-games.php
    Nintendo games: 3
    * example:games:smb (Super Mario Bros)
    * example:games:dk (Donkey Kong)
    * example:games:loz (The Legend of Zelda)

## Web based viewer

Please check https://github.com/networq/networq-web for an implementation of a web-based viewer.

## Testing

See `.circleci/config.yml` how to run tests. At the moment: run `./vendor/bin/phpunit --configuration phpunit.xml tests`.

## License

MIT. Please refer to the [license file](LICENSE) for details.

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).

Btw, we're hiring!
