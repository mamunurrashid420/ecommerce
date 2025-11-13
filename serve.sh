#!/bin/bash
# Helper script to run Laravel serve command with --no-reload flag to avoid warning
php artisan serve --no-reload "$@"

