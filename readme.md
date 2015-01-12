Composer Configuration File Helper
==================================

This provides tools to make maintaining the references and requirements easier.

Options:
--------

list            - Lists all packages that are defined in the config file.
                
                Usage:
                    [--simple | --full] [<filter>]

up              - Increments the package's version number by 1.
                
                Usage:
                    <name> [--major | --minor | --fix]

down            - Decrements the package's version number by 1.
                
                Usage:
                    <name> [--major | --minor | --fix]

set-branch      - Update a package's branch
                
                Usage:
                    <name> <source.branch>
                    
                    # or to synchronize the source.branch and the current version, use:
                    
                    <name>

set-require     - Update requirements for a package
                
                Usage:
                    <name> <require>

install         - Installs a package.
                
                Usage:
                    <name> <require> <source.url> <source.branch>

remove          - Removes a package.
                
                Usage:
                    <name>