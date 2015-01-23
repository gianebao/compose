Composer Configuration File Helper
==================================

This provides tools to make maintaining the references and requirements easier.

Options:
--------

list                    - Lists all packages that are defined in the config file.
                        
                        Usage:
                            [--simple | --full] [<filter>]

up                      - Increments the package's version number by 1.
                        
                        Usage:
                            <name> [--major | --minor | --fix]

down                    - Decrements the package's version number by 1.
                        
                        Usage:
                            <name> [--major | --minor | --fix]

set-branch              - Update a package's branch
                        
                        Usage:
                            <name> <source.branch>
                            
                            # or to synchronize the <source.branch> and the current version, use:
                            
                            <name>

set-require             - Update requirements for a package
                        
                        Usage:
                            <name> <require>
                            
                            # or to synchronize the <require> and the current version, use:
                            
                            <name>
                        
set-version             - Update package's version and source.branch
                        
                        Usage:
                            <name> <version>
                            
                            # to keep the current version and just synchronize the source.branch and version, use:
                            
                            <name>
                        
set-latest              - Sets every package to the latest available tag that can be retrieved from the branch. Default is `master`.
                        
                        Usage:
                            [source.branch]
                    
set-latest-package      - Sets the package to the latest available tag that can be retrieved from the branch. Default is `master`.
                        
                        Usage:
                            <name> [source.branch]
                        
                        
add                     - Installs a package.
                        
                        Usage:
                            <name> <source.url> <source.branch>
                        
remove                  - Removes a package.
                        
                        Usage:
                            <name>