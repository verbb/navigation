<?php
namespace verbb\navigation\helpers;

use verbb\navigation\migrations\plugins\MigrationLine;
use verbb\navigation\migrations\plugins\MigrationResult;

use craft\helpers\Console;

class MigrationRenderer
{
    // Static Methods
    // =========================================================================

    public static function renderToConsole(array $lines): void
    {
        foreach ($lines as $line) {
            $color = match ($line->level) {
                'success' => Console::FG_GREEN,
                'warning' => Console::FG_YELLOW,
                'error' => Console::FG_RED,
                default => Console::FG_GREY,
            };

            $prefix = $line->depth > 0 ? '> ' : '';

            Console::stdout($prefix . $line->message . PHP_EOL, $color);
        }
    }

    public static function renderResultToConsole(MigrationResult $result): void
    {
        self::renderToConsole($result->lines);

        if ($result->stats !== []) {
            Console::stdout(PHP_EOL . 'Summary:' . PHP_EOL, Console::FG_YELLOW);

            foreach ($result->stats as $key => $value) {
                Console::stdout("- {$key}: {$value}" . PHP_EOL, Console::FG_GREY);
            }
        }
    }

    public static function serializeLines(MigrationResult $result): array
    {
        return array_map(static fn(MigrationLine $line) => [
            'level' => $line->level,
            'message' => $line->message,
            'depth' => $line->depth,
        ], $result->lines);
    }
}
