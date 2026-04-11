# congraph/core

`congraph/core` is the foundation package for the Congraph ecosystem.

## Responsibilities

- command bus and command validation dispatching
- data transfer objects (`Model`, `Collection`, and related helpers)
- exception types
- mapping and resolution helpers
- shared service provider and framework integration points

Most other Congraph packages depend on `core`.

## Installation

```bash
composer require congraph/core congraph/contracts
```

Laravel package provider:

- [CoreServiceProvider.php](/Users/nikolap/git/np/congraph/packages/core/CoreServiceProvider.php)

## Key concepts

### Command bus

Congraph uses command classes for operations such as:

- create
- update
- delete
- fetch
- get

`CommandDispatcher` performs:

1. before-command events
2. validator resolution
3. command handling
4. after-command events

### DTO layer

The `Model` and `Collection` DTOs are used across packages for predictable API and repository output.

### Exceptions

Important exception families include:

- validation failures
- not found failures
- bad request failures

These are intentionally mapped by `laravel-api` into HTTP exceptions.

## Development

### Install dependencies

```bash
cd /Users/nikolap/git/np/congraph/packages/core
composer update -W
```

### Run tests

```bash
composer test
```

## Contribution notes

- changes here affect the whole monorepo
- preserve backwards-compatible DTO behavior when possible
- add tests for command dispatch, validation mapping, and DTO normalization
- if you touch event naming or exception mapping, re-test dependent packages
