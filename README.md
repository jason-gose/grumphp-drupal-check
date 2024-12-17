# Description

This repository adds a task for GrumPHP 2.x that launchs [drupal-check](https://github.com/mglaman/drupal-check).
During a commit check Drupal code for deprecations and discover bugs via static analysis. If a deprecated code is detected, it won't pass.


# Installation

Install it using composer:

```composer require --dev metadrop/grumphp-drupal-check```


# Usage

1) Add the extension in your grumphp.yml file:
```yaml
extensions:
  - GrumphpDrupalCheck\ExtensionLoader
```

2) Add drupal check to the tasks:
```
tasks:
  drupalcheck:
    drupal_root: ~
    format: ~
    deprecations: true
    analysis: true
    style: false
    memory_limit: ~
    exclude_dir: []
    php8: true
    verbose: 0
```
Optionally, you can define multiple DrupalCheck arguments:

- **drupal_root** (string): Configure the path to the Drupal root. This fallback option can be used if drupal-check could not identify Drupal root from the provided path(s). This is useful when testing a module as opposed to a Drupal installation.
- **format** (string): Format of output: raw, table, checkstyle, json, or junit. By default it is "table".
- **deprecations** (boolean): Check code for deprecations. By default it is true.
- **analysis** (boolean): Check code analysis.
- **style** (boolean): Check code style.
- **memory_limit** (string): Configure memory limit for the process.
- **exclude_dir** (array): Directories to exclude.
- **php8** (boolean): Set PHPStan phpVersion for 8.1 (Drupal 10 requirement).
- **verbose** (int): Set verbose output (1 for normal verbose output, 2 for more verbose output and 3 for debug).
