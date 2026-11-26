<?php
declare(strict_types=1);

namespace plugins\member\service;

/**
 * 轻量 Excel 读写：不依赖 PhpSpreadsheet。
 * - 导出：生成真正的 .xlsx（ZipArchive + OOXML，单元格用 inlineStr，手机号不会丢前导 0）
 * - 导入：支持 .xlsx 与 UTF-8/GBK 的 .csv，返回二维数组
 */
class Excel
{
    public static function export(string $filename, array $headers, array $rows): string
    {
        $dir = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'member_export';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $path = $dir . DIRECTORY_SEPARATOR . preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($filename, PATHINFO_FILENAME)) . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.xlsx';

        $sheetRows = [];
        $r = 1;
        foreach (array_merge([$headers], $rows) as $row) {
            $cells = '';
            $c = 0;
            foreach ($row as $value) {
                $ref = self::colName($c) . $r;
                $cells .= '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">' . self::xml((string) $value) . '</t></is></c>';
                $c++;
            }
            $sheetRows[] = '<row r="' . $r . '">' . $cells . '</row>';
            $r++;
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . implode('', $sheetRows) . '</sheetData></worksheet>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="会员数据" sheetId="1" r:id="rId1"/></sheets></workbook>';

        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';

        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('无法创建 Excel 文件');
        }
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();
        return $path;
    }

    /**
     * 读 Excel / CSV，返回行数组（含表头）
     * @return array<int,array<int,string>>
     */
    public static function read(string $path): array
    {
        if (!is_file($path)) {
            throw new \InvalidArgumentException('上传文件不存在');
        }
        $fh = @fopen($path, 'rb');
        if (!$fh) {
            throw new \InvalidArgumentException('无法读取上传文件');
        }
        $head = fread($fh, 4) ?: '';
        fclose($fh);
        if (str_starts_with($head, "PK\x03\x04")) {
            return self::readXlsx($path);
        }
        return self::readCsv($path);
    }

    private static function readXlsx(string $path): array
    {
        if (!class_exists('ZipArchive')) {
            throw new \InvalidArgumentException('PHP 缺少 Zip 扩展，无法读取 xlsx');
        }
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \InvalidArgumentException('xlsx 文件无法打开');
        }
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheet === false) {
            $zip->close();
            throw new \InvalidArgumentException('xlsx 中找不到第一个工作表');
        }
        $shared = [];
        $ss = $zip->getFromName('xl/sharedStrings.xml');
        if ($ss !== false) {
            $sx = @simplexml_load_string($ss);
            if ($sx) {
                foreach ($sx->si as $si) {
                    $shared[] = (string) ($si->t ?? '');
                }
            }
        }
        $zip->close();

        $dom = new \DOMDocument();
        if (!@$dom->loadXML($sheet)) {
            throw new \InvalidArgumentException('xlsx 工作表解析失败');
        }
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $out = [];
        foreach ($xpath->query('//m:sheetData/m:row') as $rowNode) {
            $cells = [];
            $max = 0;
            foreach ($xpath->query('m:c', $rowNode) as $cell) {
                $ref = (string) $cell->getAttribute('r');
                if (!preg_match('/^([A-Z]+)\d+$/', $ref, $m)) {
                    continue;
                }
                $idx = self::colIndex($m[1]);
                $max = max($max, $idx);
                $type = (string) $cell->getAttribute('t');
                if ($type === 'inlineStr') {
                    $val = '';
                    $t = $xpath->query('m:is/m:t', $cell)->item(0);
                    if ($t) {
                        $val = (string) $t->nodeValue;
                    }
                } elseif ($type === 's') {
                    $v = $xpath->query('m:v', $cell)->item(0);
                    $val = $v ? (string) ($shared[(int) $v->nodeValue] ?? '') : '';
                } else {
                    $v = $xpath->query('m:v', $cell)->item(0);
                    $val = $v ? (string) $v->nodeValue : '';
                }
                $cells[$idx] = $val;
            }
            if (!$cells) {
                continue;
            }
            $line = [];
            for ($i = 0; $i <= $max; $i++) {
                $line[] = (string) ($cells[$i] ?? '');
            }
            $out[] = $line;
        }
        return $out;
    }

    private static function readCsv(string $path): array
    {
        $raw = (string) file_get_contents($path);
        if ($raw === '') {
            return [];
        }
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'GBK');
        }
        $tmp = fopen('php://temp', 'r+');
        fwrite($tmp, $raw);
        rewind($tmp);
        $out = [];
        while (($line = fgetcsv($tmp)) !== false) {
            if ($line === [null] || $line === []) {
                continue;
            }
            $out[] = array_map(static function ($v) {
                return trim((string) $v);
            }, $line);
        }
        fclose($tmp);
        return $out;
    }

    /** 表头 -> 关联数组；无法识别的表头保持顺序 */
    public static function rowsToAssoc(array $rows, array $expected): array
    {
        if (!$rows) {
            return [];
        }
        $head = array_map(static function ($v) {
            return trim((string) $v);
        }, $rows[0]);
        $map = [];
        foreach ($head as $i => $name) {
            $name = preg_replace('/\s*[\*＊]\s*$/', '', $name);
            $map[$name] = $i;
        }
        $out = [];
        for ($r = 1; $r < count($rows); $r++) {
            $line = $rows[$r];
            if (!array_filter($line, static fn($v) => trim((string) $v) !== '')) {
                continue;
            }
            $item = [];
            foreach ($expected as $key => $label) {
                $idx = $map[$label] ?? null;
                $item[$key] = $idx !== null ? trim((string) ($line[$idx] ?? '')) : '';
            }
            $out[] = $item;
        }
        return $out;
    }

    private static function colName(int $index): string
    {
        $name = '';
        $index++;
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $name = chr(65 + $mod) . $name;
            $index = (int) (($index - $mod) / 26);
        }
        return $name;
    }

    private static function colIndex(string $letters): int
    {
        $n = 0;
        foreach (str_split(strtoupper($letters)) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }
        return max(0, $n - 1);
    }

    private static function xml(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
