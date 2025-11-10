<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

function parse_excel_datetime($val): ?string
{
    if ($val === null || $val === '') return null;

    // Jika numeric (Excel serial date)
    if (is_numeric($val)) {
        try {
            $dt = ExcelDate::excelToDateTimeObject((float)$val);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    $val = trim(str_replace(['/', '.', "\xA0"], '-', (string)$val));
    $formats = [
        'd-m-Y H:i:s', 'd-m-Y H:i', 'Y-m-d H:i:s', 'Y-m-d H:i',
        'd.m.Y H:i:s', 'd.m.Y H:i', 'd/m/Y H:i:s', 'd/m/Y H:i'
    ];

    foreach ($formats as $fmt) {
        $tmp = \DateTime::createFromFormat($fmt, $val);
        if ($tmp) return $tmp->format('Y-m-d H:i:s');
    }

    // last attempt pakai strtotime (jika format long, mis. "3 Nov 2025 15:30")
    $ts = strtotime($val);
    return $ts ? date('Y-m-d H:i:s', $ts) : null;
}


/**
 * Entry point parser
 */

function parse_mt5_report(string $filePath): array
{
    if (!file_exists($filePath)) {
        return [];
    }

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    return match ($ext) {
        'csv'   => parse_mt5_csv($filePath),
        'xlsx'  => parse_mt5_xlsx($filePath),
        'html'  => parse_mt5_html($filePath),
        default => [],
    };
}

/**
 * =========================
 * CSV PARSER
 * =========================
 */
function parse_mt5_csv(string $filePath): array
{
    $rows = [];
    if (!($handle = fopen($filePath, 'r'))) {
        return [];
    }

    // deteksi delimiter otomatis
    $firstLine = fgets($handle);
    $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
    rewind($handle);

    fgetcsv($handle, 0, $delimiter); // skip header

    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
        if (count($data) < 10 || empty($data[0])) continue;

        $rows[] = [
            'symbol'       => trim($data[2] ?? ''),
            'type'         => strtoupper(trim($data[3] ?? '')),
            'lot_size'     => (float) str_replace(',', '.', $data[4] ?? 0),
            'open_price'   => (float) str_replace(',', '.', $data[5] ?? 0),
            'close_price'  => (float) str_replace(',', '.', $data[8] ?? 0),
            'profit_loss'  => (float) str_replace(',', '.', end($data) ?? 0),
            'created_at'   => date('Y-m-d H:i:s', strtotime(str_replace(['.', '/'], '-', $data[0] ?? 'now'))),
        ];
    }

    fclose($handle);
    return $rows;
}

/**
 * =========================
 * HTML PARSER (Positions only)
 * =========================
 */

function parse_mt5_html(string $filePath): array
{
    if (!file_exists($filePath)) return [];

    $html = file_get_contents($filePath);
    if (trim($html) === '') return [];

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $rows = [];

    // cari header "Positions"
    $positionsHeader = $xpath->query("//th[contains(., 'Positions')]")->item(0);
    if (!$positionsHeader) return [];

    // cari tabel induk dari header itu
    $table = $positionsHeader->parentNode;
    while ($table && $table->nodeName !== 'table') {
        $table = $table->parentNode;
    }
    if (!$table) return [];

    $isPositions = false;
    /** @var DOMElement $table */
    foreach ($table->getElementsByTagName('tr') as $tr) {
        $htmlRow = $dom->saveHTML($tr);

        // deteksi awal dan akhir tabel
        if (stripos($htmlRow, '<b>Positions</b>') !== false) {
            $isPositions = true;
            continue;
        }
        if (stripos($htmlRow, '<b>Orders</b>') !== false || stripos($htmlRow, '<b>Deals</b>') !== false) {
            $isPositions = false;
            break; // langsung berhenti
        }
        if (!$isPositions) continue;

        // ambil semua <td> kecuali class="hidden"
        $tds = [];
        foreach ($tr->getElementsByTagName('td') as $td) {
            if ($td->hasAttribute('class') && trim($td->getAttribute('class')) === 'hidden') continue;
            $tds[] = trim($td->textContent);
        }

        if (count($tds) < 10) continue;

        $type = strtoupper($tds[3] ?? '');
        if (!in_array($type, ['BUY', 'SELL'])) continue;

        $openTime    = $tds[0] ?? '';
        $symbol      = $tds[2] ?? '';
        $lot_size    = (float) str_replace(',', '.', $tds[4] ?? 0);
        $open_price  = (float) str_replace(',', '.', $tds[5] ?? 0);
        $close_price = (float) str_replace(',', '.', $tds[9] ?? 0);
        $profit_loss = (float) str_replace(',', '.', end($tds) ?: 0);

        if ($lot_size <= 0 || !$symbol) continue;

        $timestamp = strtotime(str_replace('.', '-', $openTime)) ?: time();

        $rows[] = [
            'symbol'       => $symbol,
            'type'         => $type,
            'lot_size'     => $lot_size,
            'open_price'   => $open_price,
            'close_price'  => $close_price,
            'profit_loss'  => $profit_loss,
            'created_at'   => date('Y-m-d H:i:s', $timestamp),
        ];
    }

    return $rows;
}

/**
 * Convert various Excel numeric formats to float reliably.
 * Handles:
 *  - 3,862.87
 *  - 3.862,87
 *  - 3862.87
 *  - 3862,87
 *  - numeric values already
 */
function to_float($val): float
{
    if ($val === null || $val === '') return 0.0;

    // already numeric
    if (is_numeric($val)) {
        return (float) $val;
    }

    $s = trim((string) $val);

    // remove spaces and non-breakable spaces
    $s = str_replace(["\xc2\xa0", ' '], '', $s);

    // if both dot and comma present, assume the last one is decimal separator
    $hasDot = strpos($s, '.') !== false;
    $hasComma = strpos($s, ',') !== false;

    if ($hasDot && $hasComma) {
        // decimal is the one that appears rightmost
        $lastDot = strrpos($s, '.');
        $lastComma = strrpos($s, ',');
        if ($lastComma > $lastDot) {
            // comma is decimal -> remove dots (thousand), replace comma -> dot
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // dot is decimal -> remove commas (thousand)
            $s = str_replace(',', '', $s);
        }
    } elseif ($hasComma && !$hasDot) {
        // likely comma is decimal
        $s = str_replace('.', '', $s); // just in case stray dots
        $s = str_replace(',', '.', $s);
    } else {
        // no comma or only dot
        $s = str_replace(',', '', $s); // remove stray commas
    }

    // remove any non-digit except dot and leading minus
    if (preg_match('/^-?[\d\.]+$/', $s) !== 1) {
        // strip anything else
        $s = preg_replace('/[^0-9\.\-]/', '', $s);
    }

    return (float) $s;
}

/**
 * =========================
 * XLSX PARSER
 * =========================
 */

function parse_mt5_xlsx(string $filePath, ?int $accountId = null): array
{
    if (!file_exists($filePath)) return [];

    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    $parsed = [];
    $isPositions = false;

    // helper kecil buat parse waktu Excel
    $parseExcelDate = function ($val): ?string {
        if ($val === null || $val === '') return null;

        // Jika cell numeric (serial Excel)
        if (is_numeric($val)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float)$val);
                return $dt->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                return null;
            }
        }

        // Bersihkan karakter aneh & variasi pemisah
        $val = trim(str_replace(['/', '.', "\xA0"], '-', (string)$val));

        // Coba berbagai format umum
        $formats = [
            'd-m-Y H:i:s', 'd-m-Y H:i', 'Y-m-d H:i:s', 'Y-m-d H:i',
            'd.m.Y H:i:s', 'd.m.Y H:i', 'd/m/Y H:i:s', 'd/m/Y H:i',
            'j M Y H:i:s', 'j M Y H:i', 'd M Y H:i:s', 'd M Y H:i'
        ];

        foreach ($formats as $fmt) {
            $tmp = \DateTime::createFromFormat($fmt, $val);
            if ($tmp) return $tmp->format('Y-m-d H:i:s');
        }

        // Last resort: strtotime
        $ts = strtotime($val);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    };

    foreach ($rows as $row) {
        $joinedRow = strtoupper(trim(implode(' ', $row)));

        // --- deteksi awal bagian Positions ---
        if (!$isPositions && strpos($joinedRow, 'POSITIONS') !== false) {
            $isPositions = true;
            continue;
        }

        // --- berhenti di Orders / Deals / Closed Positions ---
        if ($isPositions && (
            strpos($joinedRow, 'ORDERS') !== false ||
            strpos($joinedRow, 'DEALS') !== false ||
            strpos($joinedRow, 'CLOSED POSITIONS') !== false
        )) {
            break;
        }

        if (!$isPositions) continue;

        $firstCell = trim($row['A'] ?? '');
        if ($firstCell === '' || stripos($firstCell, 'TIME') !== false) continue;

        $typeCheck = strtoupper(trim($row['D'] ?? ''));
        if (!in_array($typeCheck, ['BUY', 'SELL'])) continue;

        // --- Ambil kolom sesuai posisi MT5 ---
        $position_id  = trim($row['B'] ?? '') ?: null;
        $symbol       = strtoupper(trim($row['C'] ?? ''));
        $type         = strtolower($typeCheck);
        $lot_size     = to_float($row['E'] ?? 0);
        $open_price   = to_float($row['F'] ?? 0);
        $close_price  = to_float($row['J'] ?? 0);
        $profit_loss  = to_float($row['M'] ?? 0);
        $openTimeRaw  = $row['A'] ?? null; // kolom A = open time
        $closeTimeRaw = $row['I'] ?? null; // kolom I = close time

        if (!$position_id || !$symbol || $lot_size <= 0) continue;

        // --- konversi waktu ---
        $open_time  = $parseExcelDate($openTimeRaw);
        $close_time = $parseExcelDate($closeTimeRaw);

        $parsed[] = [
            'account_id'   => $accountId,
            'position_id'  => $position_id,
            'type'         => $type,
            'symbol'       => $symbol,
            'lot_size'     => $lot_size,
            'open_price'   => $open_price,
            'close_price'  => $close_price,
            'profit_loss'  => $profit_loss,
            'open_time'    => $open_time,
            'close_time'   => $close_time,
            'note'         => 'Import dari MT5 Report',
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
    }

    return $parsed;
}







