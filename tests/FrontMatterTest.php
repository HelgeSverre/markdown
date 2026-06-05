<?php

declare(strict_types=1);

namespace HelgeSverre\Markdown\Tests;

use HelgeSverre\Markdown\Ffi\Yaml;
use HelgeSverre\Markdown\FrontMatter;
use PHPUnit\Framework\TestCase;

final class FrontMatterTest extends TestCase
{
    public function test_decode_parses_a_flat_map(): void
    {
        $this->assertSame(
            ['title' => 'Hello', 'draft' => false, 'n' => 3],
            Yaml::decode("title: Hello\ndraft: false\nn: 3\n"),
        );
    }

    public function test_decode_normalizes_yaml_numeric_scalars_to_valid_json_numbers(): void
    {
        $this->assertSame(
            [
                'positive' => 3,
                'positiveFloat' => 1.5,
                'leadingDotFloat' => 0.5,
                'trailingDotFloat' => 1.0,
                'hex' => 16,
                'octal' => 10,
            ],
            Yaml::decode("positive: +3\npositiveFloat: +1.5\nleadingDotFloat: .5\ntrailingDotFloat: 1.\nhex: 0x10\noctal: 012\n"),
        );
    }

    public function test_decode_handles_special_float_scalars(): void
    {
        $decoded = Yaml::decode("plainInf: .inf\npositiveInf: +.inf\nnegativeInf: -.inf\nnotANumber: .nan\npositiveNotANumber: +.nan\n");

        $this->assertIsFloat($decoded['plainInf']);
        $this->assertIsFloat($decoded['positiveInf']);
        $this->assertIsFloat($decoded['negativeInf']);
        $this->assertTrue(is_infinite($decoded['plainInf']));
        $this->assertTrue(is_infinite($decoded['positiveInf']));
        $this->assertTrue(is_infinite($decoded['negativeInf']));
        $this->assertGreaterThan(0, $decoded['positiveInf']);
        $this->assertLessThan(0, $decoded['negativeInf']);
        $this->assertNull($decoded['notANumber']);
        $this->assertNull($decoded['positiveNotANumber']);
    }

    public function test_decode_returns_empty_for_merge_keys(): void
    {
        // `<<` merge keys are unsupported and there is no symfony fallback, so
        // the whole block degrades to an empty array.
        $yaml = "defaults: &d\n  a: 1\nthing:\n  <<: *d\n  b: 2\n";
        $this->assertSame([], Yaml::decode($yaml));
    }

    public function test_extract_returns_map_and_body(): void
    {
        $doc = "---\ntitle: Hi\ntags: [a, b]\n---\n# Body\n\ntext\n";
        [$fm, $body] = FrontMatter::extract($doc);

        $this->assertSame(['title' => 'Hi', 'tags' => ['a', 'b']], $fm);
        $this->assertSame("# Body\n\ntext\n", $body);
    }

    public function test_extract_degrades_to_empty_for_merge_keys(): void
    {
        // No symfony fallback: a `<<` merge key anywhere makes libyaml's walker
        // decline the whole block, so front matter is empty (body still split).
        $doc = "---\ndefaults: &d\n  a: 1\nthing:\n  <<: *d\n  b: 2\n---\nbody\n";
        [$fm, $body] = FrontMatter::extract($doc);

        $this->assertSame([], $fm);
        $this->assertSame("body\n", $body);
    }

    public function test_extract_with_no_frontmatter(): void
    {
        $this->assertSame([[], '# Just markdown'], FrontMatter::extract('# Just markdown'));
    }

    public function test_extract_with_malformed_yaml_is_empty(): void
    {
        $doc = "---\n\tbad:\n  - : :\nindent\n---\nbody\n";
        [$fm] = FrontMatter::extract($doc);
        $this->assertSame([], $fm);
    }

    public function test_bare_dates_stay_strings(): void
    {
        // Deliberate divergence from Symfony\Yaml (which returns an int Unix
        // timestamp): the libyaml fast path keeps bare dates as strings, like
        // PECL yaml / spyc / dallgoot. Documented in README.
        [$fm] = FrontMatter::extract("---\ndate: 2026-06-05\n---\nx\n");
        $this->assertSame('2026-06-05', $fm['date']);
    }
}
