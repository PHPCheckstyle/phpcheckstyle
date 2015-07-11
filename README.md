# PHPCheckstyle

## 1. Overview

PHPCheckstyle is an open-source tool that helps PHP programmers 
adhere to certain coding conventions. The tools checks the input PHP 
source code and reports any deviations from the coding convention.

The tool uses the PEAR Coding Standards as the default coding convention. 
But it allows you to configure it to suit your coding standards.

Please visit http://code.google.com/p/phpcheckstyle/ for
more information and documentation


## 2. Requirements

- PHP 5.0 or newer. 
- Web browser to view the checkstyle report (only for html view)
- That's all. 


## 3. Installation

### Installation with Composer

```sh
composer require phpcheckstyle/phpcheckstyle
```

or Add `phpcheckstyle/phpcheckstyle` as a requirement to `composer.json`:

```json
{
    "require": {
        "phpcheckstyle/phpcheckstyle": "dev-master"
    }
}
```

Update your packages with `composer update` or if installing from fresh, with `composer install`.

### Manual Installation

Just unzip the distribution.

```bash
$> unzip PhpCheckstyle.zip
```

This will create a directory called `phpcheckstyle` and expand all 
files in it.


## 4. Usage

- Change directory to the PHPCheckstyle installation directory.

  ```bash
  $> cd phpcheckstyle
  ```

- Execute the `run.php` script providing the `--src` option.

  ```bash
  $> php run.php --src <php source directory/file>
  ```

- Use the `--help` option to see more options

  ```bash
  $> php run.php --help
  ```


# License
See [LICENSE](/LICENSE.txt)
