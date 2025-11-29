<?php

namespace App\Infrastructure\Helpers;

class KnowledgeHelper
{
    public static function env($key, $default = null)
    {
        $value = EnvHelper::get($key, $default);
        return is_string($value) && $value !== '' ? $value : $default;
    }

    public static function httpPostJson($url, array $headers, array $payload)
    {
        $ch = curl_init($url);
        $headers[] = 'Content-Type: application/json';
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 90,
        ]);
        $raw = curl_exec($ch);
        if ($raw === false) {
            throw new \RuntimeException('Falha de rede: ' . curl_error($ch));
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (!$code) {
            $code = 0;
        }
        curl_close($ch);
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            throw new \RuntimeException("Resposta inválida da API (HTTP $code).");
        }
        if ($code < 200 || $code >= 300) {
            $msg = isset($json['error']['message']) ? $json['error']['message'] : (isset($json['error']) ? $json['error'] : 'Erro da API');
            throw new \RuntimeException("OpenAI HTTP $code: " . $msg);
        }
        return $json;
    }

    public static function hasPdfToText()
    {
        @exec('pdftotext -v', $output, $return);
        return ($return === 0 || stripos(implode("\n", $output), 'pdftotext') !== false);
    }

    public static function pdfToText($path)
    {
        if (!is_file($path)) {
            return '';
        }
        if (self::hasPdfToText()) {
            $tmp = sys_get_temp_dir() . '/pobj_' . uniqid() . '.txt';
            $cmd = 'pdftotext -layout ' . escapeshellarg($path) . ' ' . escapeshellarg($tmp);
            @exec($cmd);
            if (is_file($tmp)) {
                $txt = (string) file_get_contents($tmp);
                @unlink($tmp);
                return trim($txt);
            }
        }
        return "[AVISO] Não foi possível extrair texto do PDF '" . basename($path) . "' neste servidor. Converta para .txt e coloque em docs/knowledge.";
    }

    public static function csvToText($path)
    {
        if (!is_file($path)) {
            return '';
        }
        $fh = fopen($path, 'r');
        if (!$fh) {
            return '';
        }
        $rows = [];
        $headers = [];
        $i = 0;
        while (($row = fgetcsv($fh, 0, ';')) !== false) {
            if ($i === 0) {
                $headers = $row;
                $i++;
                continue;
            }
            $assoc = [];
            foreach ($row as $k => $v) {
                $key = isset($headers[$k]) && $headers[$k] !== '' ? $headers[$k] : "col$k";
                $assoc[$key] = $v;
            }
            $rows[] = $assoc;
            $i++;
            if ($i > 2000) {
                break;
            }
        }
        fclose($fh);
        $out = "CSV: " . basename($path) . "\n";
        foreach ($rows as $r) {
            $line = [];
            foreach ($r as $k => $v) {
                $line[] = "$k: $v";
            }
            $out .= "- " . implode(' | ', $line) . "\n";
        }
        return $out;
    }

    public static function jsonToText($path)
    {
        if (!is_file($path)) {
            return '';
        }
        $raw = file_get_contents($path);
        if (!is_string($raw) || $raw === '') {
            return '';
        }
        $data = json_decode($raw, true);
        if ($data === null) {
            return "JSON (texto bruto):\n" . $raw;
        }
        return "JSON: " . basename($path) . "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function txtToText($path)
    {
        $t = @file_get_contents($path);
        return is_string($t) ? trim($t) : '';
    }

    public static function fileToText($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            return self::pdfToText($path);
        }
        if ($ext === 'csv') {
            return self::csvToText($path);
        }
        if ($ext === 'json') {
            return self::jsonToText($path);
        }
        if ($ext === 'txt') {
            return self::txtToText($path);
        }
        return '';
    }

    public static function scanKnowledge($dir)
    {
        if (!is_dir($dir)) {
            return [];
        }
        $out = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['txt', 'pdf', 'csv', 'json'], true)) {
                continue;
            }
            $path = $file->getPathname();
            $text = self::fileToText($path);
            if ($text !== '') {
                $mtime = filemtime($path);
                $out[] = [
                    'path' => $path,
                    'name' => basename($path),
                    'mtime' => $mtime ? $mtime : time(),
                    'text' => $text,
                ];
            }
        }
        return $out;
    }

    public static function chunkText($text, $chunkSize = 1600, $overlap = 200)
    {
        $text = str_replace("\r", "", $text);
        $len = strlen($text);
        $i = 0;
        $id = 1;
        $chunks = [];
        while ($i < $len) {
            $end = min($len, $i + $chunkSize);
            $slice = trim(substr($text, $i, $end - $i));
            if ($slice !== '') {
                $chunks[] = ['id' => $id++, 'text' => $slice];
            }
            if ($end >= $len) {
                break;
            }
            $i = $end - $overlap;
            if ($i < 0) {
                $i = 0;
            }
        }
        return $chunks;
    }

    public static function embed(array $texts)
    {
        $apiKey = self::env('OPENAI_API_KEY', '');
        if ($apiKey === '') {
            throw new \RuntimeException('OPENAI_API_KEY não configurada.');
        }
        $model = self::env('OPENAI_EMBED_MODEL', 'text-embedding-3-small');

        $batch = 80;
        $out = [];
        $n = count($texts);
        for ($i = 0; $i < $n; $i += $batch) {
            $slice = array_slice($texts, $i, $batch);
            $resp = self::httpPostJson(
                'https://api.openai.com/v1/embeddings',
                ['Authorization: Bearer ' . $apiKey],
                ['model' => $model, 'input' => $slice]
            );
            foreach ($resp['data'] as $k => $row) {
                $out[$i + $k] = $row['embedding'];
            }
        }
        return $out;
    }

    public static function cosine(array $a, array $b)
    {
        $dot = 0.0;
        $na = 0.0;
        $nb = 0.0;
        $n = min(count($a), count($b));
        for ($i = 0; $i < $n; $i++) {
            $dot += $a[$i] * $b[$i];
            $na += $a[$i] * $a[$i];
            $nb += $b[$i] * $b[$i];
        }
        return ($na > 0 && $nb > 0) ? $dot / (sqrt($na) * sqrt($nb)) : 0.0;
    }

    public static function buildOrLoadIndex($dir, $indexPath)
    {
        $files = self::scanKnowledge($dir);
        $sig = [];
        foreach ($files as $f) {
            $sig[$f['path']] = $f['mtime'];
        }

        if (is_file($indexPath)) {
            $idx = json_decode((string) file_get_contents($indexPath), true);
            if (is_array($idx)) {
                $signature = isset($idx['signature']) ? $idx['signature'] : null;
                if ($signature === $sig) {
                    return $idx;
                }
            }
        }

        $items = [];
        foreach ($files as $f) {
            $chunks = self::chunkText($f['text']);
            foreach ($chunks as $c) {
                $items[] = [
                    'source' => $f['path'],
                    'name' => $f['name'],
                    'chunk_id' => $c['id'],
                    'text' => $c['text'],
                ];
            }
        }
        if (empty($items)) {
            $idx = ['items' => [], 'embeddings' => [], 'signature' => $sig, 'built_at' => time()];
            @file_put_contents($indexPath, json_encode($idx, JSON_UNESCAPED_UNICODE));
            return $idx;
        }

        $inputs = [];
        foreach ($items as $i) {
            $inputs[] = $i['text'];
        }
        $emb = self::embed($inputs);

        $idx = ['items' => $items, 'embeddings' => $emb, 'signature' => $sig, 'built_at' => time()];
        @file_put_contents($indexPath, json_encode($idx, JSON_UNESCAPED_UNICODE));
        return $idx;
    }

    public static function retrieveTopK($query, array $index, $k = 6)
    {
        if ($query === '' || empty($index['items'])) {
            return [];
        }
        $qEmbeds = self::embed([$query]);
        $q = isset($qEmbeds[0]) ? $qEmbeds[0] : [];
        $scored = [];
        foreach ($index['items'] as $i => $it) {
            $e = isset($index['embeddings'][$i]) ? $index['embeddings'][$i] : null;
            if (!is_array($e)) {
                continue;
            }
            $scored[] = [
                'score' => self::cosine($q, $e),
                'source' => $it['source'],
                'name' => $it['name'],
                'chunk_id' => $it['chunk_id'],
                'text' => $it['text'],
            ];
        }
        usort($scored, function ($a, $b) {
            return ($a['score'] < $b['score']) ? 1 : -1;
        });
        return array_slice($scored, 0, max(1, $k));
    }
}

