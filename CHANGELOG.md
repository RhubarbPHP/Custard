# Changelog

### 1.1.1

* Forward compatibility with symfony Console > 5.0.0

### 1.1.0

* Added `LockedCustardCommand` which uses a file lock in `TEMP_DIR` to lock a custard command to only run one at a time

### 1.0.12

* Removed: phpdocumentor/reflection-docblock dependency 

### 1.0.9

* Fixed:	Boot support when called inside the build of rhubarb itself.

### 1.0.8

* Added:	Support for boot-custard.php, loaded as custard starts, before boot-application.php is called.

### 1.0.7

* Changed:	Fix to use new boot files in Rhubarb 1.1.0

### 1.0.6

* Changed:   	rhubarb is no longer a dependency due to circular dependency issues.

### 1.0.5

* Added: 	Change log
* Changed:	Reversed order of module interrogation

