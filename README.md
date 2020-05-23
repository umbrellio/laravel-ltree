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
1). Register LTreeServiceProvider in your app (config/app.php):

`Umbrellio\LTree\Providers\LTreeServiceProvider::class`

If you have problems in migration with Postgres `ltree` type, also you can register second provider:

`Umbrellio\LTree\Providers\LTreeGrammarProvider::class`

2). Implement your Eloquent\Model from LTreeModelInterface.

3). Use LTreeModelTrait in your Model for extenting functionality
 - getLtreeProxyDeleteColumns
 - getLtreeProxyUpdateColumns

4). Use LTreeService for build path:
  - create: createPath(LTreeModelInterface $model)
  - update: updatePath(LTreeModelInterface $model)
  - delete: dropDescendants(LTreeModelInterface $model)

## Authors

Created by Korben Dallas.

<a href="https://github.com/umbrellio/">
<img style="float: left;" src="https://umbrellio.github.io/Umbrellio/supported_by_umbrellio.svg" alt="Supported by Umbrellio" width="439" height="72">
</a>
