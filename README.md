# laravel-ltree

[![Build Github Actions](https://github.com/umbrellio/laravel-ltree/workflows/ci/badge.svg)]
[![Build Status](https://travis-ci.org/umbrellio/laravel-ltree.svg?branch=master)](https://travis-ci.org/umbrellio/laravel-ltree)
[![Coverage Status](https://coveralls.io/repos/github/umbrellio/laravel-ltree/badge.svg?branch=master)](https://coveralls.io/github/umbrellio/laravel-ltree?branch=master)

LTree Extension (PostgreSQL) for Laravel. 

## Installation

Run this command to install:
```bash
php composer.phar require umbrellio/laravel-ltree
```

## How to use

Implement your `Eloquent\Model` from `LTreeModelInterface`.

Use LTreeService for build path:
1. when create model: `createPath(LTreeModelInterface $model)`
2. when update model: `updatePath(LTreeModelInterface $model)` for update path for model and children
3. when delete model: `dropDescendants(LTreeModelInterface $model)` for delete children models

The `get()` method returns `LTreeCollection`, instead of the usual `Eloquent\Collection`.

`LTreeCollection` has a `toTree()` method that converts a flat collection to a tree.

`LTreeResourceCollection` & `LTreeResource`, which take `LTreeCollection` as an argument, will also be useful.

## Authors

Created by Korben Dallas.

<a href="https://github.com/umbrellio/">
<img style="float: left;" src="https://umbrellio.github.io/Umbrellio/supported_by_umbrellio.svg" alt="Supported by Umbrellio" width="439" height="72">
</a>
