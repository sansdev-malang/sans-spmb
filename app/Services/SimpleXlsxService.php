<?php

namespace App\Services;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SimpleXlsxService
{
    /**
     * Create a 100% compliant OpenXML .xlsx file.
     *
     * @param array $headers Column header titles
     * @param array $rows 2D array of row data
     * @param string $sheetName Sheet tab title
     * @return string Path to temporary .xlsx file
     */
    public static function create(array $headers, array $rows, string $sheetName = 'Data Calon Murid'): string
    {
        $tmpDir = sys_get_temp_dir() . '/xlsx_' . uniqid('', true);
        mkdir($tmpDir . '/_rels', 0777, true);
        mkdir($tmpDir . '/xl/_rels', 0777, true);
        mkdir($tmpDir . '/xl/worksheets', 0777, true);

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '</Types>';
        file_put_contents($tmpDir . '/[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';
        file_put_contents($tmpDir . '/_rels/.rels', $rels);

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';
        file_put_contents($tmpDir . '/xl/_rels/workbook.xml.rels', $wbRels);

        // 4. xl/workbook.xml
        $cleanSheetName = preg_replace('/[\\\\\/:\?\*\[\]]/', '_', $sheetName);
        $cleanSheetName = mb_substr($cleanSheetName, 0, 31, 'UTF-8');
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<workbookPr defaultThemeVersion="124226"/>' .
            '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="20480" windowHeight="10240"/></bookViews>' .
            '<sheets>' .
            '<sheet name="' . htmlspecialchars($cleanSheetName, ENT_XML1) . '" sheetId="1" r:id="rId1"/>' .
            '</sheets>' .
            '<calcPr calcId="124519"/>' .
            '</workbook>';
        file_put_contents($tmpDir . '/xl/workbook.xml', $wb);

        // 5. xl/styles.xml (Strict OpenXML schema compliant)
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<numFmts count="1">' .
            '<numFmt numFmtId="164" formatCode="#,##0"/>' .
            '</numFmts>' .
            '<fonts count="2">' .
            '<font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font>' .
            '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>' .
            '</fonts>' .
            '<fills count="3">' .
            '<fill><patternFill patternType="none"/></fill>' .
            '<fill><patternFill patternType="gray125"/></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FF059669"/></patternFill></fill>' .
            '</fills>' .
            '<borders count="2">' .
            '<border><left/><right/><top/><bottom/><diagonal/></border>' .
            '<border>' .
            '<left style="thin"><color rgb="FFCBD5E1"/></left>' .
            '<right style="thin"><color rgb="FFCBD5E1"/></right>' .
            '<top style="thin"><color rgb="FFCBD5E1"/></top>' .
            '<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>' .
            '<diagonal/>' .
            '</border>' .
            '</borders>' .
            '<cellStyleXfs count="1">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' .
            '</cellStyleXfs>' .
            '<cellXfs count="4">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/>' . // 0: Default
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' . // 1: Header
            '<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>' . // 2: Text format (@)
            '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>' . // 3: Integer currency format (#,##0)
            '</cellXfs>' .
            '<cellStyles count="1">' .
            '<cellStyle name="Normal" xfId="0" builtinId="0"/>' .
            '</cellStyles>' .
            '<dxfs count="0"/>' .
            '<tableStyles count="0" defaultTableStyle="TableStyleMedium9" defaultPivotStyle="PivotStyleLight16"/>' .
            '</styleSheet>';
        file_put_contents($tmpDir . '/xl/styles.xml', $styles);

        // 6. xl/worksheets/sheet1.xml
        $numCols = count($headers);
        $numRows = count($rows) + 1;
        $lastColLetter = self::columnLetter($numCols);
        $dimension = "A1:{$lastColLetter}{$numRows}";

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<dimension ref="' . $dimension . '"/>' .
            '<sheetViews>' .
            '<sheetView tabSelected="1" workbookViewId="0">' .
            '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>' .
            '<selection pane="bottomLeft" activeCell="A2" sqref="A2"/>' .
            '</sheetView>' .
            '</sheetViews>' .
            '<sheetFormatPr defaultRowHeight="18"/>' .
            '<sheetData>';

        // Header Row
        $sheetXml .= '<row r="1" ht="26" customHeight="1">';
        foreach ($headers as $colIdx => $head) {
            $cellRef = self::columnLetter($colIdx + 1) . '1';
            $cleanHead = self::cleanXmlString((string)$head);
            $sheetXml .= '<c r="' . $cellRef . '" s="1" t="inlineStr"><is><t>' . $cleanHead . '</t></is></c>';
        }
        $sheetXml .= '</row>';

        // Data Rows
        $rowNum = 2;
        foreach ($rows as $row) {
            $sheetXml .= '<row r="' . $rowNum . '" ht="20" customHeight="1">';
            foreach ($row as $colIdx => $val) {
                $cellRef = self::columnLetter($colIdx + 1) . $rowNum;
                if (is_numeric($val) && !str_starts_with((string)$val, '0') && strlen((string)$val) < 12) {
                    $sheetXml .= '<c r="' . $cellRef . '" s="3"><v>' . $val . '</v></c>';
                } else {
                    $valStr = (string)$val;
                    if (str_starts_with($valStr, "'")) {
                        $valStr = substr($valStr, 1);
                    }
                    $cleanVal = self::cleanXmlString($valStr);
                    $sheetXml .= '<c r="' . $cellRef . '" s="2" t="inlineStr"><is><t>' . $cleanVal . '</t></is></c>';
                }
            }
            $sheetXml .= '</row>';
            $rowNum++;
        }

        $sheetXml .= '</sheetData>' .
            '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' .
            '</worksheet>';
        file_put_contents($tmpDir . '/xl/worksheets/sheet1.xml', $sheetXml);

        // Package into genuine .xlsx zip archive
        $zipPath = $tmpDir . '.xlsx';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tmpDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($tmpDir) + 1);
                $relativePath = str_replace('\\', '/', $relativePath);
                $zip->addFile($filePath, $relativePath);
            }
            $zip->close();
        }

        // Cleanup temporary folder
        self::deleteDir($tmpDir);

        return $zipPath;
    }

    /**
     * Generate BinaryFileResponse for download and auto-delete temp file afterwards.
     */
    public static function download(array $headers, array $rows, string $filename, string $sheetName = 'Data Calon Murid'): BinaryFileResponse
    {
        $filePath = self::create($headers, $rows, $sheetName);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ])->deleteFileAfterSend(true);
    }

    private static function cleanXmlString(string $val): string
    {
        // Strip control characters invalid in XML 1.0 (allow \t, \n, \r)
        $val = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $val);
        return htmlspecialchars($val, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function columnLetter(int $n): string
    {
        $letter = '';
        while ($n > 0) {
            $mod = ($n - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $n = (int)(($n - $mod) / 26);
        }
        return $letter;
    }

    private static function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? self::deleteDir($path) : unlink($path);
        }
        rmdir($dir);
    }
}
