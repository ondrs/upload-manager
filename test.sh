#!/bin/sh
./vendor/bin/parallel-lint -e php,phpt --exclude vendor .
./vendor/bin/tester -c tests/php.ini-unix -j 40 tests
