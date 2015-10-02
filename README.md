# Custard
Command line tools for Rhubarb

# Add to your project
`composer require rhubarbphp/custard`

# Call from the command line
Ensure you're currently in the root of your Rhubarb project, then call:

`php vendor/rhubarbphp/custard/src/custard.php list`

Replacing "list" with any command you wish to run.

# Document Models command
`php vendor/rhubarbphp/custard/src/custard.php document-models [your schema name]`

This method will inspect all the models in your SolutionSchema and add phpDoc @property 
definitions for all model columns and relationships. This allows code completion and
type hinting in intelligent IDEs such as PhpStorm.
