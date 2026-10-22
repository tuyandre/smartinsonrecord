<?php

namespace App\Services;

use App\Models\GlobalVeterinarian;
use Carbon\Carbon;
use SimpleXMLElement;
use ZipArchive;

/**
 * Imports the RCVD veterinarian list (.xlsx or .csv). The first row must hold the headings:
 * No, RCVD ID, National ID, Names, date of birth, District, Sector. Rows are matched on the RCVD ID,
 * so importing the same list again updates records instead of duplicating them.
 */
class VeterinarianImport
{
    private const HEADINGS = [
        'rcvdid' => 'rcvd_id', 'rcvd' => 'rcvd_id',
        'nationalid' => 'national_id', 'nid' => 'national_id',
        'names' => 'names', 'name' => 'names',
        'dateofbirth' => 'date_of_birth', 'dob' => 'date_of_birth', 'birthdate' => 'date_of_birth',
        'district' => 'district', 'sector' => 'sector',
    ];

    /** @return array{created: int, updated: int, skipped: array<int, string>} */
    public function import(string $path, string $extension): array
    {
        $rows = strtolower($extension) === 'csv' ? $this->readCsv($path) : $this->readXlsx($path);

        $result = ['created' => 0, 'updated' => 0, 'skipped' => []];
        if (count($rows) < 2) {
            $result['skipped'][] = 'The file has no data rows.';

            return $result;
        }

        $columns = $this->columns(array_shift($rows));
        if (! in_array('rcvd_id', $columns, true) || ! in_array('names', $columns, true)) {
            $result['skipped'][] = 'The first row must contain the headings "RCVD ID" and "Names".';

            return $result;
        }

        $seenNationalIds = [];
        foreach ($rows as $i => $cells) {
            $line = $i + 2;
            $row = [];
            foreach ($columns as $index => $field) {
                if ($field !== null) {
                    $row[$field] = trim((string) ($cells[$index] ?? ''));
                }
            }
            if (implode('', $row) === '') {
                continue; // blank line
            }

            if (($row['rcvd_id'] ?? '') === '' || ($row['names'] ?? '') === '') {
                $result['skipped'][] = "Row {$line}: RCVD ID and Names are required.";

                continue;
            }

            $nationalId = preg_replace('/\s+/', '', $row['national_id'] ?? '');
            if ($nationalId !== '' && preg_match('/^\d+(\.\d+)?E\+\d+$/i', $nationalId)) {
                $result['skipped'][] = "Row {$line} ({$row['rcvd_id']}): the National ID was saved as a number, so Excel shortened it. Format the column as Text and enter the 16 digits again.";

                continue;
            }
            if ($nationalId !== '' && ! preg_match('/^\d{16}$/', $nationalId)) {
                $result['skipped'][] = "Row {$line} ({$row['rcvd_id']}): the National ID must be exactly 16 digits.";

                continue;
            }
            $nationalId = $nationalId === '' ? null : $nationalId;

            $vet = GlobalVeterinarian::where('rcvd_id', $row['rcvd_id'])->first();

            if ($nationalId !== null && (isset($seenNationalIds[$nationalId])
                || GlobalVeterinarian::where('national_id', $nationalId)->when($vet, fn ($q) => $q->where('id', '!=', $vet->id))->exists())) {
                $result['skipped'][] = "Row {$line} ({$row['rcvd_id']}): National ID {$nationalId} already belongs to another veterinarian.";

                continue;
            }
            $seenNationalIds[$nationalId] = true;

            $attributes = [
                'national_id' => $nationalId,
                'names' => mb_substr($row['names'], 0, 120),
                'date_of_birth' => $this->date($row['date_of_birth'] ?? ''),
                'district' => ($row['district'] ?? '') === '' ? null : mb_convert_case(mb_substr($row['district'], 0, 60), MB_CASE_TITLE),
                'sector' => ($row['sector'] ?? '') === '' ? null : mb_convert_case(mb_substr($row['sector'], 0, 60), MB_CASE_TITLE),
            ];

            if ($vet) {
                $vet->update($attributes);
                $result['updated']++;
            } else {
                GlobalVeterinarian::create(['rcvd_id' => mb_substr($row['rcvd_id'], 0, 30)] + $attributes);
                $result['created']++;
            }
        }

        return $result;
    }

    /** Maps the heading row to field names ("RCVD ID" => rcvd_id); unknown columns (like "No") become null. */
    private function columns(array $heading): array
    {
        return array_map(fn ($cell) => self::HEADINGS[preg_replace('/[^a-z]/', '', strtolower((string) $cell))] ?? null, $heading);
    }

    /** dd/mm/yyyy, yyyy-mm-dd or an Excel serial number. */
    private function date(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            if (is_numeric($value) && (float) $value > 1000) {
                return Carbon::create(1899, 12, 30)->addDays((int) $value)->format('Y-m-d');
            }

            return (preg_match('#^\d{1,2}[/.-]\d{1,2}[/.-]\d{4}$#', $value)
                ? Carbon::createFromFormat('d/m/Y', str_replace(['.', '-'], '/', $value))
                : Carbon::parse($value))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) === false) {
            return $rows;
        }
        $first = true;
        while (($data = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if ($first) {
                $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $data[0]); // UTF-8 byte order mark
                $first = false;
            }
            $rows[] = $data;
        }
        fclose($handle);

        return $rows;
    }

    private function readXlsx(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            foreach ((new SimpleXMLElement($xml))->si as $si) {
                $text = isset($si->t) ? (string) $si->t : '';
                foreach ($si->r ?? [] as $run) {
                    $text .= (string) $run->t;
                }
                $shared[] = $text;
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) {
            return [];
        }

        $rows = [];
        foreach ((new SimpleXMLElement($sheetXml))->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $cell) {
                $value = (string) $cell->v;
                $type = (string) $cell['t'];
                if ($type === 's') {
                    $value = $shared[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) $cell->is->t;
                }
                $cells[$this->columnIndex((string) $cell['r'])] = $value;
            }
            if ($cells) {
                $rows[] = array_replace(array_fill(0, max(array_keys($cells)) + 1, ''), $cells);
            }
        }

        return $rows;
    }

    /** "C5" => 2 */
    private function columnIndex(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference));
        $index = 0;
        foreach (str_split($letters) as $char) {
            $index = $index * 26 + (ord($char) - 64);
        }

        return $index - 1;
    }
}
