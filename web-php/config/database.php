<?php
// web-php/config/database.php
// Konfigurasi Koneksi Database PostgreSQL (Neon / Supabase) & Fallback

class Database {
    private static ?PDO $instance = null;
    private static bool $attempted = false;

    public static function getConnection(): ?PDO {
        if (self::$attempted) {
            return self::$instance;
        }

        self::$attempted = true;

        // 1. Cek variabel DATABASE_URL atau POSTGRES_URL dari Vercel / Neon / Supabase
        $dbUrl = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL') ?: '';

        if (!empty($dbUrl)) {
            $parsed = parse_url($dbUrl);
            if ($parsed && isset($parsed['host'])) {
                $host = $parsed['host'];
                $port = $parsed['port'] ?? 5432;
                $user = $parsed['user'] ?? '';
                $pass = $parsed['pass'] ?? '';
                $dbname = ltrim($parsed['path'] ?? 'neondb', '/');

                try {
                    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";
                    self::$instance = new PDO($dsn, $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                    return self::$instance;
                } catch (PDOException $e) {
                    error_log('PostgreSQL Neon Connection Error: ' . $e->getMessage());
                    self::$instance = null;
                    return null;
                }
            }
        }

        // 2. Cek variabel individual (PGHOST, PGDATABASE, dll)
        $host = getenv('PGHOST');
        if (!empty($host)) {
            $port = getenv('PGPORT') ?: '5432';
            $dbname = getenv('PGDATABASE') ?: 'neondb';
            $user = getenv('PGUSER') ?: 'neondb_owner';
            $pass = getenv('PGPASSWORD') ?: '';
            $sslmode = getenv('PGSSLMODE') ?: 'require';

            try {
                $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                return self::$instance;
            } catch (PDOException $e) {
                error_log('PostgreSQL Connection Error: ' . $e->getMessage());
                self::$instance = null;
                return null;
            }
        }

        // 3. Fallback jika tidak ada database cloud: return null (aplikasi otomatis pakai store.json)
        self::$instance = null;
        return null;
    }
}
