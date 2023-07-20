# `canto-dam-assets` buildchain

This buildchain is a self-contained build system for the `canto-dam-assets` JavaScript bundle.

## Overview

The buildchain builds & bundles all of the `canto-dam-assets` TypeScript/JavaScript code along with CSS and any other static resources via Vite via a Docker container.

Source files:

`buildchain/src/`

Built distribution files:

`src/web/assets/dist/`

## Prerequisites

- Must have [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or the equivalent) installed
- For HMR during local development, you'll need the following variable set in your `.env` file:
```
VITE_PLUGIN_DEVSERVER=1
```

## Commands

This buildchain uses `make` as an interface to the buildchain. The following commands are available from the `buildchain/` directory:

- `make build` - Do a distribution build of the CantoDamAsset asset bundle resources into `src/web/assets/dist/`
- `make dev` - Start Vite HMR dev server for local development
- `make clean` - Remove `node_modules/` and `package-lock.json` to start clean (need to run `make image-build` after doing this, see below)
- `make npm XXX` - Run an `npm` command inside the container, e.g.: `make npm run lint` or `make npm install`
- `make ssh` - Open up a shell session into the buildchain Docker container
- `make image-build` - Build the Docker image & run `npm install`
