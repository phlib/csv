# phlib/csv

[![Code Checks](https://img.shields.io/github/actions/workflow/status/phlib/csv/code-checks.yml?logo=github)](https://github.com/phlib/csv/actions/workflows/code-checks.yml)
[![Codecov](https://img.shields.io/codecov/c/github/phlib/csv.svg?logo=codecov)](https://codecov.io/gh/phlib/csv)
[![Latest Stable Version](https://img.shields.io/packagist/v/phlib/csv.svg?logo=packagist)](https://packagist.org/packages/phlib/csv)
[![Total Downloads](https://img.shields.io/packagist/dt/phlib/csv.svg?logo=packagist)](https://packagist.org/packages/phlib/csv)
![Licence](https://img.shields.io/github/license/phlib/csv.svg)

A CSV parsing library; Prevents out of memory errors when parsing large files
without a closing string delimiter

## Install

Via Composer

``` bash
$ composer require phlib/csv
```

## Usage

```php
$stream = stream_for(fopen($filename, 'r')); // Must be a *seekable* stream
$csv = new \Phlib\Csv($stream);
foreach ($csv as $row) {
    print_r($row);
}
```

## License

This package is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
