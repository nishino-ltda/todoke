<?php

namespace Tests;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class TestStateListener
{
    protected static $previousTest = null;
    protected static $stateChanges = [];

    public static function logStateChange(string $currentTest, array $stateInfo)
    {
        if (self::$previousTest) {
            self::$stateChanges[] = [
                'from' => self::$previousTest,
                'to' => $currentTest,
                'changes' => $stateInfo
            ];
        }
        self::$previousTest = $currentTest;
    }

    public static function getStateChanges()
    {
        return self::$stateChanges;
    }

    public static function reset()
    {
        self::$previousTest = null;
        self::$stateChanges = [];
    }

    public static function logTestStart(string $testName)
    {
        Log::debug("Starting test: {$testName}");
        Log::debug("Memory usage: ".memory_get_usage());
        
        // Log database state
        $tables = DB::select('SHOW TABLES');
        $dbState = [];
        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_'.env('DB_DATABASE')};
            $dbState[$tableName] = DB::table($tableName)->count();
        }
        
        Log::debug("Database state:", $dbState);
    }

    public static function logTestEnd(string $testName)
    {
        Log::debug("Finished test: {$testName}");
        Log::debug("Memory usage: ".memory_get_usage());
    }
}
