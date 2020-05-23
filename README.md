# laravel-ltree

[![Build Status](https://travis-ci.org/umbrellio/laravel-ltree.svg?branch=master)](https://travis-ci.org/umbrellio/laravel-ltree)
[![Coverage Status](https://coveralls.io/repos/github/umbrellio/laravel-ltree/badge.svg?branch=master)](https://coveralls.io/github/umbrellio/laravel-ltree?branch=master)

LTree Extension (PostgreSQL) for Laravel. 

## Installation

Run this command to install:
```bash
php composer.phar require umbrellio/laravel-ltree
```

## How to use

If you have problems in migration with Postgres `ltree` type, also you can register second provider:
`Umbrellio\LTree\Providers\LTreeGrammarProvider::class`

Implement your Eloquent\Model from LTreeModelInterface.

Use LTreeService for build path:
1. when create model: `createPath(LTreeModelInterface $model)`
2. when update model: `updatePath(LTreeModelInterface $model)` for update path for model and children
3. when delete model: `dropDescendants(LTreeModelInterface $model)` for delete children models

## Authors

Created by Korben Dallas.

<a href="https://github.com/umbrellio/">
<img style="float: left;" src="https://umbrellio.github.io/Umbrellio/supported_by_umbrellio.svg" alt="Supported by Umbrellio" width="439" height="72">
</a>
