#GrumPHPPsalm
This package is an [Psalm](https://github.com/vimeo/psalm) extension for [GrumPHP](https://github.com/phpro/grumphp).
All newly committed files will be analysed by Psalm.

##Installation
Add this package using composer, firstly add the packages repository


  Then, require the this repository

```bash
	composer require --dev weemen/grumphp-psalm
```

##Usage
First of all, dont forget to create your psaml.xml file

For example:

```xml
<?xml version="1.0"?>
<psalm
  stopOnFirstError="false"
  useDocblockTypes="true"
>
    <projectFiles>
        <directory name="src" />
    </projectFiles>
    <issueHandlers>
        <MissingReturnType errorLevel="error" />
        <MissingClosureReturnType errorLevel="error" />
        <MissingPropertyType errorLevel="error" />
    </issueHandlers>
</psalm>
```

Edit GrumPHP and add the psalm task:
```yaml
parameters:
  git_dir: .
  bin_dir: bin
  tasks:
    psalm:
       config: psalm.xml
  extensions:
    - Weemen\GrumPHPPsalm\Extension\Loader
```