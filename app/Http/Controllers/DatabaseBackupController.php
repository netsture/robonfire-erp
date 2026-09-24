<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseBackupController extends Controller
{
    public function download()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Database Export is restricted to Admin role.');
        }

        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();

        $tables = [];
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $tablesResult = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
            $keyName = "Tables_in_" . $dbName;
            foreach ($tablesResult as $tableObj) {
                if (isset($tableObj->$keyName)) {
                    $tables[] = $tableObj->$keyName;
                } else {
                    $arr = (array)$tableObj;
                    $tables[] = reset($arr);
                }
            }
        } else {
            // SQLite or fallback
            $tablesResult = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($tablesResult as $row) {
                $tables[] = $row->name;
            }
        }

        date_default_timezone_set('Asia/Kolkata');
        $filename = 'ERP_Database_Dump_' . date('d-m-Y_H-i-s') . '.sql';

        return response()->streamDownload(function () use ($tables, $driver) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "-- ERP Full Database Dump\n");
            fwrite($handle, "-- Generated on: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "-- Database Driver: " . strtoupper($driver) . "\n\n");

            if ($driver === 'mysql') {
                fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
                fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
                fwrite($handle, "SET time_zone = \"+00:00\";\n\n");
            }

            foreach ($tables as $table) {
                fwrite($handle, "-- --------------------------------------------------------\n");
                fwrite($handle, "-- Table structure for `$table`\n");
                fwrite($handle, "-- --------------------------------------------------------\n\n");

                fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");

                if ($driver === 'mysql') {
                    $createTableRes = DB::select("SHOW CREATE TABLE `$table`");
                    if (!empty($createTableRes)) {
                        $createArr = (array)$createTableRes[0];
                        $createSql = $createArr['Create Table'] ?? reset($createArr);
                        fwrite($handle, $createSql . ";\n\n");
                    }
                }

                fwrite($handle, "-- Dumping data for table `$table`\n\n");

                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    foreach ($rows as $row) {
                        $rowArray = (array)$row;
                        $columns = array_map(function ($col) {
                            return "`" . str_replace("`", "``", $col) . "`";
                        }, array_keys($rowArray));

                        $values = array_map(function ($val) use ($driver) {
                            if (is_null($val)) {
                                return "NULL";
                            }
                            if (is_numeric($val)) {
                                return $val;
                            }
                            return "'" . addslashes($val) . "'";
                        }, array_values($rowArray));

                        $insertSql = "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");\n";
                        fwrite($handle, $insertSql);
                    }
                    fwrite($handle, "\n");
                }
            }

            if ($driver === 'mysql') {
                fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/x-sql',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
