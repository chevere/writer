# Writer

![Chevere](chevere.svg)

[![Build](https://img.shields.io/github/actions/workflow/status/chevere/writer/test.yml?branch=1.0&style=flat-square)](https://github.com/chevere/writer/actions)
![Code size](https://img.shields.io/github/languages/code-size/chevere/writer?style=flat-square)
[![Apache-2.0](https://img.shields.io/github/license/chevere/writer?style=flat-square)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-blueviolet?style=flat-square)](https://phpstan.org/)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchevere%2Fwriter%2F1.0)](https://dashboard.stryker-mutator.io/reports/github.com/chevere/writer/1.0)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=chevere_writer&metric=alert_status)](https://sonarcloud.io/dashboard?id=chevere_writer)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_writer&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=chevere_writer)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_writer&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=chevere_writer)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_writer&metric=security_rating)](https://sonarcloud.io/dashboard?id=chevere_writer)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=chevere_writer&metric=coverage)](https://sonarcloud.io/dashboard?id=chevere_writer)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=chevere_writer&metric=sqale_index)](https://sonarcloud.io/dashboard?id=chevere_writer)
[![CodeFactor](https://www.codefactor.io/repository/github/chevere/writer/badge)](https://www.codefactor.io/repository/github/chevere/writer)

## Summary

Writer provides tooling for writing to streams.

## Installing

Writer is available through [Packagist](https://packagist.org/packages/chevere/writer) and the repository source is at [chevere/writer](https://github.com/chevere/writer).

```sh
composer require chevere/writer
```

## Streams

### Stream for

Use function `streamFor` to create an stream.

```php
use function Chevere\Writer\streamFor;

$stream = streamFor(
    stream: 'php://temp',
    mode: 'r+'
);
```

### Stream temp

Use function `streamTemp` to create a temp stream (rw+).

```php
use function Chevere\Writer\streamTemp;

$stream = streamTemp($content);
```

## StreamWriter

Use `StreamWriter` to write strings to a stream.

```php
use Chevere\Writer\StreamWriter;
use function Chevere\Writer\streamFor;

$stream = streamFor('php://output', 'r');
$writer = new StreamWriter($stream);
$writer->write('Hello, world!');
```

## NullWriter

Use `NullWriter` when requiring `null` write override.

## Writers

Use `Writers` to interact with pre-defined streams for output, error, debug and log. By default only output and error streams are defined.

| Stream | Default      |
| ------ | ------------ |
| output | StreamWriter |
| error  | StreamWriter |
| debug  | NullWriter   |
| log    | NullWriter   |

```php
use Chevere\Writer\Writers;

$writers = new Writers();
$writers->error();
$writers->debug();
$writers->log();
```

### Output stream

Use `output` to interact with the output stream. Use `withOutput` to set a custom output stream.

```php
$with = $writers->withOutput($stream);
$with->output(); // $stream
```

### Error stream

Use `error` to interact with the error stream. Use `withError` to set a custom error stream.

```php
$with = $writers->withError($stream);
$with->error(); // $stream
```

### Debug stream

Use `debug` to interact with the debug stream. Use `withDebug` to set a custom debug stream.

```php
$with = $writers->withDebug($stream);
$with->debug(); // $stream
```

### Log stream

Use `log` to interact with the log stream. Use `withLog` to set a custom log stream.

```php
$with = $writers->withLog($stream);
$with->log(); // $stream
```

## Documentation

Documentation is available at [chevere.org](https://chevere.org/packages/writer).

## License

Copyright [Rodolfo Berrios A.](https://rodolfoberrios.com/)

Chevere is licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for the full license text.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
