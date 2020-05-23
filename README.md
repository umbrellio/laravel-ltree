# laravel-ltree-extension

[![Build Status](https://travis-ci.org/umbrellio/laravel-ltree.svg?branch=master)](https://travis-ci.org/umbrellio/laravel-ltree)
[![Coverage Status](https://coveralls.io/repos/github/umbrellio/laravel-ltree/badge.svg?branch=master)](https://coveralls.io/github/umbrellio/laravel-ltree?branch=master)

LTree Extension (PostgreSQL) for Laravel. 

## Installation

Run this command to install:
```bash
php composer.phar require umbrellio/laravel-ltree
```

## How to use

1. Implement your Eloquent\Model from LTreeModelInterface.

2. Use LTreeModelTrait in your Model for extenting functionality
 - getLtreeProxyDeleteColumns
 - getLtreeProxyUpdateColumns

3. Use LTreeService for build path:
  - create: createPath(LTreeModelInterface $model)
  - update: updatePath(LTreeModelInterface $model)
  - delete: dropDescendants(LTreeModelInterface $model)

## Authors

Created by Korben Dallas.

<a href="https://github.com/umbrellio/">
<img style="float: left;" src="https://umbrellio.github.io/Umbrellio/supported_by_umbrellio.svg" alt="Supported by Umbrellio" width="439" height="72">
</a>
